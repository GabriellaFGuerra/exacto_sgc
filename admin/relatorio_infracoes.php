<?php
session_start (); 
$pagina_link = 'relatorio_infracoes';
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
$page = "Relatórios &raquo; <a href='relatorio_infracoes.php?pagina=relatorio_infracoes".$autenticacao."'>Infrações</a>";

$fil_nome = $_REQUEST['fil_nome'];
if($fil_nome == '')
{
	$nome_query = " 1 = 1 ";
}
else
{
	$nome_query = " (cli_nome_razao LIKE '%".$fil_nome."%') ";
}

$fil_proprietario = $_REQUEST['fil_proprietario'];
if($fil_proprietario == '')
{
	$proprietario_query = " 1 = 1 ";
}
else
{
	$proprietario_query = " (inf_proprietario LIKE '%".$fil_proprietario."%') ";
}
$fil_assunto = $_REQUEST['fil_assunto'];
if($fil_assunto == '')
{
	$assunto_query = " 1 = 1 ";
}
else
{
	$assunto_query = " (inf_assunto LIKE '%".$fil_assunto."%') ";
}
$fil_bloco = $_REQUEST['fil_bloco'];
if($fil_bloco == '')
{
	$bloco_query = " 1 = 1 ";
}
else
{
	$bloco_query = " (inf_bloco LIKE '%".$fil_bloco."%') ";
}
$fil_apto = $_REQUEST['fil_apto'];
if($fil_apto == '')
{
	$apto_query = " 1 = 1 ";
}
else
{
	$apto_query = " (inf_apto LIKE '%".$fil_apto."%') ";
}

$fil_inf_tipo = $_REQUEST['fil_inf_tipo'];
if($fil_inf_tipo == '')
{
	$tipo_inf_query = " 1 = 1 ";
	$fil_inf_tipo_n = "Tipo de infracoes";
}
else
{
	$tipo_inf_query = " (inf_tipo = '".$fil_inf_tipo."') ";
	$fil_inf_tipo_n = $fil_inf_tipo;
}

$fil_data_inicio = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_inicio'])));
$fil_data_fim = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_fim'])));
if($fil_data_inicio == '' && $fil_data_fim == '')
{
	$data_query = " 1 = 1 ";
}
elseif($fil_data_inicio != '' && $fil_data_fim == '')
{
	$data_query = " inf_data >= '$fil_data_inicio' ";
}
elseif($fil_data_inicio == '' && $fil_data_fim != '')
{
	$data_query = " inf_data <= '$fil_data_fim' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " inf_data BETWEEN '$fil_data_inicio' AND '$fil_data_fim ' ";
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
$sql = "SELECT * FROM infracoes_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
		WHERE ".$nome_query." AND ".$proprietario_query." AND ".$assunto_query." AND ".$bloco_query." AND ".$apto_query." AND ".$tipo_inf_query." AND ".$data_query." AND ".$filtro_query."  
		ORDER BY inf_data DESC
		";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "relatorio_infracoes")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_infracoes.php?pagina=relatorio_infracoes".$autenticacao."&filtro=1'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input name='fil_proprietario' id='fil_proprietario' value='$fil_proprietario' placeholder='Proprietário'>
			<input name='fil_assunto' id='fil_assunto' value='$fil_assunto' placeholder='Assunto'>
			<input name='fil_bloco' id='fil_bloco' value='$fil_bloco' placeholder='Bloco'>
			<input name='fil_apto' id='fil_apto' value='$fil_apto' placeholder='Apto.'>
			<select name='fil_inf_tipo' id='fil_inf_tipo'>
				<option value='$fil_inf_tipo'>$fil_inf_tipo_n</option>
				<option value='Notificação de advertência por infração disciplinar'>Notificação de advertência por infração disciplinar</option>
				<option value='Multa por Infração Interna'>Multa por Infração Interna</option>
				<option value='Notificação de ressarcimento'>Notificação de ressarcimento</option>
				<option value='Comunicação interna'>Comunicação interna</option>
				<option value=''>Todos</option>
			</select>
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
					<td class='titulo_tabela'>N.</td>
					<td class='titulo_tabela'>Tipo</td>
					<td class='titulo_tabela'>Assunto</td>
					<td class='titulo_tabela'>Cliente</td>
					<td class='titulo_tabela'>Proprietário</td>
					<td class='titulo_tabela' align='center'>Bloco/Apto</td>
					<td class='titulo_tabela' align='center'>Data</td>					
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$inf_id = mysql_result($query, $x, 'inf_id');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$inf_ano = mysql_result($query, $x, 'inf_ano');
					$inf_tipo = mysql_result($query, $x, 'inf_tipo');
					$inf_proprietario = mysql_result($query, $x, 'inf_proprietario');
					$inf_data = implode("/",array_reverse(explode("-",mysql_result($query, $x, 'inf_data'))));
					$inf_bloco = mysql_result($query, $x, 'inf_bloco');
					$inf_apto = mysql_result($query, $x, 'inf_apto');
					$inf_assunto = mysql_result($query, $x, 'inf_assunto');
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
						<a href='relatorio_infracoes.php?pagina=editar_relatorio_infracoes&cha_id=$cha_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o cliente <b>$cha_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'relatorio_infracoes.php?pagina=relatorio_infracoes&action=excluir&cha_id=$cha_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>".str_pad($inf_id,3,"0",STR_PAD_LEFT)."/".$inf_ano."</td>
							  <td>$inf_tipo</td>
							  <td>$inf_assunto</td>
							  <td>$cli_nome_razao</td>
							  <td>$inf_proprietario</td>
							  <td align='center'>$inf_bloco/$inf_apto</td>
							  <td align=center>$inf_data</td>
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