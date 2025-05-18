<?php
session_start();
include('../mod_includes/php/connect.php');

if ($_GET['action'] === "adicionar") {
	$cli_id = $_POST['cli_id'] ?? null;
	$orc_tipo_servico_cliente = $_POST['orc_tipo_servico_cliente'] ?? '';
	$orc_observacoes = $_POST['orc_observacoes'] ?? '';

	// Consulta segura do cliente
	$sql_unidade = "SELECT cli_nome_razao FROM cadastro_clientes WHERE cli_id = :cli_id";
	$stmt = $pdo->prepare($sql_unidade);
	$stmt->bindParam(':cli_id', $cli_id, PDO::PARAM_INT);
	$stmt->execute();
	$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($cliente) {
		$sql = "INSERT INTO orcamento_gerenciar (orc_cliente, orc_tipo_servico_cliente, orc_observacoes) 
                VALUES (:cli_id, :orc_tipo_servico_cliente, :orc_observacoes)";

		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':cli_id', $cli_id, PDO::PARAM_INT);
		$stmt->bindParam(':orc_tipo_servico_cliente', $orc_tipo_servico_cliente, PDO::PARAM_STR);
		$stmt->bindParam(':orc_observacoes', $orc_observacoes, PDO::PARAM_STR);

		if ($stmt->execute()) {
			$ultimo_id = $pdo->lastInsertId();
			$sql_status = "INSERT INTO cadastro_status_orcamento (sto_orcamento, sto_status, sto_observacao) 
                           VALUES (:ultimo_id, 1, 'Abertura de orçamento')";

			$stmt_status = $pdo->prepare($sql_status);
			$stmt_status->bindParam(':ultimo_id', $ultimo_id, PDO::PARAM_INT);
			$stmt_status->execute();

			include("../mail/envia_email_novo_orcamento.php");

			echo "<SCRIPT>
                    abreMask('<img src=../imagens/ok.png> Orçamento cadastrado com sucesso.<br>Aguarde o breve atendimento da nossa equipe e acompanhe o andamento do seu orçamento.<br><br>
                    <input value=\' Ok \' type=\'button\' class=\'close_janela\'>');
                  </SCRIPT>";
		} else {
			echo "<SCRIPT>
                    abreMask('<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>
                    <input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
                  </SCRIPT>";
		}
	}
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
	<title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script src="../mod_includes/js/funcoes.js"></script>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
	<?php
	include('../mod_includes/php/funcoes-jquery.php');
	require_once('../mod_includes/php/verificalogincliente.php');
	include("../mod_topo_cliente/topo.php");

	if ($_GET['pagina'] === 'novo_orcamento') {
		?>

		<form name="form_cadastro_orcamentos" id="form_cadastro_orcamentos" method="post"
			action="novo_orcamento.php?pagina=novo_orcamento&action=adicionar">
			<div class="centro">
				<div class="titulo">Novo Orçamento</div>
				<table align="center" cellspacing="0" width="580">
					<tr>
						<td align="left">
							<input type="hidden" name="cli_id"
								value="<?php echo htmlspecialchars($_SESSION['cliente_id']); ?>">
							<input name="orc_tipo_servico_cliente"
								placeholder="Digite o serviço que deseja solicitar orçamento">
							<p>
								<textarea name="orc_observacoes"
									placeholder="Observações, detalhar o máximo possível."></textarea>
								<br><br>
							<p>
								<center>
									<div id="erro">&nbsp;</div>
									<input type="submit" value="Solicitar Orçamento">
								</center>
						</td>
					</tr>
				</table>
				<div class="titulo"></div>
			</div>
		</form>

		<?php
	}

	include('../mod_rodape/rodape.php');
	?>
</body>

</html>