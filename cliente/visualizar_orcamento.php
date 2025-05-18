<?php
session_start();
include('../mod_includes/php/connect.php');

$orc_id = $_GET['orc_id'] ?? '';

$sql = "SELECT * FROM cadastro_orcamentos
        LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_orcamentos.orc_equipamento
        LEFT JOIN cadastro_tecnicos ON cadastro_tecnicos.tec_id = cadastro_orcamentos.orc_tecnico
        LEFT JOIN cadastro_unidades ON cadastro_unidades.uni_id = cadastro_orcamentos.orc_unidade
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente
        LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = cadastro_orcamentos.orc_id 
        WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento)
        AND cli_id = :cliente_id AND orc_id = :orc_id
        GROUP BY orc_id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
$stmt->bindParam(':orc_id', $orc_id, PDO::PARAM_INT);
$stmt->execute();
$orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orcamento) {
	echo "<div class='centro'><br><br><br>Nenhum orçamento encontrado.</div>";
	exit();
}

$orc_data_cadastro = date("d/m/Y H:i", strtotime($orcamento['orc_data']));
$sto_status_n = match ($orcamento['sto_status']) {
	1 => "<span class='preto'>Em análise</span>",
	2 => "<span class='azul'>Aberto</span>",
	3 => "<span class='laranja'>Pendente</span>",
	4 => "<span class='verde'>Finalizado</span>",
	5 => "<span class='vermelho'>Cancelado</span>",
	default => "Não especificado"
};

?>

<!DOCTYPE html>
<html lang="pt">

<head>
	<title>Visualizar Orçamento</title>
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
	?>

	<div class='centro'>
		<div class='titulo'>Visualizar Orçamento</div>
		<div class='quadro'>
			<div style='width:90%; margin:0 auto; line-height:25px;'>
				<div class='formtitulo'>Dados do Orçamento</div>
				<b>Nº Protocolo:</b> <?php echo htmlspecialchars($orcamento['orc_ano'] . $orcamento['orc_id']); ?><br>
				<b>Situação atual:</b> <?php echo $sto_status_n; ?><br>
				<b>Data de abertura:</b> <?php echo $orc_data_cadastro; ?>
				<p>
					<b>Unidade Solicitante:</b> <?php echo htmlspecialchars($orcamento['uni_nome_razao']); ?><br>
					<b>Equipamento:</b>
				<ul>
					<li><b>Tipo:</b> <?php echo htmlspecialchars($orcamento['equ_tipo']); ?></li>
					<li><b>Marca:</b> <?php echo htmlspecialchars($orcamento['equ_marca']); ?></li>
					<li><b>Modelo:</b> <?php echo htmlspecialchars($orcamento['equ_modelo']); ?></li>
					<li><b>Nº Série:</b> <?php echo htmlspecialchars($orcamento['equ_num_serie']); ?></li>
				</ul>
				<b>Responsável:</b> <?php echo htmlspecialchars($orcamento['orc_responsavel']); ?><br>
				<b>Telefone:</b> <?php echo htmlspecialchars($orcamento['orc_telefone']); ?><br>
				<b>Descrição do orçamento/problema:</b><br>
				<?php echo nl2br(htmlspecialchars($orcamento['orc_descricao'])); ?>
				<p>
			</div>
		</div>

		<div style='width:90%; margin:0 auto; line-height:25px;'>
			<div class='formtitulo'>Histórico do Orçamento</div>

			<?php
			$sql = "SELECT * FROM cadastro_status_orcamento WHERE sto_orcamento = :orc_id ORDER BY sto_data ASC";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':orc_id', $orc_id, PDO::PARAM_INT);
			$stmt->execute();
			$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ($historico) {
				echo "<section id='cd-timeline' class='cd-container'>";
				foreach ($historico as $evento) {
					$sto_data = date("d/m/Y H:i", strtotime($evento['sto_data']));
					$sto_observacao = htmlspecialchars($evento['sto_observacao']);
					$sto_status_n = match ($evento['sto_status']) {
						1 => "<span class='preto'>Em análise</span>",
						2 => "<span class='azul'>Aberto</span>",
						3 => "<span class='laranja'>Pendente</span>",
						4 => "<span class='verde'>Finalizado</span>",
						5 => "<span class='vermelho'>Cancelado</span>",
						default => "Não especificado"
					};

					echo "
                <div class='cd-timeline-block'>
                    <div class='cd-timeline-img cd-location'>
                        <img src='../imagens/cd-icon-location.svg' alt='Location'>
                    </div>
                    <div class='cd-timeline-content'>
                        <p><b>Status:</b> {$sto_status_n}</p>
                        <p><b>Observações:</b> {$sto_observacao}</p>
                        <span class='cd-date'>{$sto_data}</span>
                    </div>
                </div>";
				}
				echo "</section>";
			} else {
				echo "<br><br><br>Nenhum histórico encontrado.";
			}
			?>
		</div>
	</div>

	<?php include('../mod_rodape/rodape.php'); ?>
</body>

</html>