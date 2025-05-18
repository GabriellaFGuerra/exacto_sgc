<?php
session_start();
include('../mod_includes/php/connect.php');
?>
<!DOCTYPE html
	PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php echo $titulo; ?></title>
	<meta name="author" content="MogiComp">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script type="text/javascript" src="../mod_includes/js/funcoes.js"></script>
	<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
	<?php
	include('../mod_includes/php/funcoes-jquery.php');
	require_once('../mod_includes/php/verificalogincliente.php');
	include("../mod_topo_cliente/topo.php");

	$num_por_pagina = 10;
	$pag = $_REQUEST['pag'] ?? 1;
	$primeiro_registro = ($pag - 1) * $num_por_pagina;

	// Aprovar orçamento
	if ($_GET['action'] === "aprovar") {
		$orc_id = $_GET['orc_id'];
		$orc_data_aprovacao = date("Y-m-d");

		$stmt = $pdo->prepare("UPDATE orcamento_gerenciar SET orc_data_aprovacao = :orc_data_aprovacao WHERE orc_id = :orc_id");
		$stmt->bindParam(':orc_data_aprovacao', $orc_data_aprovacao, PDO::PARAM_STR);
		$stmt->bindParam(':orc_id', $orc_id, PDO::PARAM_INT);
		$stmt->execute();

		// Dados do cliente
		$stmt = $pdo->prepare("SELECT cli_nome_razao, COALESCE(tps_nome, orc_tipo_servico_cliente) as tps_nome 
                           FROM cadastro_clientes
                           LEFT JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_cliente = cadastro_clientes.cli_id
                           LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
                           WHERE orc_id = :orc_id");
		$stmt->bindParam(':orc_id', $orc_id, PDO::PARAM_INT);
		$stmt->execute();
		$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

		$sto_fornecedor_aprovado = $_POST['sto_fornecedor_aprovado'];
		$sto_observacao = $_POST['sto_observacao'];

		// Inserir status
		$stmt = $pdo->prepare("INSERT INTO cadastro_status_orcamento (sto_orcamento, sto_status, sto_fornecedor_aprovado, sto_observacao)
                           VALUES (:orc_id, 3, :sto_fornecedor_aprovado, :sto_observacao)");
		$stmt->bindParam(':orc_id', $orc_id, PDO::PARAM_INT);
		$stmt->bindParam(':sto_fornecedor_aprovado', $sto_fornecedor_aprovado, PDO::PARAM_INT);
		$stmt->bindParam(':sto_observacao', $sto_observacao, PDO::PARAM_STR);
		$stmt->execute();

		include("../mail/envia_email_orcamento_aprovado.php");
		echo "<SCRIPT>abreMask('<img src=../imagens/ok.png> Orçamento aprovado com sucesso.<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );</SCRIPT>";
	}

	// Reprovar orçamento
	if ($_GET['action'] === "reprovar") {
		$orc_id = $_GET['orc_id'];
		$orc_data_reprovacao = date("Y-m-d");

		$stmt = $pdo->prepare("UPDATE orcamento_gerenciar SET orc_data_aprovacao = :orc_data_reprovacao WHERE orc_id = :orc_id");
		$stmt->bindParam(':orc_data_reprovacao', $orc_data_reprovacao, PDO::PARAM_STR);
		$stmt->bindParam(':orc_id', $orc_id, PDO::PARAM_INT);
		$stmt->execute();

		$sto_observacao = $_POST['sto_observacao'];

		$stmt = $pdo->prepare("INSERT INTO cadastro_status_orcamento (sto_orcamento, sto_status, sto_observacao)
                           VALUES (:orc_id, 4, :sto_observacao)");
		$stmt->bindParam(':orc_id', $orc_id, PDO::PARAM_INT);
		$stmt->bindParam(':sto_observacao', $sto_observacao, PDO::PARAM_STR);
		$stmt->execute();

		include("../mail/envia_email_orcamento_reprovado.php");
		echo "<SCRIPT>abreMask('<img src=../imagens/ok.png> Orçamento reprovado com sucesso.<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );</SCRIPT>";
	}

	// Filtros
	$fil_orcamento = $_REQUEST['fil_orcamento'] ?? '';
	$fil_data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
	$fil_data_fim = $_REQUEST['fil_data_fim'] ?? '';
	$fil_status = $_REQUEST['fil_status'] ?? '';

	$status_map = [
		1 => "Pendente",
		2 => "Calculado",
		3 => "Aprovado",
		4 => "Reprovado"
	];
	$fil_status_n = $status_map[$fil_status] ?? "Status";

	$sql = "SELECT * FROM orcamento_gerenciar 
        LEFT JOIN cadastro_status_orcamento ON cadastro_status_orcamento.sto_orcamento = orcamento_gerenciar.orc_id
        WHERE 1=1";
	$condicoes = [];
	$parametros = [];

	if (!empty($fil_orcamento)) {
		$condicoes[] = "orc_id LIKE :fil_orcamento";
		$parametros[':fil_orcamento'] = "%$fil_orcamento%";
	}
	if (!empty($fil_data_inicio) && empty($fil_data_fim)) {
		$condicoes[] = "orc_data_cadastro >= :fil_data_inicio";
		$parametros[':fil_data_inicio'] = $fil_data_inicio;
	}
	if (empty($fil_data_inicio) && !empty($fil_data_fim)) {
		$condicoes[] = "orc_data_cadastro <= :fil_data_fim";
		$parametros[':fil_data_fim'] = $fil_data_fim . " 23:59:59";
	}
	if (!empty($fil_data_inicio) && !empty($fil_data_fim)) {
		$condicoes[] = "orc_data_cadastro BETWEEN :fil_data_inicio AND :fil_data_fim";
		$parametros[':fil_data_inicio'] = $fil_data_inicio;
		$parametros[':fil_data_fim'] = $fil_data_fim . " 23:59:59";
	}
	if (!empty($fil_status)) {
		$condicoes[] = "sto_status = :fil_status";
		$parametros[':fil_status'] = $fil_status;
	}

	if ($condicoes) {
		$sql .= " AND " . implode(" AND ", $condicoes);
	}
	$sql .= " ORDER BY orc_data_cadastro DESC LIMIT :primeiro_registro, :num_por_pagina";

	$stmt = $pdo->prepare($sql);
	foreach ($parametros as $param => $value) {
		$stmt->bindParam($param, $value, PDO::PARAM_STR);
	}
	$stmt->bindParam(':primeiro_registro', $primeiro_registro, PDO::PARAM_INT);
	$stmt->bindParam(':num_por_pagina', $num_por_pagina, PDO::PARAM_INT);
	$stmt->execute();
	$orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	// Construção da consulta com segurança
	$sql = "SELECT * FROM orcamento_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente 
        LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico 
        LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
        WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento) 
        AND cli_id = :cliente_id 
        ORDER BY orc_data_cadastro DESC 
        LIMIT :primeiro_registro, :num_por_pagina";

	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
	$stmt->bindParam(':primeiro_registro', $primeiro_registro, PDO::PARAM_INT);
	$stmt->bindParam(':num_por_pagina', $num_por_pagina, PDO::PARAM_INT);
	$stmt->execute();
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
	?>

	<div class='centro'>
		<div class='titulo'>Consultar Orçamentos</div>
		<div class='filtro'>
			<form method='post' action='consultar_orcamento.php?pagina=consultar_orcamento'>
				<input name='fil_orcamento' placeholder='Nº Orçamento'
					value='<?php echo htmlspecialchars($_REQUEST['fil_orcamento'] ?? ''); ?>'>
				<input type='text' name='fil_data_inicio' placeholder='Data Início'
					value='<?php echo htmlspecialchars($_REQUEST['fil_data_inicio'] ?? ''); ?>'>
				<input type='text' name='fil_data_fim' placeholder='Data Fim'
					value='<?php echo htmlspecialchars($_REQUEST['fil_data_fim'] ?? ''); ?>'>
				<select name='fil_status'>
					<option value=''>Status</option>
					<option value='1'>Pendente</option>
					<option value='2'>Calculado</option>
					<option value='3'>Aprovado</option>
					<option value='4'>Reprovado</option>
				</select>
				<input type='submit' value='Filtrar'>
			</form>
		</div>

		<?php if ($resultados): ?>
			<table class='bordatabela' width='100%' cellspacing='0' cellpadding='10'>
				<tr>
					<td class='titulo_tabela'>N. Orçamento</td>
					<td class='titulo_tabela'>Tipo de Serviço</td>
					<td class='titulo_tabela'>Observações</td>
					<td class='titulo_tabela' align='center'>Data de Abertura</td>
					<td class='titulo_tabela' align='center'>Status</td>
					<td class='titulo_tabela' align='center'>Visualizar</td>
					<td class='titulo_tabela' align='center'>Aprovar/Reprovar</td>
				</tr>
				<?php foreach ($resultados as $row): ?>
					<tr class='<?php echo $c = ($c ?? 0) ? "linhapar" : "linhaimpar";
					$c = !$c; ?>'>
						<td><?php echo htmlspecialchars($row['orc_id']); ?></td>
						<td><?php echo htmlspecialchars($row['tps_nome'] ?? $row['orc_tipo_servico_cliente']); ?></td>
						<td><?php echo htmlspecialchars($row['orc_observacoes']); ?></td>
						<td align='center'><?php echo date("d/m/Y", strtotime($row['orc_data_cadastro'])); ?></td>
						<td align='center'>
							<?php
							$status_map = [
								1 => "<span class='laranja'>Pendente</span>",
								2 => "<span class='azul'>Calculado</span>",
								3 => "<span class='verde'>Aprovado</span>",
								4 => "<span class='vermelho'>Reprovado</span>",
							];
							echo $status_map[$row['sto_status']] ?? '';
							?>
						</td>
						<td align='center'>
							<img class='mouse' src='../imagens/icon-pdf.png'
								onclick="window.open('orcamento_imprimir.php?orc_id=<?php echo $row['orc_id']; ?>');">
						</td>
						<td align='center'>
							<?php if ($row['sto_status'] != 1 && $row['sto_status'] != 3 && $row['sto_status'] != 4): ?>
								<a href='#'
									onclick="abreMaskAcao('<form method=\'post\' action=\'consultar_orcamento.php?pagina=consultar_orcamento&action=aprovar&orc_id=<?php echo $row['orc_id']; ?>'>Selecione o fornecedor aprovado:<select name=\'sto_fornecedor_aprovado\'><?php foreach ($fornecedores as $fornecedor)
										   echo '<option value=\'' . htmlspecialchars($fornecedor['for_id']) . '\'>' . htmlspecialchars($fornecedor['for_nome_razao']) . '</option>'; ?></select><input type=\'text\' name=\'sto_observacao\' placeholder=\'Observação\'><input type=\'submit\' value=\'Aprovar\'></form>');">
									<img border='0' src='../imagens/icon-aprovar.png'>
								</a>
								&nbsp;&nbsp;
								<a href='#'
									onclick="abreMaskAcao('<form method=\'post\' action=\'consultar_orcamento.php?pagina=consultar_orcamento&action=reprovar&orc_id=<?php echo $row['orc_id']; ?>'><input type=\'text\' name=\'sto_observacao\' placeholder=\'Observação\'><input type=\'submit\' value=\'Reprovar\'></form>');">
									<img border='0' src='../imagens/icon-reprovar.png'>
								</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
		<?php else: ?>
			<br><br><br>Não há nenhum orçamento cadastrado.
		<?php endif; ?>
	</div>

	<?php include('../mod_rodape/rodape.php'); ?>