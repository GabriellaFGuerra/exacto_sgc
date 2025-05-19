<?php
session_start();
$pagina_link = 'malote_gerenciar';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Funções utilitárias padronizadas
function exibirMensagem($mensagem, $url = 'malote_gerenciar.php?pagina=malote_gerenciar')
{
    $msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    echo "<script>alert('$msg'); window.location.href = '$url';</script>";
    exit;
}
function dataParaBanco($data)
{
    if (!$data) return '';
    $partes = explode('/', $data);
    return (count($partes) === 3) ? "{$partes[2]}-{$partes[1]}-{$partes[0]}" : $data;
}
function dataParaBR($data)
{
    if (!$data) return '';
    $partes = explode('-', $data);
    return (count($partes) === 3) ? "{$partes[2]}/{$partes[1]}/{$partes[0]}" : $data;
}
function formatarValor($valor)
{
    return str_replace(',', '.', str_replace('.', '', $valor));
}
function fazerUploadArquivos($arquivos, $destino)
{
    $resultado = [];
    if (!file_exists($destino)) {
        mkdir($destino, 0755, true);
    }
    foreach ($arquivos['name'] as $indice => $nome) {
        if ($nome) {
            $extensao = pathinfo($nome, PATHINFO_EXTENSION);
            $nomeArquivo = $destino . md5(mt_rand(1, 10000) . $nome) . '.' . $extensao;
            if (move_uploaded_file($arquivos['tmp_name'][$indice], $nomeArquivo)) {
                $resultado[] = $nomeArquivo;
            }
        }
    }
    return $resultado;
}
function obterRequest($chave, $padrao = '')
{
    return $_POST[$chave] ?? $_GET[$chave] ?? $padrao;
}

// Variáveis de controle padronizadas
$acao           = obterRequest('action');
$pagina         = obterRequest('pagina', 'malote_gerenciar');
$autenticacao   = $_GET['autenticacao'] ?? '';
$paginaAtual    = max(1, intval(obterRequest('pag', 1)));
$itensPorPagina = 10;
$primeiroRegistro = ($paginaAtual - 1) * $itensPorPagina;
$tituloPagina   = "Malotes &raquo; <a href='malote_gerenciar.php?pagina=malote_gerenciar$autenticacao'>Gerenciar</a>";

// CRUD - Adicionar
if ($acao === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $clienteId = obterRequest('mal_cliente_id');
    $lacre = obterRequest('mal_lacre');
    $observacoes = obterRequest('mal_observacoes');
    $stmt = $pdo->prepare(
        "INSERT INTO malote_gerenciar (mal_cliente, mal_lacre, mal_observacoes) VALUES (?, ?, ?)"
    );
    if ($stmt->execute([$clienteId, $lacre, $observacoes])) {
        $ultimoId = $pdo->lastInsertId();

        // Itens do malote
        if (!empty($_POST['fornecedores']) && is_array($_POST['fornecedores'])) {
            foreach ($_POST['fornecedores'] as $item) {
                $item = array_filter($item);
                if (!empty($item)) {
                    $dataVencimento = isset($item['mai_data_vencimento']) ? dataParaBanco($item['mai_data_vencimento']) : null;
                    $valorItem = isset($item['mai_valor']) ? formatarValor($item['mai_valor']) : null;
                    $stmtItem = $pdo->prepare(
                        "INSERT INTO malote_itens (mai_fornecedor, mai_tipo_documento, mai_num_cheque, mai_valor, mai_data_vencimento, mai_malote) VALUES (?, ?, ?, ?, ?, ?)"
                    );
                    $stmtItem->execute([
                        $item['mai_fornecedor'] ?? '',
                        $item['mai_tipo_documento'] ?? '',
                        $item['mai_num_cheque'] ?? '',
                        $valorItem,
                        $dataVencimento,
                        $ultimoId
                    ]);
                }
            }
        }

        // Upload de arquivos
        $caminho = "../admin/malote/$ultimoId/";
        $arquivoEletronico1 = fazerUploadArquivos($_FILES['mal_pg_eletronico'], $caminho);
        $arquivoEletronico2 = fazerUploadArquivos($_FILES['mal_pg_eletronico2'], $caminho);

        if ($arquivoEletronico1) {
            $pdo->prepare(
                "UPDATE malote_gerenciar SET mal_pg_eletronico = ? WHERE mal_id = ?"
            )->execute([$arquivoEletronico1[0], $ultimoId]);
        }
        if ($arquivoEletronico2) {
            $pdo->prepare(
                "UPDATE malote_gerenciar SET mal_pg_eletronico2 = ? WHERE mal_id = ?"
            )->execute([$arquivoEletronico2[0], $ultimoId]);
        }

        exibirMensagem('Cadastro efetuado com sucesso.');
    } else {
        exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
    }
}

// Exclusão
if ($acao === 'excluir' && isset($_GET['mal_id'])) {
    $mal_id = $_GET['mal_id'];
    $stmt = $pdo->prepare("DELETE FROM malote_gerenciar WHERE mal_id = ?");
    if ($stmt->execute([$mal_id])) {
        exibirMensagem('Exclusão realizada com sucesso.');
    } else {
        exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
    }
}

// Filtros
$filtroMalote = obterRequest('fil_malote');
$filtroLacre = obterRequest('fil_lacre');
$filtroCheque = obterRequest('fil_cheque');
$filtroNome = obterRequest('fil_nome');
$filtroDataInicio = dataParaBanco(obterRequest('fil_data_inicio'));
$filtroDataFim = dataParaBanco(obterRequest('fil_data_fim'));
$filtroBaixado = obterRequest('fil_baixado');

$where = ['ucl_usuario = :usuario'];
$params = [':usuario' => $_SESSION['usuario_id']];

if ($filtroMalote) {
    $where[] = 'mal_id = :malote';
    $params[':malote'] = $filtroMalote;
}
if ($filtroLacre) {
    $where[] = 'mal_lacre LIKE :lacre';
    $params[':lacre'] = "%$filtroLacre%";
}
if ($filtroNome) {
    $where[] = 'cli_nome_razao LIKE :nome';
    $params[':nome'] = "%$filtroNome%";
}
if ($filtroCheque) {
    $where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_num_cheque = :cheque)';
    $params[':cheque'] = $filtroCheque;
}
if ($filtroDataInicio && $filtroDataFim) {
    $where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_data_vencimento BETWEEN :data_inicio AND :data_fim)';
    $params[':data_inicio'] = $filtroDataInicio;
    $params[':data_fim'] = $filtroDataFim . ' 23:59:59';
} elseif ($filtroDataInicio) {
    $where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_data_vencimento >= :data_inicio)';
    $params[':data_inicio'] = $filtroDataInicio;
} elseif ($filtroDataFim) {
    $where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_data_vencimento <= :data_fim)';
    $params[':data_fim'] = $filtroDataFim . ' 23:59:59';
}
if ($filtroBaixado !== '') {
    if ($filtroBaixado == 1) {
        $where[] = 'mal_id NOT IN (SELECT mai_malote FROM malote_itens WHERE mai_baixado IS NULL)';
    } elseif ($filtroBaixado == 0) {
        $where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_baixado IS NULL)';
    }
}

$whereSql = implode(' AND ', $where);

// Consulta principal
$sql = "SELECT * FROM malote_gerenciar 
    LEFT JOIN (cadastro_clientes 
        INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
    ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
    WHERE $whereSql
    ORDER BY mal_data_cadastro DESC
    LIMIT $primeiroRegistro, $itensPorPagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$malotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total de registros para paginação
$sqlTotal = "SELECT COUNT(*) FROM malote_gerenciar 
    LEFT JOIN (cadastro_clientes 
        INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
    ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
    WHERE $whereSql";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

$tituloPagina = "Malotes &raquo; <a href='malote_gerenciar.php?pagina=malote_gerenciar$autenticacao'>Gerenciar</a>";
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <?php include '../mod_topo/topo.php'; ?>
</head>

<body>
    <?php if ($pagina == 'malote_gerenciar'): ?>
    <div class='centro'>
        <div class='titulo'> <?= $tituloPagina ?> </div>
        <div id='botoes'><input value='Novo Malote' type='button'
                onclick="window.location.href='malote_gerenciar.php?pagina=adicionar_malote_gerenciar<?= $autenticacao; ?>';" />
        </div>
        <div class='filtro'>
            <form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post'
                action='malote_gerenciar.php?pagina=malote_gerenciar<?= $autenticacao; ?>'>
                <input name='fil_malote' id='fil_malote' value='<?= htmlspecialchars($filtroMalote) ?>'
                    placeholder='N° malote' size='10'>
                <input name='fil_lacre' id='fil_lacre' value='<?= htmlspecialchars($filtroLacre) ?>'
                    placeholder='N° lacre' size='10'>
                <input name='fil_cheque' id='fil_cheque' value='<?= htmlspecialchars($filtroCheque) ?>'
                    placeholder='N° Cheque' size='10'>
                <input name='fil_nome' id='fil_nome' value='<?= htmlspecialchars($filtroNome) ?>' placeholder='Cliente'>
                <input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' size='10'
                    value='<?= dataParaBR(obterRequest('fil_data_inicio')) ?>'
                    onkeypress='return mascaraData(this,event);'>
                <input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' size='10'
                    value='<?= dataParaBR(obterRequest('fil_data_fim')) ?>'
                    onkeypress='return mascaraData(this,event);'>
                <select name='fil_baixado' id='fil_baixado'>
                    <option value='<?= htmlspecialchars($filtroBaixado) ?>'>
                        <?= $filtroBaixado === '' ? 'Baixado?' : ($filtroBaixado ? 'Sim' : 'Não') ?></option>
                    <option value='1'>Sim</option>
                    <option value='0'>Não</option>
                    <option value=''>Todos</option>
                </select>
                <input type='submit' value='Filtrar'>
            </form>
        </div>
        <?php if ($malotes): ?>
        <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
            <tr>
                <td class='titulo_tabela'>N° Malote</td>
                <td class='titulo_tabela'>N° Lacre</td>
                <td class='titulo_tabela'>Cliente</td>
                <td class='titulo_tabela'>Observação</td>
                <td class='titulo_tabela' align='center'>Data Cadastro</td>
                <td class='titulo_tabela' align='center'>Protocolo</td>
                <td class='titulo_tabela' align='center'>Baixado?</td>
                <td class='titulo_tabela' align='center'>Gerenciar</td>
            </tr>
            <?php $contador = 0;
            foreach ($malotes as $malote):
                $maloteId = $malote['mal_id'];
                $lacre = htmlspecialchars($malote['mal_lacre']);
                $nomeCliente = htmlspecialchars($malote['cli_nome_razao']);
                $observacoes = htmlspecialchars($malote['mal_observacoes']);
                $dataCadastro = dataParaBR(substr($malote['mal_data_cadastro'], 0, 10));
                $horaCadastro = substr($malote['mal_data_cadastro'], 11, 5);

                // Verifica se todos os itens estão baixados
                $stmtBaixado = $pdo->prepare("SELECT mai_baixado FROM malote_itens WHERE mai_malote = ?");
                $stmtBaixado->execute([$maloteId]);
                $itensBaixados = $stmtBaixado->fetchAll(PDO::FETCH_COLUMN);
                $todosBaixados = $itensBaixados && count(array_filter($itensBaixados, fn($b) => $b != 1)) === 0 ? 1 : 0;
                $textoBaixado = $todosBaixados ? "<span class='verde'>Sim</span>" : "<span class='vermelho'>Não</span>";

                $classeLinha = $contador++ % 2 == 0 ? "linhaimpar" : "linhapar";
            ?>
            <tr class='<?= $classeLinha ?>'>
                <td><a
                        href='malote_gerenciar.php?pagina=exibir_malote_gerenciar&mal_id=<?= $maloteId . $autenticacao ?>'><b><?= $maloteId ?></b></a>
                </td>
                <td><?= $lacre ?></td>
                <td><?= $nomeCliente ?></td>
                <td><?= $observacoes ?></td>
                <td align='center'><?= $dataCadastro ?><br><span class='detalhe'><?= $horaCadastro ?></span></td>
                <td align='center'><img class='mouse' src='../imagens/icon-pdf.png'
                        onclick="window.open('malote_imprimir.php?mal_id=<?= $maloteId . $autenticacao ?>');"></td>
                <td align='center'><?= $textoBaixado ?></td>
                <td align='center'>
                    <a
                        href='malote_gerenciar.php?pagina=exibir_malote_gerenciar&mal_id=<?= $maloteId . $autenticacao ?>'><img
                            border='0' src='../imagens/icon-exibir.png'></a>
                    <a
                        href='malote_gerenciar.php?pagina=editar_malote_gerenciar&mal_id=<?= $maloteId . $autenticacao ?>'><img
                            border='0' src='../imagens/icon-editar.png'></a>
                    <a href="javascript:void(0);"
                        onclick="if(confirm('Deseja realmente excluir o malote <?= addslashes($nomeCliente) ?>?')){window.location.href='malote_gerenciar.php?pagina=malote_gerenciar&action=excluir&mal_id=<?= $maloteId . $autenticacao ?>';}">
                        <img border='0' src='../imagens/icon-excluir.png'>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php if ($totalPaginas > 1): ?>
        <div class='paginacao' style='text-align:center; margin:20px 0;'>
            <?php for ($i = 1; $i <= $totalPaginas; $i++):
                $classe = ($i == $paginaAtual) ? "pagina-ativa" : "";
                $url = "malote_gerenciar.php?pagina=malote_gerenciar&pag=$i$autenticacao";
                ?>
            <a class='<?= $classe ?>' href='<?= $url ?>'><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <br><br><br>Não há nenhum malote cadastrado.
        <?php endif; ?>
        <div class='titulo'></div>
    </div>
    <?php endif; ?>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>