<?php
session_start();
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Variáveis de controle padronizadas
$pagina_link = 'recurso_gerenciar';
$autenticacao = $_GET['autenticacao'] ?? '';
$pagina = $_GET['pagina'] ?? 'recurso_gerenciar';
$action = $_GET['action'] ?? '';
$rec_id = $_GET['rec_id'] ?? '';
$paginaAtual = max(1, intval($_GET['pag'] ?? 1));
$itensPorPagina = 10;
$primeiroRegistro = ($paginaAtual - 1) * $itensPorPagina;
$tituloPagina = "Infrações &raquo; <a href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'>Infrações</a> &raquo; Recurso";

// Função utilitária padronizada
function exibirMensagem($mensagem, $url = 'recurso_gerenciar.php?pagina=recurso_gerenciar')
{
    $msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    echo "<script>alert('$msg'); window.location.href = '$url';</script>";
    exit;
}

// Função para upload de arquivos de recurso
function uploadRecurso($rec_id, $arquivos)
{
    $caminho = "../admin/recurso/$rec_id/";
    if (!file_exists($caminho)) {
        mkdir($caminho, 0755, true);
    }
    $arquivoFinal = '';
    foreach ($arquivos['name'] as $key => $nome_arquivo) {
        if (!empty($nome_arquivo)) {
            $extensao = pathinfo($nome_arquivo, PATHINFO_EXTENSION);
            $novo_nome = md5(mt_rand(1, 10000) . $nome_arquivo) . '.' . $extensao;
            $destino = $caminho . $novo_nome;
            if (move_uploaded_file($arquivos['tmp_name'][$key], $destino)) {
                $arquivoFinal = $destino;
            }
        }
    }
    return $arquivoFinal;
}

// CRUD - Editar recurso
if ($action === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rec_assunto = $_POST['rec_assunto'] ?? '';
    $rec_descricao = $_POST['rec_descricao'] ?? '';
    $rec_status = $_POST['rec_status'] ?? '';
    $arquivos = $_FILES['rec_recurso'] ?? ['name' => []];

    // Atualiza dados principais
    $sql = "UPDATE recurso_gerenciar 
            SET rec_assunto = :rec_assunto, rec_descricao = :rec_descricao, rec_status = :rec_status 
            WHERE rec_id = :rec_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':rec_assunto' => $rec_assunto,
        ':rec_descricao' => $rec_descricao,
        ':rec_status' => $rec_status,
        ':rec_id' => $rec_id
    ]);

    // Upload de arquivos
    $arquivoFinal = uploadRecurso($rec_id, $arquivos);
    if ($arquivoFinal) {
        // Remove arquivo antigo, se existir
        $sql = "SELECT rec_recurso FROM recurso_gerenciar WHERE rec_id = :rec_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':rec_id' => $rec_id]);
        $anexo_atual = $stmt->fetchColumn();
        if ($anexo_atual && file_exists($anexo_atual)) {
            unlink($anexo_atual);
        }
        // Atualiza caminho do arquivo no banco
        $sql = "UPDATE recurso_gerenciar SET rec_recurso = :arquivo WHERE rec_id = :rec_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':arquivo' => $arquivoFinal, ':rec_id' => $rec_id]);
    }

    exibirMensagem('Dados alterados com sucesso.', "recurso_gerenciar.php?pagina=recurso_gerenciar&rec_id=$rec_id$autenticacao");
}

// Exibe detalhes do recurso
if ($pagina === 'recurso_gerenciar' && $rec_id) {
    $sql = "SELECT * FROM recurso_gerenciar 
            LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
            LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
            WHERE rec_id = :rec_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':rec_id' => $rec_id]);
    $recurso = $stmt->fetch(PDO::FETCH_ASSOC);
}

// FILTROS DINÂMICOS
$filtros = [];
$params = [];
if (!empty($_REQUEST['fil_assunto'])) {
    $filtros[] = "rec_assunto LIKE :fil_assunto";
    $params[':fil_assunto'] = '%' . $_REQUEST['fil_assunto'] . '%';
}
if (!empty($_REQUEST['fil_cliente'])) {
    $filtros[] = "cli_nome_razao LIKE :fil_cliente";
    $params[':fil_cliente'] = '%' . $_REQUEST['fil_cliente'] . '%';
}
if (!empty($_REQUEST['fil_status'])) {
    $filtros[] = "rec_status = :fil_status";
    $params[':fil_status'] = $_REQUEST['fil_status'];
}
$whereSql = $filtros ? implode(' AND ', $filtros) : '1=1';

// PAGINAÇÃO E LISTAGEM
if ($pagina === 'recurso_listar') {
    $sql_total = "SELECT COUNT(*) FROM recurso_gerenciar
        LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
        WHERE $whereSql";
    $stmtTotal = $pdo->prepare($sql_total);
    $stmtTotal->execute($params);
    $total_registros = $stmtTotal->fetchColumn();

    $sql = "SELECT * FROM recurso_gerenciar 
        LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
        WHERE $whereSql
        ORDER BY rec_id DESC
        LIMIT :offset, :limite";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $primeiroRegistro, PDO::PARAM_INT);
    $stmt->bindValue(':limite', $itensPorPagina, PDO::PARAM_INT);
    $stmt->execute();
    $recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <?php include '../mod_topo/topo.php'; ?>

    <?php if ($pagina === 'recurso_gerenciar' && !empty($recurso)): ?>
    <form name="form_recurso_gerenciar" id="form_recurso_gerenciar" enctype="multipart/form-data" method="post"
        action="recurso_gerenciar.php?pagina=recurso_gerenciar&action=editar&rec_id=<?= htmlspecialchars($rec_id) . $autenticacao ?>">
        <div class="centro">
            <div class="titulo"><?= $tituloPagina ?> &raquo; Gerenciar: <?= htmlspecialchars($recurso['rec_assunto']) ?>
            </div>
            <table align="center" cellspacing="0" width="90%">
                <tr>
                    <td align="left">
                        <b>Cliente:</b> <?= htmlspecialchars($recurso['cli_nome_razao']) ?>
                        (<?= htmlspecialchars($recurso['cli_cnpj']) ?>)
                        <p>
                            <b>Recurso:</b>
                            <?php if (!empty($recurso['rec_recurso'])): ?>
                            <a href="<?= htmlspecialchars($recurso['rec_recurso']) ?>" target="_blank"><img
                                    src="../imagens/icon-pdf.png" border="0"></a>
                            <?php else: ?>
                            Nenhum arquivo anexado.
                            <?php endif; ?>
                            <input type="file" name="rec_recurso[]" />
                        <p>
                            <b>Status:</b> <?= htmlspecialchars($recurso['rec_status']) ?>
                        <p>
                            <textarea name="rec_descricao" rows="15" id="rec_descricao"
                                placeholder="Descrição"><?= htmlspecialchars($recurso['rec_descricao']) ?></textarea>
                        <p>
                            <select name="rec_status" id="rec_status">
                                <option value="<?= htmlspecialchars($recurso['rec_status']) ?>">
                                    <?= htmlspecialchars($recurso['rec_status']) ?>
                                </option>
                                <option value="Deferido">Deferido</option>
                                <option value="Indeferido">Indeferido</option>
                            </select>
                        <p>
                            <center>
                                <input type="submit" id="bt_recurso_gerenciar" value="Salvar" />&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="button" id="botao_cancelar"
                                    onclick="window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar<?= $autenticacao ?>';"
                                    value="Cancelar" />
                            </center>
                    </td>
                </tr>
            </table>
            <div class="titulo"></div>
        </div>
    </form>
    <?php endif; ?>

    <?php if ($pagina === 'recurso_listar'): ?>
    <div class='centro'>
        <div class='titulo'>Lista de Recursos</div>
        <div class='filtro'>
            <form method="get" action="recurso_gerenciar.php">
                <input type="hidden" name="pagina" value="recurso_listar">
                <input type="text" name="fil_assunto" placeholder="Assunto"
                    value="<?= htmlspecialchars($_REQUEST['fil_assunto'] ?? '') ?>">
                <input type="text" name="fil_cliente" placeholder="Cliente"
                    value="<?= htmlspecialchars($_REQUEST['fil_cliente'] ?? '') ?>">
                <select name="fil_status">
                    <option value="">Status</option>
                    <option value="Deferido" <?= (isset($_REQUEST['fil_status']) && $_REQUEST['fil_status'] === 'Deferido') ? 'selected' : '' ?>>Deferido</option>
                    <option value="Indeferido" <?= (isset($_REQUEST['fil_status']) && $_REQUEST['fil_status'] === 'Indeferido') ? 'selected' : '' ?>>Indeferido</option>
                </select>
                <button type="submit">Filtrar</button>
            </form>
        </div>
        <?php if (!empty($recursos)): ?>
        <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
            <tr>
                <td class='titulo_tabela'>ID</td>
                <td class='titulo_tabela'>Assunto</td>
                <td class='titulo_tabela'>Cliente</td>
                <td class='titulo_tabela'>Status</td>
                <td class='titulo_tabela'>Gerenciar</td>
            </tr>
            <?php foreach ($recursos as $recurso): ?>
            <tr>
                <td><?= htmlspecialchars($recurso['rec_id']) ?></td>
                <td><?= htmlspecialchars($recurso['rec_assunto']) ?></td>
                <td><?= htmlspecialchars($recurso['cli_nome_razao']) ?></td>
                <td><?= htmlspecialchars($recurso['rec_status']) ?></td>
                <td>
                    <a
                        href='recurso_gerenciar.php?pagina=recurso_gerenciar&rec_id=<?= htmlspecialchars($recurso['rec_id']) . $autenticacao ?>'>Gerenciar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
            $totalPaginas = ceil($total_registros / $itensPorPagina);
            if ($totalPaginas > 1): ?>
        <div class='paginacao'>
            <?php for ($i = 1; $i <= $totalPaginas; $i++):
                $active = $i == $paginaAtual ? "style='font-weight:bold;'" : '';
                $url = "recurso_gerenciar.php?pagina=recurso_listar&pag=$i$autenticacao";
                ?>
            <a href="<?= $url ?>" <?= $active ?>><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <br><br><br>Não há nenhum recurso cadastrado.
        <?php endif; ?>
        <div class='titulo'></div>
    </div>
    <?php endif; ?>

    <?php include '../mod_rodape/rodape.php'; ?>
</body>
</html>