<?php
session_start();
$pagina_link = 'infracoes_gerenciar';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Funções utilitárias
function exibirMensagem($mensagem, $url = 'infracoes_gerenciar.php?pagina=infracoes_gerenciar')
{
    $msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    echo "<script>alert('$msg'); window.location.href = '$url';</script>";
    exit;
}
function dataParaBanco($data)
{
    if (!$data) return null;
    $partes = explode('/', $data);
    return (count($partes) === 3) ? "{$partes[2]}-{$partes[1]}-{$partes[0]}" : $data;
}
function dataParaBR($data)
{
    if (!$data) return '';
    $partes = explode('-', $data);
    return (count($partes) === 3) ? "{$partes[2]}/{$partes[1]}/{$partes[0]}" : $data;
}

// Parâmetros da requisição
$acao = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? 'infracoes_gerenciar';
$autenticacao = isset($_GET['autenticacao']) ? '&autenticacao=' . urlencode($_GET['autenticacao']) : '';
$paginaNumero = max(1, intval($_GET['pag'] ?? 1));

// Página de navegação
$tituloPagina = "Infrações &raquo; <a href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'>Gerenciar</a>";

// Processamento de formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($acao === "adicionar" || $acao === "duplicar") {
        $stmt = $pdo->prepare(
            "INSERT INTO infracoes_gerenciar (
                inf_cliente, inf_tipo, inf_ano, inf_cidade, inf_data, inf_proprietario, inf_apto, inf_bloco, inf_endereco, inf_email, inf_desc_irregularidade, inf_assunto, inf_desc_artigo, inf_desc_notificacao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $ok = $stmt->execute([
            $_POST['inf_cliente_id'] ?? null,
            $_POST['inf_tipo'] ?? '',
            date("Y"),
            $_POST['inf_cidade'] ?? '',
            dataParaBanco($_POST['inf_data'] ?? ''),
            $_POST['inf_proprietario'] ?? '',
            $_POST['inf_apto'] ?? '',
            $_POST['inf_bloco'] ?? '',
            $_POST['inf_endereco'] ?? '',
            $_POST['inf_email'] ?? '',
            $_POST['inf_desc_irregularidade'] ?? '',
            $_POST['inf_assunto'] ?? '',
            $_POST['inf_desc_artigo'] ?? '',
            $_POST['inf_desc_notificacao'] ?? ''
        ]);
        if ($ok) {
            exibirMensagem('Cadastro efetuado com sucesso.', 'infracoes_gerenciar.php?pagina=infracoes_gerenciar' . $autenticacao);
        } else {
            exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
        }
    }

    if ($acao === 'editar') {
        $id = isset($_GET['inf_id']) ? $_GET['inf_id'] : (isset($_POST['inf_id']) ? $_POST['inf_id'] : null);
        if (!$id) {
            exibirMensagem('ID da infração não informado.');
        }
        $stmt = $pdo->prepare(
            "UPDATE infracoes_gerenciar SET
                inf_tipo = ?, inf_cidade = ?, inf_data = ?, inf_proprietario = ?, inf_apto = ?, inf_bloco = ?, inf_endereco = ?, inf_email = ?, inf_desc_irregularidade = ?, inf_assunto = ?, inf_desc_artigo = ?, inf_desc_notificacao = ?
                WHERE inf_id = ?"
        );
        $ok = $stmt->execute([
            $_POST['inf_tipo'] ?? '',
            $_POST['inf_cidade'] ?? '',
            dataParaBanco($_POST['inf_data'] ?? ''),
            $_POST['inf_proprietario'] ?? '',
            $_POST['inf_apto'] ?? '',
            $_POST['inf_bloco'] ?? '',
            $_POST['inf_endereco'] ?? '',
            $_POST['inf_email'] ?? '',
            $_POST['inf_desc_irregularidade'] ?? '',
            $_POST['inf_assunto'] ?? '',
            $_POST['inf_desc_artigo'] ?? '',
            $_POST['inf_desc_notificacao'] ?? '',
            $id
        ]);
        if ($ok) {
            exibirMensagem('Dados alterados com sucesso.', 'infracoes_gerenciar.php?pagina=infracoes_gerenciar' . $autenticacao);
        } else {
            exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
        }
    }
}

// Exclusão
if ($acao === 'excluir' && isset($_GET['inf_id'])) {
    $stmt = $pdo->prepare("DELETE FROM infracoes_gerenciar WHERE inf_id = ?");
    $ok = $stmt->execute([$_GET['inf_id']]);
    if ($ok) {
        exibirMensagem('Exclusão realizada com sucesso.', 'infracoes_gerenciar.php?pagina=infracoes_gerenciar' . $autenticacao);
    } else {
        exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
    }
}

// Filtros
$itensPorPagina = 10;
$primeiroRegistro = ($paginaNumero - 1) * $itensPorPagina;

$filtroNome = $_REQUEST['fil_nome'] ?? '';
$filtroBloco = $_REQUEST['fil_bloco'] ?? '';
$filtroAssunto = $_REQUEST['fil_assunto'] ?? '';
$filtroApto = $_REQUEST['fil_apto'] ?? '';
$filtroProprietario = $_REQUEST['fil_proprietario'] ?? '';
$filtroTipo = $_REQUEST['fil_inf_tipo'] ?? '';

$where = [];
$params = [];

// Remova o filtro de usuário para mostrar todas as infrações
// Se quiser filtrar por usuário, adicione: $where[] = "ucl_usuario = ?"; $params[] = $_SESSION['usuario_id'];

if ($filtroNome) {
    $where[] = "cli_nome_razao LIKE ?";
    $params[] = "%$filtroNome%";
}
if ($filtroBloco) {
    $where[] = "inf_bloco LIKE ?";
    $params[] = "%$filtroBloco%";
}
if ($filtroAssunto) {
    $where[] = "inf_assunto LIKE ?";
    $params[] = "%$filtroAssunto%";
}
if ($filtroApto) {
    $where[] = "inf_apto LIKE ?";
    $params[] = "%$filtroApto%";
}
if ($filtroProprietario) {
    $where[] = "inf_proprietario LIKE ?";
    $params[] = "%$filtroProprietario%";
}
if ($filtroTipo) {
    $where[] = "inf_tipo = ?";
    $params[] = $filtroTipo;
}

$whereSql = $where ? implode(' AND ', $where) : '1=1';

// Consulta para paginação
$sqlTotal = "SELECT COUNT(*) FROM infracoes_gerenciar
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    WHERE $whereSql";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Consulta principal
$sql = "SELECT infracoes_gerenciar.*, cli_nome_razao
    FROM infracoes_gerenciar
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    WHERE $whereSql
    ORDER BY inf_data DESC
    LIMIT $primeiroRegistro, $itensPorPagina";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$infracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Infrações</title>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include "../css/style.php"; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <?php include '../mod_topo/topo.php'; ?>

    <?php if ($pagina === "infracoes_gerenciar"): ?>
    <div class='centro'>
        <div class='titulo'>
            <?= $tituloPagina ?>
        </div>
        <div id='botoes'><input value='Nova Infração' type='button'
                onclick="window.location.href='infracoes_gerenciar.php?pagina=adicionar_infracoes_gerenciar<?= $autenticacao; ?>';" />
        </div>
        <div class='filtro'>
            <form name='form_filtro' id='form_filtro' method='post'
                action='infracoes_gerenciar.php?pagina=infracoes_gerenciar<?= $autenticacao; ?>'>
                <input name='fil_nome' value='<?= htmlspecialchars($filtroNome) ?>' placeholder=' Cliente'>
                <input name='fil_bloco' value='<?= htmlspecialchars($filtroBloco) ?>' placeholder='Bloco/Quadra'>
                <input name='fil_apto' value='<?= htmlspecialchars($filtroApto) ?>' placeholder='Apto.'>
                <input name='fil_proprietario' value='<?= htmlspecialchars($filtroProprietario) ?>'
                    placeholder='Proprietário'>
                <input name='fil_assunto' value='<?= htmlspecialchars($filtroAssunto) ?>' placeholder='Assunto'>
                <select name='fil_inf_tipo'>
                    <option value=''>Tipo de Infração</option>
                    <option value='Notificação de advertência por infração disciplinar'
                        <?= $filtroTipo == 'Notificação de advertência por infração disciplinar' ? 'selected' : '' ?>>
                        Notificação de advertência por infração disciplinar</option>
                    <option value='Multa por Infração Interna'
                        <?= $filtroTipo == 'Multa por Infração Interna' ? 'selected' : '' ?>>Multa por Infração Interna
                    </option>
                    <option value='Notificação de ressarcimento'
                        <?= $filtroTipo == 'Notificação de ressarcimento' ? 'selected' : '' ?>>Notificação de
                        ressarcimento</option>
                    <option value='Comunicação interna' <?= $filtroTipo == 'Comunicação interna' ? 'selected' : '' ?>>
                        Comunicação interna</option>
                    <option value='' <?= $filtroTipo == '' ? 'selected' : '' ?>>Todos</option>
                </select>
                <input type='submit' value='Filtrar'>
            </form>
        </div>
        <?php if ($infracoes): ?>
        <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
            <tr>
                <td class='titulo_tabela'>N.</td>
                <td class='titulo_tabela'>Cliente</td>
                <td class='titulo_tabela'>Tipo</td>
                <td class='titulo_tabela'>Assunto</td>
                <td class='titulo_tabela'>Proprietário</td>
                <td class='titulo_tabela'>Bloco/Quadra/Ap</td>
                <td class='titulo_tabela'>Data</td>
                <td class='titulo_tabela' align='center'>Gerar advertência/multa</td>
                <td class='titulo_tabela' align='center'>Gerar protocolo</td>
                <td class='titulo_tabela' align='center'>Gerenciar</td>
            </tr>
            <?php $c = 0;
                    foreach ($infracoes as $infracao):
                        $classeLinha = $c++ % 2 ? "linhapar" : "linhaimpar";
                        $id = $infracao['inf_id'];
                        $ano = $infracao['inf_ano'];
                        $cliente = htmlspecialchars($infracao['cli_nome_razao']);
                        $tipo = htmlspecialchars($infracao['inf_tipo']);
                        $assunto = htmlspecialchars($infracao['inf_assunto']);
                        $bloco = htmlspecialchars($infracao['inf_bloco']);
                        $apto = htmlspecialchars($infracao['inf_apto']);
                        $proprietario = htmlspecialchars($infracao['inf_proprietario']);
                        $data = dataParaBR($infracao['inf_data']);
                        ?>
            <tr class='<?= $classeLinha ?>'>
                <td><?= str_pad($id, 3, "0", STR_PAD_LEFT) . "/$ano" ?></td>
                <td><?= $cliente ?></td>
                <td><?= $tipo ?></td>
                <td><?= $assunto ?></td>
                <td><?= $proprietario ?></td>
                <td><?= $bloco ?>/<?= $apto ?></td>
                <td><?= $data ?></td>
                <td align='center'><a href='infracoes_imprimir.php?inf_id=<?= $id . $autenticacao ?>'
                        target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
                <td align='center'><a href='infracoes_protocolo_imprimir.php?inf_id=<?= $id . $autenticacao ?>'
                        target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
                <td align='center'>
                    <a href="infracoes_gerenciar.php?pagina=editar_infracoes_gerenciar&inf_id=<?= $id . $autenticacao ?>" title="Editar">
                        <img src="../imagens/icon-editar.png" border="0" />
                    </a>
                    <a href="javascript:void(0);" onclick="if(confirm('Deseja realmente excluir a infração <?= addslashes($cliente) ?>?')){window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=excluir&inf_id=<?= $id . $autenticacao ?>';}" title="Excluir">
                        <img src="../imagens/icon-excluir.png" border="0" />
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
                if ($totalPaginas > 1) {
                    echo "<div class='paginacao' style='text-align:center; margin:20px 0;>";
                    for ($i = 1; $i <= $totalPaginas; $i++) {
                        $classe = ($i == $paginaNumero) ? "pagina-ativa" : "";
                        $url = "infracoes_gerenciar.php?pagina=infracoes_gerenciar&pag=$i$autenticacao";
                        echo "<a class='$classe' href='$url'>$i</a> ";
                    }
                    echo "</div>";
                }
                ?>
        <?php else: ?>
        <br><br><br>Não há nenhuma infração cadastrada.
        <?php endif; ?>
        <div class='titulo'></div>
    </div>
    <?php endif; ?>
    <?php if ($pagina === "adicionar_infracoes_gerenciar"): ?>
    <div class='centro'>
        <div class='titulo'>Adicionar Infração</div>
        <form method="post" action="infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=adicionar<?= $autenticacao ?>">
            <table class="formulario" align="center">
                <tr>
                    <td>Cliente:</td>
                    <td>
                        <select name="inf_cliente_id" required>
                            <option value="">Selecione</option>
                            <?php
                            $sql = "SELECT cli_id, cli_nome_razao FROM cadastro_clientes WHERE cli_status = 1 AND cli_deletado = 0 ORDER BY cli_nome_razao ASC";
                            foreach ($pdo->query($sql) as $row) {
                                echo "<option value='{$row['cli_id']}'>{$row['cli_nome_razao']}</option>";
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Tipo:</td>
                    <td>
                        <select name="inf_tipo" required>
                            <option value="">Selecione</option>
                            <option value="Notificação de advertência por infração disciplinar">Notificação de advertência por infração disciplinar</option>
                            <option value="Multa por Infração Interna">Multa por Infração Interna</option>
                            <option value="Notificação de ressarcimento">Notificação de ressarcimento</option>
                            <option value="Comunicação interna">Comunicação interna</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Cidade:</td>
                    <td><input type="text" name="inf_cidade" required /></td>
                </tr>
                <tr>
                    <td>Data:</td>
                    <td><input type="text" name="inf_data" maxlength="10" onkeypress="return mascaraData(this,event);" required placeholder="dd/mm/aaaa" /></td>
                </tr>
                <tr>
                    <td>Proprietário:</td>
                    <td><input type="text" name="inf_proprietario" /></td>
                </tr>
                <tr>
                    <td>Apto:</td>
                    <td><input type="text" name="inf_apto" /></td>
                </tr>
                <tr>
                    <td>Bloco:</td>
                    <td><input type="text" name="inf_bloco" /></td>
                </tr>
                <tr>
                    <td>Endereço:</td>
                    <td><input type="text" name="inf_endereco" /></td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td><input type="email" name="inf_email" /></td>
                </tr>
                <tr>
                    <td>Irregularidade:</td>
                    <td><textarea name="inf_desc_irregularidade" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td>Assunto:</td>
                    <td><input type="text" name="inf_assunto" /></td>
                </tr>
                <tr>
                    <td>Artigo:</td>
                    <td><textarea name="inf_desc_artigo" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td>Notificação:</td>
                    <td><textarea name="inf_desc_notificacao" rows="2"></textarea></td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="Salvar" />
                        <input type="button" value="Cancelar" onclick="window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar';" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php endif; ?>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>
</html>