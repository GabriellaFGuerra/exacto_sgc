<?php
session_start (); 
$pagina_link = 'relatorio_malotes';
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
<script language="javascript">
jQuery(document).ready(function()
{
	jQuery(".toggle_container").show();
 	jQuery(".toggle_container_info").hide();
 
	jQuery("h2.trigger").click(function(){
		jQuery(this).toggleClass("active").next().slideToggle("slow");
		return false;
	});
});
</script>
</head>
<body>
<?php	
include		('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogin.php');
include		("../mod_topo/topo.php");
require_once('../mod_includes/php/verificapermissao.php');

?>

<?php
$page = "Relatórios &raquo; <a href='relatorio_malotes.php?pagina=relatorio_malotes".$autenticacao."'>Malotes</a>";

$fil_malote = $_REQUEST['fil_malote'];
if($fil_malote == '')
{
	$malote_query = " 1 = 1 ";
}
else
{
	$malote_query = " (mal_id = '".$fil_malote."') ";
}
$fil_lacre = $_REQUEST['fil_lacre'];
if($fil_lacre == '')
{
	$lacre_query = " 1 = 1 ";
}
else
{
	$lacre_query = " (mal_lacre LIKE '%".$fil_lacre."%') ";
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
$fil_data_inicio = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_inicio'])));
$fil_data_fim = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_fim'])));
if($fil_data_inicio == '' && $fil_data_fim == '')
{
	$data_query = " 1 = 1 ";
}
elseif($fil_data_inicio != '' && $fil_data_fim == '')
{
	$data_query = " mai_data_vencimento >= '$fil_data_inicio' ";
}
elseif($fil_data_inicio == '' && $fil_data_fim != '')
{
	$data_query = " mai_data_vencimento <= '$fil_data_fim 23:59:59' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " mai_data_vencimento BETWEEN '$fil_data_inicio' AND '$fil_data_fim 23:59:59' ";
}
$fil_baixado = $_REQUEST['fil_baixado'];
if($fil_baixado == '')
{
	$baixado_query = " 1 = 1 ";
	$fil_baixado_n = "Baixado?";
}
elseif($fil_baixado == 1)
{
	$baixado_query = " (mai_baixado = ".$fil_baixado.") ";
	switch($fil_baixado)
	{
		case 0: $fil_baixado_n = "Não";break;
		case 1: $fil_baixado_n = "Sim";break;
		case '': $fil_baixado_n = "Todos";break;
	}
}
elseif($fil_baixado == '0')
{
	$baixado_query = " (mai_baixado IS NULL) ";
	switch($fil_baixado)
	{
		case 0: $fil_baixado_n = "Não";break;
		case 1: $fil_baixado_n = "Sim";break;
		case '': $fil_baixado_n = "Todos";break;
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
$sql = "SELECT * FROM malote_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
		WHERE ".$malote_query." AND ".$lacre_query." AND ".$nome_query." AND ".$filtro_query."
			  AND mal_id IN 
			  (
			  	SELECT mal_id FROM malote_itens
				LEFT JOIN malote_gerenciar ON malote_gerenciar.mal_id = malote_itens.mai_malote 
				WHERE  ".$baixado_query." AND ".$data_query."  
			   ) 
		ORDER BY mal_data_cadastro DESC
		";
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "relatorio_malotes")
{
	echo "
	<div class='container'>
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_malotes.php?pagina=relatorio_malotes".$autenticacao."&filtro=1'>
			<input name='fil_malote' id='fil_malote' value='$fil_malote' placeholder='N° malote'>
			<input name='fil_lacre' id='fil_lacre' value='$fil_lacre' placeholder='N° lacre'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='".implode('/',array_reverse(explode('-',$fil_data_inicio)))."' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='".implode('/',array_reverse(explode('-',$fil_data_fim)))."' onkeypress='return mascaraData(this,event);'>
			<select name='fil_baixado' id='fil_baixado'>
				<option value='$fil_baixado'>$fil_baixado_n</option>
				<option value='1'>Sim</option>
				<option value='0'>Não</option>
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
			<img src='$logo' border='0' valign='middle' class='logo' /> 
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>N° Malote</td>
					<td class='titulo_tabela'>N° Lacre</td>
					<td class='titulo_tabela'>Cliente</td>
					<td class='titulo_tabela'>Observação</td>
					<td class='titulo_tabela' align='center'>Data Cadastro</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$mal_id = mysql_result($query, $x, 'mal_id');
					$mal_lacre = mysql_result($query, $x, 'mal_lacre');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$mal_observacoes = mysql_result($query, $x, 'mal_observacoes');
					$mal_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'mal_data_cadastro'),0,10))));
					$mal_hora_cadastro = substr(mysql_result($query, $x, 'mal_data_cadastro'),11,5);
					
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
						<a href='relatorio_malotes.php?pagina=editar_relatorio_malotes&cha_id=$cha_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o cliente <b>$cha_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'relatorio_malotes.php?pagina=relatorio_malotes&action=excluir&cha_id=$cha_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1' >
							  <td style='border-top:1px solid #DADADA'>$mal_id</td>
							  <td style='border-top:1px solid #DADADA'>$mal_lacre</td>
							  <td style='border-top:1px solid #DADADA'>$cli_nome_razao</td>
							  <td style='border-top:1px solid #DADADA'>$mal_observacoes</td>
							  <td style='border-top:1px solid #DADADA' align='center'>$mal_data_cadastro<br><span class='detalhe'>$mal_hora_cadastro</span></td>
						  </tr>";
					$sql_itens = "SELECT * FROM  malote_itens WHERE mai_malote = $mal_id AND ".$baixado_query." AND ".$data_query."  ";
					$query_itens = mysql_query($sql_itens,$conexao);
					$rows_itens = mysql_num_rows($query_itens);
					if($rows_itens > 0)
					{
						for($y=0; $y < $rows_itens; $y++)
						{
							$mai_fornecedor = mysql_result($query_itens,$y,'mai_fornecedor');
							$mai_tipo_documento = mysql_result($query_itens,$y,'mai_tipo_documento');
							$mai_num_cheque = mysql_result($query_itens,$y,'mai_num_cheque');
							$mai_valor = number_format(mysql_result($query_itens,$y,'mai_valor'),2,',','.');
							$mai_data_vencimento = implode("/",array_reverse(explode("-",mysql_result($query_itens,$y,'mai_data_vencimento'))));
							$mai_baixado = mysql_result($query_itens,$y,'mai_baixado');
							$mai_data_baixa = implode("/",array_reverse(explode("-",substr(mysql_result($query_itens, $y, 'mai_data_baixa'),0,10))));
							$mai_hora_baixa = substr(mysql_result($query_itens, $y, 'mai_data_baixa'),11,5);
							switch($mai_baixado)
							{
								case 0: $mai_baixado_n = "<span class='vermelho'>Não</span>";break;
								case 1: $mai_baixado_n = "<span class='verde'>Sim</span>";break;
							}
							echo "
							<tr class='$c1'>
								<td colspan='5'>
								";
									if($y==0)
									{
										echo "
										<h2 class='trigger'><a href='#'> &nbsp;&nbsp;&nbsp;&nbsp; Documentos do malote:</a></h2>
										<div class='toggle_container'>
										<div class='block'>
										<table align='center' width='100%' border='0' cellspacing='0' cellpadding='3' class='bordatabela2'>
										<tr>
											<td class='titulo_tabela2'>Fornecedor</td>
											<td class='titulo_tabela2'>Tipo Documento</td>
											<td class='titulo_tabela2'>N° Cheque</td>
											<td class='titulo_tabela2'>Valor</td>
											<td class='titulo_tabela2' align='center'>Data Vencimento</td>
											<td class='titulo_tabela2' align='center'>Baixado?</td>
											<td class='titulo_tabela2' align='center'>Data Baixa</td>
										</tr>
										<tr>
											<td>".$mai_fornecedor."</td>
											<td>".$mai_tipo_documento."</td>
											<td>".$mai_num_cheque."</td>
											<td>".$mai_valor."</td>
											<td align='center'>".$mai_data_vencimento."</td>
											<td align='center'>".$mai_baixado_n."</td>
											<td align='center'>$mai_data_baixa<br><span class='detalhe'>$mai_hora_baixa</span></td>
										</tr>
										";
									}
									else
									{
										echo "
										<tr>
											<td>".$mai_fornecedor."</td>
											<td>".$mai_tipo_documento."</td>
											<td>".$mai_num_cheque."</td>
											<td>R$ ".$mai_valor."</td>
											<td align='center'>".$mai_data_vencimento."</td>
											<td align='center'>".$mai_baixado_n."</td>
											<td align='center'>$mai_data_baixa<br><span class='detalhe'>$mai_hora_baixa</span></td>
										</tr>
										";
									}
									echo "
								</td>
							</tr>
							";
						}
						echo "</table></div></div>";
					}
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
	</div>
	</div>";
}

include('../mod_rodape/rodape.php');
?>
<script type="text/javascript" src="../mod_includes/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="../mod_includes/js/elementPrint.js"></script>
</body>
</html>