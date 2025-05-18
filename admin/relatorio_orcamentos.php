<?php
session_start (); 
$pagina_link = 'relatorio_orcamentos';
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
$page = "Relatórios &raquo; <a href='relatorio_orcamentos.php?pagina=relatorio_orcamentos".$autenticacao."'>Orçamentos</a>";

$fil_orc = $_REQUEST['fil_orc'];
if($fil_orc == '')
{
	$orc_query = " 1 = 1 ";
}
else
{
	$orc_query = " (orc_id LIKE '%".$fil_orc."%') ";
}
$fil_nome = $_REQUEST['fil_nome'];
if($fil_nome == '')
{
	$nome_query = " 1 = 1 ";
}
else
{
	$nome_query = " (cli_nome_razao LIKE '%".$fil_nome."%') ";
}
$fil_tipo_servico = $_REQUEST['fil_tipo_servico'];
if($fil_tipo_servico == '')
{
	$tipo_servico_query = " 1 = 1 ";
	$fil_tipo_servico_n = "Tipo de Serviço Prestado";
}
else
{
	$tipo_servico_query = " orc_tipo_servico = '".$fil_tipo_servico."' ";
	$sql_tipos_servicos = "SELECT * FROM cadastro_tipos_servicos WHERE tps_id = $fil_tipo_servico ";
	$query_tipos_servicos = mysql_query($sql_tipos_servicos,$conexao);
	$fil_tipo_servico_n = mysql_result($query_tipos_servicos,0,'tps_nome');
}
$fil_data_inicio = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_inicio'])));
$fil_data_fim = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_fim'])));
if($fil_data_inicio == '' && $fil_data_fim == '')
{
	$data_query = " 1 = 1 ";
}
elseif($fil_data_inicio != '' && $fil_data_fim == '')
{
	$data_query = " orc_data_cadastro >= '$fil_data_inicio' ";
}
elseif($fil_data_inicio == '' && $fil_data_fim != '')
{
	$data_query = " orc_data_cadastro <= '$fil_data_fim 23:59:59' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " orc_data_cadastro BETWEEN '$fil_data_inicio' AND '$fil_data_fim 23:59:59' ";
}
$fil_status = $_REQUEST['fil_status'];
if($fil_status == '')
{
	$status_query = " 1 = 1 ";
	$fil_status_n = "Status";
}
else
{
	$status_query = " (sto_status = '".$fil_status."') ";
	switch($fil_status)
	{
		case 1: $fil_status_n = "<span class='laranja'>Pendente</span>";break;
		case 2: $fil_status_n = "<span class='azul'>Calculado</span>";break;
		case 3: $fil_status_n = "<span class='verde'>Aprovado</span>";break;
		case 4: $fil_status_n = "<span class='vermelho'>Reprovado</span>";break;
	}
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
$sql = "SELECT * FROM orcamento_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
		LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
		LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
		WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND 
			  ".$orc_query." AND ".$nome_query." AND ".$tipo_servico_query." AND ".$data_query." AND ".$status_query." AND ".$filtro_query."  
		ORDER BY orc_data_cadastro DESC
		";
		
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "relatorio_orcamentos")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_orcamentos.php?pagina=relatorio_orcamentos".$autenticacao."&filtro=1'>
			<input name='fil_orc' id='fil_orc' value='$fil_orc' placeholder='N° Orçamento'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<select name='fil_tipo_servico' id='fil_tipo_servico'>
				<option value='$fil_tipo_servico'>$fil_tipo_servico_n</option>
				"; 
				$sql_tipo_servico = " SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome";
				$query_tipo_servico = mysql_query($sql_tipo_servico,$conexao);
				while($row_tipo_servico = mysql_fetch_array($query_tipo_servico) )
				{
					echo "<option value='".$row_tipo_servico['tps_id']."'>".$row_tipo_servico['tps_nome']."</option>";
				}
				echo "
				<option value=''>Todos</option>
			</select>
			<select name='fil_status' id='fil_status'>
				<option value='$fil_status'>$fil_status_n</option>
				<option value='1'>Pendente</option>
				<option value='2'>Calculado</option>
				<option value='3'>Aprovado</option>
				<option value='4'>Reprovado</option>
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
			<img src='$logo' border='0' valign='middle' class='logo' /> 
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>N° Orçamento</td>
					<td class='titulo_tabela'>Cliente</td>
					<td class='titulo_tabela'>Serviço</td>
					<td class='titulo_tabela' align='center'>Status</td>
					<td class='titulo_tabela' align='center'>Data Cadastro</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$orc_id = mysql_result($query, $x, 'orc_id');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$tps_nome = mysql_result($query, $x, 'tps_nome');
					if($tps_nome == ''){$tps_nome = mysql_result($query, $x, 'orc_tipo_servico_cliente')."<br><span class='detalhe'>Digitado pelo cliente</span>";}
					$sto_status = mysql_result($query, $x, 'sto_status');
					$orc_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'orc_data_cadastro'),0,10))));
					$orc_hora_cadastro = substr(mysql_result($query, $x, 'orc_data_cadastro'),11,5);
					
					switch($sto_status)
					{
						case 1: $sto_status_n = "<span class='laranja'>Pendente</span>";break;
						case 2: $sto_status_n = "<span class='azul'>Calculado</span>";break;
						case 3: $sto_status_n = "<span class='verde'>Aprovado</span>";break;
						case 4: $sto_status_n = "<span class='vermelho'>Reprovado</span>";break;
					}
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
						<a href='relatorio_orcamentos.php?pagina=editar_relatorio_orcamentos&cha_id=$cha_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o cliente <b>$cha_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'relatorio_orcamentos.php?pagina=relatorio_orcamentos&action=excluir&cha_id=$cha_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$orc_id</td>
							  <td>$cli_nome_razao</td>
							  <td>$tps_nome</td>
							  <td align=center>$sto_status_n</td>
							  <td align='center'>$orc_data_cadastro<br><span class='detalhe'>$orc_hora_cadastro</span></td>
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