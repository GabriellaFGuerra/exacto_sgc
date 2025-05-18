<?php
session_start();
$pagina_link = 'relatorio_orcamentos';
include '../mod_includes/php/connect.php';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php echo $titulo ?? ''; ?></title>
	<meta name="author" content="MogiComp">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include "../css/style.php"; ?>
	<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
	<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
	<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
	<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
	<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
	<?php
	include '../mod_includes/php/funcoes-jquery.php';
	require_once '../mod_includes/php/verificalogin.php';
	include "../mod_topo/topo.php";
	require_once '../mod_includes/php/verificapermissao.php';

	$page = "Relatórios &raquo; <a href='relatorio_orcamentos.php?pagina=relatorio_orcamentos" . $autenticacao . "'>Orçamentos</a>";

	$fil_orc = $_REQUEST['fil_orc'] ?? '';
	$orc_query = $fil_orc !== '' ? "orc_id LIKE :fil_orc" : "1 = 1";

	$fil_nome = $_REQUEST['fil_nome'] ?? '';
	$nome_query = $fil_nome !== '' ? "cli_nome_razao LIKE :fil_nome" : "1 = 1";

	$fil_tipo_servico = $_REQUEST['fil_tipo_servico'] ?? '';
	if ($fil_tipo_servico !== '') {
		$tipo_servico_query = "orc_tipo_servico = :fil_tipo_servico";
		$stmt_tps = $pdo->prepare("SELECT tps_nome FROM cadastro_tipos_servicos WHERE tps_id = :tps_id");
		$stmt_tps->execute([':tps_id' => $fil_tipo_servico]);
		$fil_tipo_servico_n = $stmt_tps->fetchColumn() ?: "Tipo de Serviço Prestado";
	} else {
		$tipo_servico_query = "1 = 1";
		$fil_tipo_servico_n = "Tipo de Serviço Prestado";
	}

	$fil_data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
	$fil_data_fim = $_REQUEST['fil_data_fim'] ?? '';
	$data_query = "1 = 1";
	$params_data = [];
	if ($fil_data_inicio !== '') {
		$data_inicio = implode('-', array_reverse(explode('/', $fil_data_inicio)));
		$data_query = "orc_data_cadastro >= :data_inicio";
		$params_data[':data_inicio'] = $data_inicio;
	}
	if ($fil_data_fim !== '') {
		$data_fim = implode('-', array_reverse(explode('/', $fil_data_fim)));
		if ($fil_data_inicio !== '') {
			$data_query = "orc_data_cadastro BETWEEN :data_inicio AND :data_fim";
			$params_data[':data_fim'] = $data_fim . " 23:59:59";
		} else {
			$data_query = "orc_data_cadastro <= :data_fim";
			$params_data[':data_fim'] = $data_fim . " 23:59:59";
		}
	}

	$fil_status = $_REQUEST['fil_status'] ?? '';
	if ($fil_status !== '') {
		$status_query = "sto_status = :fil_status";
		switch ($fil_status) {
			case 1:
				$fil_status_n = "<span class='laranja'>Pendente</span>";
				break;
			case 2:
				$fil_status_n = "<span class='azul'>Calculado</span>";
				break;
			case 3:
				$fil_status_n = "<span class='verde'>Aprovado</span>";
				break;
			case 4:
				$fil_status_n = "<span class='vermelho'>Reprovado</span>";
				break;
			default:
				$fil_status_n = "Status";
		}
	} else {
		$status_query = "1 = 1";
		$fil_status_n = "Status";
	}

	$filtro = $_REQUEST['filtro'] ?? '';
	$filtro_query = $filtro !== '' ? "1 = 1" : "1 = 0";

	$sql = "SELECT * FROM orcamento_gerenciar 
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
	LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
	LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
	WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento)
	AND $orc_query AND $nome_query AND $tipo_servico_query AND $data_query AND $status_query AND $filtro_query
	ORDER BY orc_data_cadastro DESC";

	$stmt = $pdo->prepare($sql);

	if ($fil_orc !== '')
		$stmt->bindValue(':fil_orc', "%$fil_orc%");
	if ($fil_nome !== '')
		$stmt->bindValue(':fil_nome', "%$fil_nome%");
	if ($fil_tipo_servico !== '')
		$stmt->bindValue(':fil_tipo_servico', $fil_tipo_servico);
	if ($fil_status !== '')
		$stmt->bindValue(':fil_status', $fil_status);
	foreach ($params_data as $k => $v)
		$stmt->bindValue($k, $v);

	$stmt->execute();
	$rows = $stmt->rowCount();

	echo "
<div class='centro'>
	<div class='titulo'> $page  </div>
	<div class='filtro'>
		<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_orcamentos.php?pagina=relatorio_orcamentos" . $autenticacao . "&filtro=1'>
		<input name='fil_orc' id='fil_orc' value='" . htmlspecialchars($fil_orc) . "' placeholder='N° Orçamento'>
		<input name='fil_nome' id='fil_nome' value='" . htmlspecialchars($fil_nome) . "' placeholder='Cliente'>
		<select name='fil_tipo_servico' id='fil_tipo_servico'>
			<option value='" . htmlspecialchars($fil_tipo_servico) . "'>$fil_tipo_servico_n</option>
";
	$sql_tipo_servico = "SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome";
	foreach ($pdo->query($sql_tipo_servico) as $row_tipo_servico) {
		echo "<option value='" . $row_tipo_servico['tps_id'] . "'>" . $row_tipo_servico['tps_nome'] . "</option>";
	}
	echo "
			<option value=''>Todos</option>
		</select>
		<select name='fil_status' id='fil_status'>
			<option value='" . htmlspecialchars($fil_status) . "'>$fil_status_n</option>
			<option value='1'>Pendente</option>
			<option value='2'>Calculado</option>
			<option value='3'>Aprovado</option>
			<option value='4'>Reprovado</option>
			<option value=''>Todos</option>
		</select>
		<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='" . htmlspecialchars($fil_data_inicio) . "' onkeypress='return mascaraData(this,event);'>
		<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='" . htmlspecialchars($fil_data_fim) . "' onkeypress='return mascaraData(this,event);'>
		<input type='submit' value='Filtrar'> 
		<input type='button' onclick=\"PrintDiv('imprimir');\" value='Imprimir' />
		</form>
	</div>
	<div class='contentPrint' id='imprimir'>
";

	if ($rows > 0) {
		echo "
	<img src='$logo' border='0' valign='middle' class='logo' /> 
	<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
		<tr>
			<td class='titulo_tabela'>N° Orçamento</td>
			<td class='titulo_tabela'>Cliente</td>
			<td class='titulo_tabela'>Serviço</td>
			<td class='titulo_tabela' align='center'>Status</td>
			<td class='titulo_tabela' align='center'>Data Cadastro</td>
		</tr>";
		$c = 0;
		foreach ($stmt as $row) {
			$orc_id = $row['orc_id'];
			$cli_nome_razao = $row['cli_nome_razao'];
			$tps_nome = $row['tps_nome'] ?: $row['orc_tipo_servico_cliente'] . "<br><span class='detalhe'>Digitado pelo cliente</span>";
			$sto_status = $row['sto_status'];
			$orc_data_cadastro = date('d/m/Y', strtotime($row['orc_data_cadastro']));
			$orc_hora_cadastro = date('H:i', strtotime($row['orc_data_cadastro']));
			switch ($sto_status) {
				case 1:
					$sto_status_n = "<span class='laranja'>Pendente</span>";
					break;
				case 2:
					$sto_status_n = "<span class='azul'>Calculado</span>";
					break;
				case 3:
					$sto_status_n = "<span class='verde'>Aprovado</span>";
					break;
				case 4:
					$sto_status_n = "<span class='vermelho'>Reprovado</span>";
					break;
				default:
					$sto_status_n = "";
			}
			$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
			$c++;
			echo "<tr class='$c1'>
				  <td>$orc_id</td>
				  <td>$cli_nome_razao</td>
				  <td>$tps_nome</td>
				  <td align=center>$sto_status_n</td>
				  <td align='center'>$orc_data_cadastro<br><span class='detalhe'>$orc_hora_cadastro</span></td>
			  </tr>";
		}
		echo "</table>";
	} else {
		echo "<br><br><br>Selecione acima os filtros que deseja para gerar o relatório.";
	}
	echo "
	<div class='titulo'>  </div>				
	</div>
</div>";

	include '../mod_rodape/rodape.php';
	?>
	<script type="text/javascript" src="../mod_includes/js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="../mod_includes/js/elementPrint.js"></script>
</body>

</html>