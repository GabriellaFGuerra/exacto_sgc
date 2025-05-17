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
$num_por_pagina = 10;
if(!$pag){$primeiro_registro = 0; $pag = 1;}
else{$primeiro_registro = ($pag - 1) * $num_por_pagina;}

$fil_doc_tipo = $_REQUEST['fil_doc_tipo'];
if($fil_doc_tipo == '')
{
	$tipo_doc_query = " 1 = 1 ";
	$fil_doc_tipo_n = "Tipo de documento";
}
else
{
	$tipo_doc_query = " (doc_tipo = '".$fil_doc_tipo."') ";
	$sql_tipo_doc = "SELECT * FROM cadastro_tipos_docs WHERE tpd_id = $fil_doc_tipo";
	$query_tipo_doc = mysql_query($sql_tipo_doc,$conexao);
	$fil_doc_tipo_n = mysql_result($query_tipo_doc,0,'tpd_nome');
}
$fil_data_inicio = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_inicio'])));
$fil_data_fim = implode('-',array_reverse(explode('/',$_REQUEST['fil_data_fim'])));
if($fil_data_inicio == '' && $fil_data_fim == '')
{
	$data_query = " 1 = 1 ";
}
elseif($fil_data_inicio != '' && $fil_data_fim == '')
{
	$data_query = " doc_data_cadastro >= '$fil_data_inicio' ";
}
elseif($fil_data_inicio == '' && $fil_data_fim != '')
{
	$data_query = " doc_data_cadastro <= '$fil_data_fim 23:59:59' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " doc_data_cadastro BETWEEN '$fil_data_inicio' AND '$fil_data_fim 23:59:59' ";
}
$sql = "SELECT * FROM documento_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
		LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
		LEFT JOIN (orcamento_gerenciar 
			LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
		ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
		WHERE cli_id = ".$_SESSION['cliente_id']." AND ".$tipo_doc_query." AND ".$data_query." 
		ORDER BY doc_data_cadastro DESC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM documento_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
		LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
		LEFT JOIN (orcamento_gerenciar 
			LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
		ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
		WHERE cli_id = ".$_SESSION['cliente_id']." AND ".$tipo_doc_query." AND ".$data_query." ";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == 'consultar_documento')
{
	echo "
	<div class='centro'>
		<div class='titulo'> Consultar Documentos  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='consultar_documento.php?pagina=consultar_documento".$autenticacao."'>
			<select name='fil_doc_tipo' id='fil_doc_tipo'>
				<option value='$fil_doc_tipo'>$fil_doc_tipo_n</option>
				"; 
				$sql_tpd = " SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC";
				$query_tpd = mysql_query($sql_tpd,$conexao);
				while($row_tpd = mysql_fetch_array($query_tpd) )
				{
					echo "<option value='".$row_tpd['tpd_id']."'>".$row_tpd['tpd_nome']."</option>";
				}
				echo "
				<option value=''>Todos</option>
			</select>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='".implode('/',array_reverse(explode('-',$fil_data_inicio)))."' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='".implode('/',array_reverse(explode('-',$fil_data_fim)))."' onkeypress='return mascaraData(this,event);'>
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
		";
		if ($rows > 0)
		{
			echo "
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>Tipo de Documento</td>
					<td class='titulo_tabela'>Orçamento</td>
					<td class='titulo_tabela' align='center'>Data Emissão</td>
					<td class='titulo_tabela' align='center'>Periodicidade</td>
					<td class='titulo_tabela' align='center'>Data Vencimento</td>
					<td class='titulo_tabela' align='center'>Data Cadastro</td>
					<td class='titulo_tabela' align='center'>Anexo</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$doc_id = mysql_result($query, $x, 'doc_id');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$orc_id = mysql_result($query, $x, 'orc_id');
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
					echo "<tr class='$c1'>
							  <td>$tpd_nome</td>
							  <td>$orc_id ($tps_nome)</td>
							  <td align=center>$doc_data_emissao</td>
							  <td align=center>$doc_periodicidade_n</td>
							  <td align=center>$doc_data_vencimento</td>
							  <td align='center'>$doc_data_cadastro<br><span class='detalhe'>$doc_hora_cadastro</span></td>
							  <td align='center'>";if($doc_anexo != ''){echo "<a href='".$doc_anexo."' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";} echo "</td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=consultar_documento&fil_doc_tipo=$fil_doc_tipo&fil_data_inicio=$fil_data_inicio&fil_data_fim=$fil_data_fim".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum documento cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}


include('../mod_rodape/rodape.php');
?>
