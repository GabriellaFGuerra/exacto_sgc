<?php
session_start();

require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogincliente.php';

$titulo = 'Consultar Recurso';
$clienteId = (int) ($_SESSION['cliente_id'] ?? 0);
$recId = isset($_GET['rec_id']) ? (int) $_GET['rec_id'] : 0;

// Paginação
$itensPorPagina = 1;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Consulta total de recursos para paginação
$sqlTotal = "SELECT COUNT(*) FROM recurso_gerenciar 
    LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    WHERE cli_id = :cliente_id";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Consulta dos recursos
$sql = "SELECT * FROM recurso_gerenciar 
    LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    WHERE cli_id = :cliente_id"
    . ($recId ? " AND rec_id = :rec_id" : "") . "
    ORDER BY inf_data DESC
    LIMIT :offset, :itensPorPagina";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
if ($recId) {
    $stmt->bindValue(':rec_id', $recId, PDO::PARAM_INT);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':itensPorPagina', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <?php include '../mod_topo_cliente/topo.php'; ?>
</head>
<body>
<?php include '../mod_includes/php/funcoes-jquery.php'; ?>
<div class="centro">
    <div class="titulo"><?= $titulo ?></div>
    <?php if ($recursos): ?>
        <?php foreach ($recursos as $recurso): ?>
            <table align="center" cellspacing="0" width="90%">
                <tr>
                    <td align="left">
                        <b>Recurso:</b>
                        <?php if (!empty($recurso['rec_recurso'])): ?>
                            <a href="<?= htmlspecialchars($recurso['rec_recurso']) ?>" target="_blank">
                                <img src="../imagens/icon-pdf.png" alt="PDF">
                            </a>
                        <?php endif; ?>
                        <p><b>Status:</b> <?= htmlspecialchars($recurso['rec_status']) ?></p>
                        <p>Mogi das Cruzes, <?= date('d/m/Y') ?></p>
                        <p><?= htmlspecialchars($recurso['rec_assunto']) ?></p>
                        <p><?= nl2br(htmlspecialchars($recurso['rec_descricao'])) ?></p>
                        <div id="erro">&nbsp;</div>
                        <input type="button" onclick="window.location.href='consultar_infracoes.php?pagina=consultar_infracoes';" value="Voltar" />
                    </td>
                </tr>
            </table>
        <?php endforeach; ?>

        <!-- Paginação -->
        <?php if ($totalPaginas > 1): ?>
            <div class="paginacao" style="text-align:center; margin-top:20px;">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <?php if ($i == $paginaAtual): ?>
                        <strong><?= $i ?></strong>
                    <?php else: ?>
                        <a href="?pagina=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                    <?= $i < $totalPaginas ? ' | ' : '' ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p>Recurso não encontrado.</p>
    <?php endif; ?>
</div>
<?php include '../mod_rodape/rodape.php'; ?>
</body>
</html>