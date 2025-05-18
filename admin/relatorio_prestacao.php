<?php
session_start();
$pagina_link = 'relatorio_prestacao';
include('../mod_includes/php/connect.php');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title><?php echo $titulo ?? ''; ?></title>
    <meta name="author" content="MogiComp">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include("../css/style.php"); ?>
    <script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
    <script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php
include('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogin.php');
include("../mod_topo/topo.php");
require_once('../mod_includes/php/verificapermissao.php');

$page = "Relatórios &raquo; <a href='relatorio_prestacao.php?pagina=relatorio_prestacao" . ($autenticacao ?? '') . "'>Prestação de Contas</a>";

$fil_nome = $_REQUEST['fil_nome'] ?? '';
$fil_referencia = $_REQUEST['fil_referencia'] ?? '';
$fil_data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
$fil_data_fim = $_REQUEST['fil_data_fim'] ?? '';
$filtro = $_REQUEST['filtro'] ?? '';

$nome_query = $fil_nome !== '' ? "cli_nome_razao LIKE :fil_nome" : "1=1";
$referencia_query = $fil_referencia !== '' ? "pre_referencia = :fil_referencia" : "1=1";

function formatDate($date)
{
	if (!$date)
		return '';
	$parts = explode('/', $date);
	if (count($parts) === 3) {
		return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
	}
	return $date;
}

$fil_data_inicio_sql = formatDate($fil_data_inicio);
$fil_data_fim_sql = formatDate($fil_data_fim);

if ($fil_data_inicio_sql === '' && $fil_data_fim_sql === '') {
	$data_query = "1=1";
} elseif ($fil_data_inicio_sql !== '' && $fil_data_fim_sql === '') {
	$data_query = "pre_data_cadastro >= :fil_data_inicio";
} elseif ($fil_data_inicio_sql === '' && $fil_data_fim_sql !== '') {
	$data_query = "pre_data_cadastro <= :fil_data_fim";
} else {
	$data_query = "pre_data_cadastro BETWEEN :fil_data_inicio AND :fil_data_fim";
}

$filtro_query = $filtro !== '' ? "1=1" : "1=0";

$sql = "
	SELECT prestacao_gerenciar.*, cadastro_clientes.cli_nome_razao
	FROM prestacao_gerenciar
	LEFT JOIN (
		cadastro_clientes
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
	) ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
	WHERE ucl_usuario = :usuario_id
	  AND $nome_query
	  AND $referencia_query
	  AND $data_query
	  AND $filtro_query
	ORDER BY pre_data_cadastro DESC
";

$stmt = $conexao->prepare($sql);
$stmt->bindValue(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
if ($fil_nome !== '')
	$stmt->bindValue(':fil_nome', "%$fil_nome%", PDO::PARAM_STR);
if ($fil_referencia !== '')
	$stmt->bindValue(':fil_referencia', $fil_referencia, PDO::PARAM_STR);
if ($fil_data_inicio_sql !== '' && $fil_data_fim_sql === '')
	$stmt->bindValue(':fil_data_inicio', $fil_data_inicio_sql, PDO::PARAM_STR);
if ($fil_data_inicio_sql === '' && $fil_data_fim_sql !== '')
	$stmt->bindValue(':fil_data_fim', $fil_data_fim_sql, PDO::PARAM_STR);
if ($fil_data_inicio_sql !== '' && $fil_data_fim_sql !== '') {
	$stmt->bindValue(':fil_data_inicio', $fil_data_inicio_sql, PDO::PARAM_STR);
	$stmt->bindValue(':fil_data_fim', $fil_data_fim_sql, PDO::PARAM_STR);
}
$stmt->execute();
$rows = $stmt->rowCount();

if (($_GET['pagina'] ?? '') === "relatorio_prestacao") {
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_prestacao.php?pagina=relatorio_prestacao" . ($autenticacao ?? '') . "&filtro=1'>
			<input name='fil_nome' id='fil_nome' value='" . htmlspecialchars($fil_nome) . "' placeholder='Cliente'>
			<input name='fil_referencia' id='fil_referencia' value='" . htmlspecialchars($fil_referencia) . "' placeholder='Referência '>
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
		<br>
		<img src='" . ($logo ?? '') . "' border='0' valign='middle' class='logo' /> 
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>N° Prestação de Conta</td>
				<td class='titulo_tabela'>Cliente</td>
				<td class='titulo_tabela'>Referência</td>
				<td class='titulo_tabela'>Data Envio</td>
				<td class='titulo_tabela'>Por</td>
				<td class='titulo_tabela'>Observação</td>
				<td class='titulo_tabela' align='center'>Data Cadastro</td>
			</tr>";
		$c = 0;
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$pre_id = $row['pre_id'];
			$cli_nome_razao = htmlspecialchars($row['cli_nome_razao']);
			$pre_referencia = htmlspecialchars($row['pre_referencia']);
			$pre_data_envio = $row['pre_data_envio'] ? date('d/m/Y', strtotime($row['pre_data_envio'])) : '';
			$pre_enviado_por = htmlspecialchars($row['pre_enviado_por']);
			$pre_observacoes = htmlspecialchars($row['pre_observacoes']);
			$pre_data_cadastro = $row['pre_data_cadastro'] ? date('d/m/Y', strtotime($row['pre_data_cadastro'])) : '';
			$pre_hora_cadastro = $row['pre_data_cadastro'] ? date('H:i', strtotime($row['pre_data_cadastro'])) : '';
			$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
			$c++;
			echo "<tr class='$c1'>
					  <td>$pre_id</td>
					  <td>$cli_nome_razao</td>
					  <td>$pre_referencia</td>
					  <td>$pre_data_envio</td>
					  <td>$pre_enviado_por</td>
					  <td>$pre_observacoes</td>
					  <td align='center'>$pre_data_cadastro<br><span class='detalhe'>$pre_hora_cadastro</span></td>
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

include('../mod_rodape/rodape.php');
?>
    <script type="text/javascript" src="../mod_includes/js/jquery-1.3.2.min.js"></script>
    <script type="text/javascript" src="../mod_includes/js/elementPrint.js"></script>
</body>

</html>