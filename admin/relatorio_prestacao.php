<?php
session_start (); 
$pagina_link = 'relatorio_prestacao';
include		('../mod_includes/php/connect.php');
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $titulo;?></title>
<meta name="author" content="MogiComp">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="../imagens/favicon.png">
<?php include("../css/style.php"); ?>
<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
<!-- TOOLBAR -->
<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
<!-- TOOLBAR -->
</head>
<body>
<?php	
include		('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogin.php');
include		("../mod_topo/topo.php");
require_once('../mod_includes/php/verificapermissao.php');

?>

<?php
$page = "Relatórios &raquo; <a href='relatorio_prestacao.php?pagina=relatorio_prestacao".$autenticacao."'>Prestação de Contas</a>";

$fil_nome = $_REQUEST['fil_nome'];
if($fil_nome == '')
{
	$nome_query = " 1 = 1 ";
}
else
{
	$nome_query = " (cli_nome_razao LIKE '%".$fil_nome."%') ";
}
$fil_referencia = $_REQUEST['fil_referencia'];
if($fil_referencia == '')
{
	$referencia_query = " 1 = 1 ";
}
else
{
	$referencia_query = " (pre_referencia = '".$fil_referencia."') ";
}
$fil_data_inicio = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_inicio'])));
$fil_data_fim = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_fim'])));
if($fil_data_inicio == '' && $fil_data_fim == '')
{
	$data_query = " 1 = 1 ";
}
elseif($fil_data_inicio != '' && $fil_data_fim == '')
{
	$data_query = " pre_data_cadastro >= '$fil_data_inicio' ";
}
elseif($fil_data_inicio == '' && $fil_data_fim != '')
{
	$data_query = " pre_data_cadastro <= '$fil_data_fim' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " pre_data_cadastro BETWEEN '$fil_data_inicio' AND '$fil_data_fim ' ";
}
$filtro = $_REQUEST['filtro'];
if($filtro == '')
{
	$filtro_query = " 1 = 0 ";
}
else
{
	$filtro_query = " 1 = 1 ";
}
$sql = "SELECT * FROM prestacao_gerenciar 
		LEFT JOIN ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
		WHERE ucl_usuario = '".$_SESSION['usuario_id']."'  AND ".$nome_query." AND ".$referencia_query." AND ".$data_query."  AND ".$filtro_query."  
		ORDER BY pre_data_cadastro DESC
		";
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "relatorio_prestacao")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_prestacao.php?pagina=relatorio_prestacao".$autenticacao."&filtro=1'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input name='fil_referencia' id='fil_referencia' value='$fil_referencia' placeholder='Referência '>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='".implode('/',array_reverse(explode('-',$fil_data_inicio)))."' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='".implode('/',array_reverse(explode('-',$fil_data_fim)))."' onkeypress='return mascaraData(this,event);'>
			<input type='submit' value='Filtrar'> 
			<input type='button' onclick=\"PrintDiv('imprimir');\" value='Imprimir' />
			</form>
		</div>
		<div class='contentPrint' id='imprimir'>
		";
		if ($rows > 0)
		{
			echo "
			<br>
			<img src='$logo' border='0' valign='middle' class='logo' /> 
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
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$pre_id = mysql_result($query, $x, 'pre_id');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$pre_referencia = mysql_result($query, $x, 'pre_referencia');
					$pre_data_envio = implode("/",array_reverse(explode("-",mysql_result($query, $x, 'pre_data_envio'))));
					$pre_enviado_por = mysql_result($query, $x, 'pre_enviado_por');
					$pre_observacoes = mysql_result($query, $x, 'pre_observacoes');
					$pre_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'pre_data_cadastro'),0,10))));
					$pre_hora_cadastro = substr(mysql_result($query, $x, 'pre_data_cadastro'),11,5);
					$pre_comprovante = mysql_result($query, $x, 'pre_comprovante');
					if ($c == 0)
					{
					 $c1 = "linhaimpar";
					 $c=1;
					}
					else
					{
					$c1 = "linhapar";
					 $c=0;
					} 
					echo "
					<script type='text/javascript'>
						jQuery(document).ready(function($) {
					
							// Define any icon actions before calling the toolbar
							$('.toolbar-icons a').on('click', function( event ) {
								$(this).click();
								
							});
							$('#normal-button-$cha_id').toolbar({content: '#user-options-$cha_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$cha_id' class='toolbar-icons' style='display: none;'>
						<a href='cadastro_unidades.php?pagina=cadastro_unidades&cha_id=$cha_id$autenticacao'><img border='0' src='../imagens/icon-unidade.png'></a>
						<a href='relatorio_prestacao.php?pagina=editar_relatorio_prestacao&cha_id=$cha_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o cliente <b>$cha_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'relatorio_prestacao.php?pagina=relatorio_prestacao&action=excluir&cha_id=$cha_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$pre_id</td>
							  <td>$cli_nome_razao</td>
							  <td>$pre_referencia</td>
							  <td>$pre_data_envio</td>
							  <td>$pre_enviado_por</td>
							  <td>$pre_observacoes</td>
							  <td align='center'>$pre_data_cadastro<br><span class='detalhe'>$pre_hora_cadastro</span></td>
							  <td align='center'>$doc_data_cadastro<br><span class='detalhe'>$doc_hora_cadastro</span></td>
						  </tr>";
				}
				echo "</table>";
		}
		else
		{
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