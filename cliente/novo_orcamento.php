<?php
session_start (); 
include('../mod_includes/php/connect.php');
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $titulo;?></title>
<meta name="author" content="MogiComp">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="../imagens/favicon.png">
<?php include("../css/style.php"); ?>
<script type="text/javascript" src="../mod_includes/js/funcoes.js"></script>
<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>
<body>
<?php	
include		('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogincliente.php');
include		("../mod_topo_cliente/topo.php");
?>

<?php
if($action == "adicionar")
{
	$cli_id = $_POST['cli_id'];
	$sql_unidade = "SELECT * FROM cadastro_clientes WHERE cli_id = $cli_id";
	$query_unidade = mysql_query($sql_unidade,$conexao);
	$rows_unidade = mysql_num_rows($query_unidade);
	if($rows_unidade > 0 )
	{
		$cli_nome_razao = mysql_result($query_unidade,0,'cli_nome_razao');
	}
	
	$orc_tipo_servico_cliente = $_POST['orc_tipo_servico_cliente'];
	$orc_observacoes = $_POST['orc_observacoes'];
	$sql = "INSERT INTO orcamento_gerenciar (
	orc_cliente,
	orc_tipo_servico_cliente,
	orc_observacoes
	) 
	VALUES 
	(
	'$cli_id',
	'$orc_tipo_servico_cliente',
	'$orc_observacoes'
	)";

	if(mysql_query($sql,$conexao))
	{		
		$ultimo_id = mysql_insert_id();
		$sql_status = "INSERT INTO cadastro_status_orcamento (
					   sto_orcamento,
					   sto_status,
					   sto_observacao 
					   )
					   VALUES
					   (
					   '$ultimo_id',
					   1,
					   'Abertura de orçamento'
					   )";
		mysql_query($sql_status,$conexao);
		include("../mail/envia_email_novo_orcamento.php");
		/*echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Orçamento cadastrado com sucesso.<br>Aguarde o breve atendimento de nossa equipe e acompanhe o andamento do seu orcamento.<br><br>'+
			'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";*/
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
			"; 
	}	
}
if($pagina == 'novo_orcamento')
{
	echo "	
	<form name='form_cadastro_orcamentos' id='form_cadastro_orcamentos' enctype='multipart/form-data' method='post' action='novo_orcamento.php?pagina=novo_orcamento&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> Novo Orçamento </div>
		<table align='center' cellspacing='0' width='580'>
			<tr>
				<td align='left'>
					<input name='cli_id' id='cli_id'  type='hidden' value='".$_SESSION['cliente_id']."' />
					<input name='orc_tipo_servico_cliente' id='orc_tipo_servico_cliente' placeholder='Digite o serviço que deseja solicitar orçamento'>
					<p>
					<textarea id='orc_observacoes' name='orc_observacoes' placeholder='Observações, detalhar o máximo possível.'></textarea>
					<br><br>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_cadastro_orcamentos' value='Solicitar Orçamento' />&nbsp;&nbsp;&nbsp;&nbsp; 
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}


include('../mod_rodape/rodape.php');
?>
