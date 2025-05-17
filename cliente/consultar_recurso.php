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
$rec_id = $_GET['rec_id'];
$sql = "SELECT * FROM recurso_gerenciar 
		LEFT JOIN ( infracoes_gerenciar 
			LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente	)
		ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao		
		WHERE cli_id = ".$_SESSION['cliente_id']." AND rec_id = $rec_id
		ORDER BY inf_data DESC
		";
$cnt = "SELECT COUNT(*) FROM infracoes_gerenciar 
		SELECT * FROM recurso_gerenciar 
				LEFT JOIN ( infracoes_gerenciar 
					LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente	)
				ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao		
		WHERE cli_id = ".$_SESSION['cliente_id']." AND rec_id = $rec_id ";
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);

if($pagina == 'consultar_recurso')
{
	if($rows > 0)
	{
		$inf_cliente = mysql_result($query, 0, 'inf_cliente');
		$cli_nome_razao = mysql_result($query, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($query, 0, 'cli_cnpj');
		$rec_assunto = mysql_result($query, 0, 'rec_assunto');
		$rec_descricao = mysql_result($query, 0, 'rec_descricao');
		$rec_recurso = mysql_result($query, 0, 'rec_recurso');
		$rec_status = mysql_result($query, 0, 'rec_status');
		echo "
		<form name='form_recurso_gerenciar' id='form_recurso_gerenciar' enctype='multipart/form-data' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=adicionar_recurso&inf_id=$inf_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> Consultar Recurso  </div>
			<table align='center' cellspacing='0' width='90%'>
				<tr>
					<td align='left'>
						<input type='hidden' name='inf_id' id='inf_id' value='$inf_id' placeholder='ID'>
						<b>Recurso:</b> <a href='$rec_recurso' target='_blank'><img src='../imagens/icon-pdf.png' border='0' valign='middle'></a>
						<p>
						<b>Status:</b> $rec_status
						<p>
						Mogi das Cruzes, ".date('d/m/Y')."
						<p>
						$rec_assunto
						<p>
						$rec_descricao
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='consultar_infracoes.php?pagina=consultar_infracoes$autenticacao'; value='Voltar'/></center>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'>   </div>
		</div>
		</form>
		";
	}
}


include('../mod_rodape/rodape.php');
?>
