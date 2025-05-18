<?php
session_start();
$pagina_link = 'relatorio_documentos';

include '../mod_includes/php/connect.php';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?= $titulo ?? '' ?></title>
	<meta name="author" content="MogiComp">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include "../css/style.php"; ?>
	<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
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

	$page = "Relatórios &raquo; <a href='relatorio_documentos.php?pagina=relatorio_documentos$autenticacao'>Documentos</a>";

	$fil_nome = $_REQUEST['fil_nome'] ?? '';
	$nome_query = $fil_nome === '' ? "1=1" : "cli_nome_razao LIKE :fil_nome";

	$fil_tipo_documento = $_REQUEST['fil_tipo_documento'] ?? '';
	if ($fil_tipo_documento === '') {
		$tipo_documento_query = "1=1";
		$fil_tipo_documento_n = "Tipo de Documento";
	} else {
		$tipo_documento_query = "doc_tipo = :fil_tipo_documento";
		$stmt_tipos_docs = $pdo->prepare("SELECT tpd_nome FROM cadastro_tipos_docs WHERE tpd_id = :tpd_id");
		$stmt_tipos_docs->execute(['tpd_id' => $fil_tipo_documento]);
		$fil_tipo_documento_n = $stmt_tipos_docs->fetchColumn() ?: "Tipo de Documento";
	}

	$fil_data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
	$fil_data_fim = $_REQUEST['fil_data_fim'] ?? '';
	$data_inicio = $fil_data_inicio ? implode('-', array_reverse(explode('/', $fil_data_inicio))) : '';
	$data_fim = $fil_data_fim ? implode('-', array_reverse(explode('/', $fil_data_fim))) : '';

	if ($data_inicio === '' && $data_fim === '') {
		$data_query = "1=1";
	} elseif ($data_inicio !== '' && $data_fim === '') {
		$data_query = "doc_data_vencimento >= :data_inicio";
	} elseif ($data_inicio === '' && $data_fim !== '') {
		$data_query = "doc_data_vencimento <= :data_fim";
	} else {
		$data_query = "doc_data_vencimento BETWEEN :data_inicio AND :data_fim";
	}

	$fil_periodicidade = $_REQUEST['fil_periodicidade'] ?? '';
	if ($fil_periodicidade === '') {
		$periodicidade_query = "1=1";
		$fil_periodicidade_n = "Periodicidade";
	} else {
		$periodicidade_query = "doc_periodicidade = :fil_periodicidade";
		$periodicidades = [
			6 => "Semestral",
			12 => "Anual",
			24 => "Bienal",
			36 => "Trienal",
			48 => "Quadrienal",
			60 => "Quinquenal"
		];
		$fil_periodicidade_n = $periodicidades[$fil_periodicidade] ?? "Periodicidade";
	}

	$fil_vencido = $_REQUEST['fil_vencido'] ?? '';
	if ($fil_vencido === '') {
		$vencido_query = "1=1";
		$fil_vencido_n = "Vencido";
	} elseif ($fil_vencido === 'Sim') {
		$hoje = date("Y-m-d");
		$vencido_query = "doc_data_vencimento <= :hoje";
	} elseif ($fil_vencido === 'Não') {
		$hoje = date("Y-m-d");
		$vencido_query = "doc_data_vencimento > :hoje";
	}

	$filtro = $_REQUEST['filtro'] ?? '';
	$filtro_query = $filtro === '' ? "1=0" : "1=1";

	$sql = "
SELECT documento_gerenciar.*, cadastro_clientes.cli_nome_razao, cadastro_tipos_docs.tpd_nome, cadastro_tipos_servicos.tps_nome
FROM documento_gerenciar
LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
LEFT JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
WHERE $nome_query AND $tipo_documento_query AND $data_query AND $vencido_query AND $periodicidade_query AND $filtro_query
ORDER BY doc_data_cadastro DESC
";

	$params = [];
	if ($fil_nome !== '')
		$params['fil_nome'] = "%$fil_nome%";
	if ($fil_tipo_documento !== '')
		$params['fil_tipo_documento'] = $fil_tipo_documento;
	if ($data_inicio !== '')
		$params['data_inicio'] = $data_inicio;
	if ($data_fim !== '')
		$params['data_fim'] = $data_fim;
	if (isset($hoje))
		$params['hoje'] = $hoje;
	if ($fil_periodicidade !== '')
		$params['fil_periodicidade'] = $fil_periodicidade;

	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	$rows = $stmt->rowCount();

	if (($pagina ?? '') === "relatorio_documentos") {
		echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_documentos.php?pagina=relatorio_documentos$autenticacao&filtro=1'>
			<select name='fil_tipo_documento' id='fil_tipo_documento'>
				<option value='$fil_tipo_documento'>$fil_tipo_documento_n</option>";
		$stmt_tipo_documento = $pdo->query("SELECT tpd_id, tpd_nome FROM cadastro_tipos_docs ORDER BY tpd_nome");
		foreach ($stmt_tipo_documento as $row_tipo_documento) {
			echo "<option value='{$row_tipo_documento['tpd_id']}'>{$row_tipo_documento['tpd_nome']}</option>";
		}
		echo "
				<option value=''>Todos</option>
			</select>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<select name='fil_periodicidade' id='fil_periodicidade'>
				<option value='$fil_periodicidade'>$fil_periodicidade_n</option>
				<option value='6'>Semestral</option>
				<option value='12'>Anual</option>
				<option value='24'>Bienal</option>
				<option value='36'>Trienal</option>
				<option value=''>Todos</option>
			</select>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='$fil_data_inicio' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='$fil_data_fim' onkeypress='return mascaraData(this,event);'>
			<select name='fil_vencido' id='fil_vencido' style='width:150px;'>
				<option value='$fil_vencido'>$fil_vencido</option>
				<option value='Sim'>Sim</option>
				<option value='Não'>Não</option>
				<option value=''>Todos</option>
			</select>
			<input type='submit' value='Filtrar'>
			<input type='button' onclick=\"PrintDiv('imprimir');\" value='Imprimir' />
			</form>
		</div>
		<div class='contentPrint' id='imprimir'>
	";
		if ($rows > 0) {
			echo "
		<br>
		<img src='$logo' border='0' valign='middle' class='logo' />
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Tipo de Doc</td>
				<td class='titulo_tabela'>Cliente</td>
				<td class='titulo_tabela'>Orçamento</td>
				<td class='titulo_tabela' align='center'>Data Emissão</td>
				<td class='titulo_tabela' align='center'>Periodicidade</td>
				<td class='titulo_tabela' align='center'>Data Vencimento</td>
				<td class='titulo_tabela' align='center'>Data Cadastro</td>
			</tr>";
			$c = 0;
			$periodicidades = [
				6 => "Semestral",
				12 => "Anual",
				24 => "Bienal",
				36 => "Trienal",
				48 => "Quadrienal",
				60 => "Quinquenal"
			];
			foreach ($stmt as $row) {
				$doc_id = $row['doc_id'];
				$cli_nome_razao = $row['cli_nome_razao'];
				$tps_nome = $row['tps_nome'] ?? '';
				$tpd_nome = $row['tpd_nome'] ?? '';
				$doc_data_emissao = $row['doc_data_emissao'] ? implode("/", array_reverse(explode("-", $row['doc_data_emissao']))) : '';
				$doc_periodicidade = $row['doc_periodicidade'];
				$doc_periodicidade_n = $periodicidades[$doc_periodicidade] ?? '';
				$doc_data_vencimento = $row['doc_data_vencimento'] ? implode("/", array_reverse(explode("-", $row['doc_data_vencimento']))) : '';
				$doc_data_cadastro = $row['doc_data_cadastro'] ? implode("/", array_reverse(explode("-", substr($row['doc_data_cadastro'], 0, 10)))) : '';
				$doc_hora_cadastro = $row['doc_data_cadastro'] ? substr($row['doc_data_cadastro'], 11, 5) : '';
				$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
				$c++;
				echo "<tr class='$c1'>
				  <td>$tpd_nome</td>
				  <td>$cli_nome_razao</td>
				  <td>$doc_id ($tps_nome)</td>
				  <td align=center>$doc_data_emissao</td>
				  <td align=center>$doc_periodicidade_n</td>
				  <td align=center>$doc_data_vencimento</td>
				  <td align='center'>$doc_data_cadastro<br><span class='detalhe'>$doc_hora_cadastro</span></td>
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
	}

	include '../mod_rodape/rodape.php';
	?>
	<script src="../mod_includes/js/jquery-1.3.2.min.js"></script>
	<script src="../mod_includes/js/elementPrint.js"></script>
</body>

</html>