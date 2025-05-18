<?php
session_start();
require_once '../mod_includes/php/connect.php';

function obterCliente($pdo, $cliId)
{
	$sql = "SELECT cli_nome_razao FROM cadastro_clientes WHERE cli_id = :cli_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':cli_id', $cliId, PDO::PARAM_INT);
	$stmt->execute();
	return $stmt->fetch(PDO::FETCH_ASSOC);
}

function cadastrarOrcamento($pdo, $cliId, $tipoServico, $observacoes)
{
	$sql = "INSERT INTO orcamento_gerenciar (orc_cliente, orc_tipo_servico_cliente, orc_observacoes) 
			VALUES (:cli_id, :tipo_servico, :observacoes)";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':cli_id', $cliId, PDO::PARAM_INT);
	$stmt->bindParam(':tipo_servico', $tipoServico, PDO::PARAM_STR);
	$stmt->bindParam(':observacoes', $observacoes, PDO::PARAM_STR);
	$stmt->execute();
	return $pdo->lastInsertId();
}

function cadastrarStatusOrcamento($pdo, $orcamentoId)
{
	$sql = "INSERT INTO cadastro_status_orcamento (sto_orcamento, sto_status, sto_observacao) 
			VALUES (:orcamento_id, 1, 'Abertura de orçamento')";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':orcamento_id', $orcamentoId, PDO::PARAM_INT);
	$stmt->execute();
}

function exibirMensagem($mensagem, $sucesso = true)
{
	$icone = $sucesso ? 'ok.png' : 'x.png';
	$acao = $sucesso
		? "<input value=' Ok ' type='button' class='close_janela'>"
		: "<input value=' Ok ' type='button' onclick='javascript:window.history.back();'>";
	echo "<script>
		abreMask('<img src=../imagens/{$icone}> {$mensagem}<br><br>{$acao}');
	</script>";
}

if (isset($_GET['action']) && $_GET['action'] === 'adicionar') {
	$cliId = $_POST['cli_id'] ?? null;
	$tipoServico = trim($_POST['orc_tipo_servico_cliente'] ?? '');
	$observacoes = trim($_POST['orc_observacoes'] ?? '');

	if ($cliId && $tipoServico) {
		$cliente = obterCliente($pdo, $cliId);

		if ($cliente) {
			try {
				$pdo->beginTransaction();
				$orcamentoId = cadastrarOrcamento($pdo, $cliId, $tipoServico, $observacoes);
				cadastrarStatusOrcamento($pdo, $orcamentoId);
				$pdo->commit();

				include '../mail/envia_email_novo_orcamento.php';

				exibirMensagem(
					'Orçamento cadastrado com sucesso.<br>Aguarde o breve atendimento da nossa equipe e acompanhe o andamento do seu orçamento.'
				);
			} catch (Exception $e) {
				$pdo->rollBack();
				exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.', false);
			}
		} else {
			exibirMensagem('Cliente não encontrado.', false);
		}
	} else {
		exibirMensagem('Preencha todos os campos obrigatórios.', false);
	}
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
	<title>Novo Orçamento</title>
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include '../css/style.php'; ?>
	<script src="../mod_includes/js/funcoes.js"></script>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
	<?php
	include '../mod_includes/php/funcoes-jquery.php';
	require_once '../mod_includes/php/verificalogincliente.php';
	include '../mod_topo_cliente/topo.php';

	if (isset($_GET['pagina']) && $_GET['pagina'] === 'novo_orcamento'):
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
								placeholder="Digite o serviço que deseja solicitar orçamento" required>
							<p>
								<textarea name="orc_observacoes"
									placeholder="Observações, detalhar o máximo possível."></textarea>
							<p>
								<center>
									<div id="erro">&nbsp;</div>
									<input type="submit" value="Solicitar Orçamento">
								</center>
						</td>
					</tr>
				</table>
			</div>
		</form>
		<?php
	endif;
	include '../mod_rodape/rodape.php';
	?>
</body>

</html>