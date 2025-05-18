<?php
session_start();
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogincliente.php';

$recId = isset($_GET['rec_id']) ? (int) $_GET['rec_id'] : 0;
$clienteId = isset($_SESSION['cliente_id']) ? (int) $_SESSION['cliente_id'] : 0;

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
$stmtTotal->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Consulta dos recursos
$sql = "SELECT * FROM recurso_gerenciar 
	LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
	WHERE cli_id = :cliente_id" . ($recId ? " AND rec_id = :rec_id" : "") . "
	ORDER BY inf_data DESC
	LIMIT :offset, :itensPorPagina";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
if ($recId) {
	$stmt->bindParam(':rec_id', $recId, PDO::PARAM_INT);
}
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->bindParam(':itensPorPagina', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo = 'Consultar Recurso';
?>
<!DOCTYPE html>
<html lang="pt">

<head>
	<title>
		<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>
	</title>
	<meta name="author" content="MogiComp">
	<meta charset=" UTF-8">
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include '../css/style.php'; ?>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>
<body>
	<?php include '../mod_includes/php/funcoes-jquery.php'; ?>
	<?php include '../mod_topo_cliente/topo.php'; ?>

	<div class=" centro">
	<div class="titulo">Consultar Recurso</div>

	<?php if ($recursos): ?>
		<?php foreach ($recursos as $recurso): ?>
			<form name="form_recurso_gerenciar" id="form_recurso_gerenciar" method="post"
				action="infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=adicionar_recurso&inf_id=<?= htmlspecialchars($recurso['inf_id']) ?>">
							<table align="center" cellspacing="0" width="90%">
				<tr>
										<td align="left">
					<input type="hidden" name="inf_id" value="<?= htmlspecialchars($recurso['inf_id']) ?>">

												<b>Recurso:</b>
					<a href="<?= htmlspecialchars($recurso['rec_recurso']) ?>" target="_blank">
						<img src="../imagens/icon-pdf.png" alt="PDF">
					</a>
					<p><b>Status:</b>
						<?= htmlspecialchars($recurso['rec_status']) ?>
					</p>
					<p>Mogi das Cruzes,
						<?= date('d/m/Y') ?>
					</p>
					<p>
						<?= htmlspecialchars($recurso['rec_assunto']) ?>
					</p>
					<p>
						<?= htmlspecialchars($recurso['rec_descricao']) ?>
					</p>

					<div id="erro">&nbsp;</div>
					<input type="button" onclick="window.location.href='consultar_infracoes.php?pagina=consultar_infracoes';"
						value="Voltar" />
					</td>
				</tr>
				</table>
			</form>
		<?php endforeach; ?>

		<!-- Paginação -->
		<?php if ($totalPaginas > 1): ?>
			<div style="text-align:center; margin-top:20px;">
				<?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
					<?php if ($i == $paginaAtual): ?>
						<strong>
							<?= $i ?>
						</strong>
					<?php else: ?>
						<a href="?pagina=<?= $i ?>">
							<?= $i ?>
						</a>
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