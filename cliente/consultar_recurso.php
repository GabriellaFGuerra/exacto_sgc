<?php
session_start();
include('../mod_includes/php/connect.php');

$rec_id = $_GET['rec_id'] ?? '';

$sql = "SELECT * FROM recurso_gerenciar 
        LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
        WHERE cli_id = :cliente_id AND rec_id = :rec_id
        ORDER BY inf_data DESC";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
$stmt->bindParam(':rec_id', $rec_id, PDO::PARAM_INT);
$stmt->execute();
$resultado = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">

<head>
	<title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>
	<meta name="author" content="MogiComp">
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
	<?php include('../mod_includes/php/funcoes-jquery.php'); ?>
	<?php require_once('../mod_includes/php/verificalogincliente.php'); ?>
	<?php include("../mod_topo_cliente/topo.php"); ?>

	<div class='centro'>
		<div class='titulo'>Consultar Recurso</div>

		<?php if ($resultado): ?>
			<form name='form_recurso_gerenciar' id='form_recurso_gerenciar' method='post'
				action='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=adicionar_recurso&inf_id=<?php echo htmlspecialchars($resultado['inf_id']); ?>'>
				<table align='center' cellspacing='0' width='90%'>
					<tr>
						<td align='left'>
							<input type='hidden' name='inf_id'
								value='<?php echo htmlspecialchars($resultado['inf_id']); ?>'>

							<b>Recurso:</b> <a href='<?php echo htmlspecialchars($resultado['rec_recurso']); ?>'
								target='_blank'><img src='../imagens/icon-pdf.png' border='0'></a>
							<p><b>Status:</b> <?php echo htmlspecialchars($resultado['rec_status']); ?></p>
							<p>Mogi das Cruzes, <?php echo date('d/m/Y'); ?></p>
							<p><?php echo htmlspecialchars($resultado['rec_assunto']); ?></p>
							<p><?php echo htmlspecialchars($resultado['rec_descricao']); ?></p>

							<center>
								<div id='erro'>&nbsp;</div>
								<input type='button'
									onclick="window.location.href='consultar_infracoes.php?pagina=consultar_infracoes';"
									value='Voltar' />
							</center>
						</td>
					</tr>
				</table>
			</form>
		<?php else: ?>
			<p>Recurso não encontrado.</p>
		<?php endif; ?>
	</div>

	<?php include('../mod_rodape/rodape.php'); ?>
</body>

</html>