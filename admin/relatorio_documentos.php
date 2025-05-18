<?php
session_start (); 
$pagina_link = 'relatorio_documentos';
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
$page = "Relatórios &raquo; <a href='relatorio_documentos.php?pagina=relatorio_documentos".$autenticacao."'>Documentos</a>";

$fil_nome = $_REQUEST['fil_nome'];
if($fil_nome == '')
{
	$nome_query = " 1 = 1 ";
}
else
{
	$nome_query = " (cli_nome_razao LIKE '%".$fil_nome."%') ";
}
$fil_tipo_documento = $_REQUEST['fil_tipo_documento'];
if($fil_tipo_documento == '')
{
	$tipo_documento_query = " 1 = 1 ";
	$fil_tipo_documento_n = "Tipo de Documento";
}
else
{
	$tipo_documento_query = " doc_tipo = '".$fil_tipo_documento."' ";
	$sql_tipos_docs = "SELECT * FROM cadastro_tipos_docs WHERE tpd_id = $fil_tipo_documento ";
	$query_tipos_docs = mysql_query($sql_tipos_docs,$conexao);
	$fil_tipo_documento_n = mysql_result($query_tipos_docs,0,'tpd_nome');
}
$fil_data_inicio = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_inicio'])));
$fil_data_fim = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_fim'])));
if($fil_data_inicio == '' && $fil_data_fim == '')
{
	$data_query = " 1 = 1 ";
}
elseif($fil_data_inicio != '' && $fil_data_fim == '')
{
	$data_query = " doc_data_vencimento >= '$fil_data_inicio' ";
}
elseif($fil_data_inicio == '' && $fil_data_fim != '')
{
	$data_query = " doc_data_vencimento <= '$fil_data_fim' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " doc_data_vencimento BETWEEN '$fil_data_inicio' AND '$fil_data_fim ' ";
}
$fil_periodicidade = $_REQUEST['fil_periodicidade'];
if($fil_periodicidade == '')
{
	$periodicidade_query = " 1 = 1 ";
	$fil_periodicidade_n = "Periodicidade";
}
else
{
	$periodicidade_query = " (doc_periodicidade = '".$fil_periodicidade."') ";
	switch($fil_periodicidade)
	{
		case 6: $fil_periodicidade_n = "Semestral";break;
		case 12: $fil_periodicidade_n = "Anual";break;
		case 24: $fil_periodicidade_n = "Bienal";break;
		case 36: $fil_periodicidade_n = "Trienal";break;
		case 48: $fil_periodicidade_n = "Quadrienal";break;
		case 60: $fil_periodicidade_n = "Quinquenal";break;
		
	}
}
$fil_vencido = $_REQUEST['fil_vencido'];
if($fil_vencido == '')
{
	$vencido_query = " 1 = 1 ";
	$fil_vencido = "Vencido";
}
elseif($fil_vencido == 'Sim')
{
	$hoje = date("Y-m-d");
	$vencido_query = " (doc_data_vencimento <= '".$hoje."') ";
}
elseif($fil_vencido == 'Não')
{
	$hoje = date("Y-m-d");
	$vencido_query = " (doc_data_vencimento > '".$hoje."') ";
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
$sql = "SELECT * FROM documento_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
		LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
		LEFT JOIN (orcamento_gerenciar 
			LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
		ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
		WHERE ".$nome_query." AND ".$tipo_documento_query." AND ".$data_query." AND ".$vencido_query." AND ".$periodicidade_query." AND ".$filtro_query."  
		ORDER BY doc_data_cadastro DESC
		";
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "relatorio_documentos")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_documentos.php?pagina=relatorio_documentos".$autenticacao."&filtro=1'>
			<select name='fil_tipo_documento' id='fil_tipo_documento'>
				<option value='$fil_tipo_documento'>$fil_tipo_documento_n</option>
				"; 
				$sql_tipo_documento = " SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome";
				$query_tipo_documento = mysql_query($sql_tipo_documento,$conexao);
				while($row_tipo_documento = mysql_fetch_array($query_tipo_documento) )
				{
					echo "<option value='".$row_tipo_documento['tpd_id']."'>".$row_tipo_documento['tpd_nome']."</option>";
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
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='".implode('/',array_reverse(explode('-',$fil_data_inicio)))."' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='".implode('/',array_reverse(explode('-',$fil_data_fim)))."' onkeypress='return mascaraData(this,event);'>
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
		if ($rows > 0)
		{
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
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$doc_id = mysql_result($query, $x, 'doc_id');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$doc_id = mysql_result($query, $x, 'doc_id');
					$tps_nome = mysql_result($query, $x, 'tps_nome');
					$doc_tipo = mysql_result($query, $x, 'doc_tipo');
					$tpd_nome = mysql_result($query, $x, 'tpd_nome');
					$doc_anexo = mysql_result($query, $x, 'doc_anexo');
					$doc_data_emissao = implode("/",array_reverse(explode("-",mysql_result($query, $x, 'doc_data_emissao'))));
					$doc_periodicidade = mysql_result($query, $x, 'doc_periodicidade');
					$doc_data_vencimento = implode("/",array_reverse(explode("-",mysql_result($query, $x, 'doc_data_vencimento'))));
					switch($doc_periodicidade)
					{
						case 6: $doc_periodicidade_n = "Semestral";break;
						case 12: $doc_periodicidade_n = "Anual";break;
						case 24: $doc_periodicidade_n = "Bienal";break;
						case 36: $doc_periodicidade_n = "Trienal";break;
						case 48: $doc_periodicidade_n = "Quadrienal";break;
						case 60: $doc_periodicidade_n = "Quinquenal";break;
					}
					$doc_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'doc_data_cadastro'),0,10))));
					$doc_hora_cadastro = substr(mysql_result($query, $x, 'doc_data_cadastro'),11,5);
					
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
						<a href='relatorio_documentos.php?pagina=editar_relatorio_documentos&cha_id=$cha_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o cliente <b>$cha_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'relatorio_documentos.php?pagina=relatorio_documentos&action=excluir&cha_id=$cha_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
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