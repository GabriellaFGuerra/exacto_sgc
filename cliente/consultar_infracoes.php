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
$fil_bloco = $_REQUEST['fil_bloco'];
if($fil_bloco == '')
{
	$bloco_query = " 1 = 1 ";
}
else
{
	$bloco_query = " (inf_bloco LIKE '%".$fil_bloco."%') ";
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
$fil_apto = $_REQUEST['fil_apto'];
if($fil_apto == '')
{
	$apto_query = " 1 = 1 ";
}
else
{
	$apto_query = " (inf_apto LIKE '%".$fil_apto."%') ";
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
$fil_inf_tipo = $_REQUEST['fil_inf_tipo'];
if($fil_inf_tipo == '')
{
	$tipo_inf_query = " 1 = 1 ";
	$fil_inf_tipo_n = "Tipo de Infração";
}
else
{
	$tipo_inf_query = " (inf_tipo = '".$fil_inf_tipo."') ";
	$fil_inf_tipo_n = $fil_inf_tipo;
}
$sql = "SELECT * FROM infracoes_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
		LEFT JOIN recurso_gerenciar ON recurso_gerenciar.rec_infracao = infracoes_gerenciar.inf_id
		WHERE cli_id = ".$_SESSION['cliente_id']." AND ".$proprietario_query." AND ".$tipo_inf_query." AND ".$assunto_query." AND ".$bloco_query." AND ".$apto_query." 
		ORDER BY inf_data DESC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM infracoes_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
		LEFT JOIN recurso_gerenciar ON recurso_gerenciar.rec_infracao = infracoes_gerenciar.inf_id
		WHERE cli_id = ".$_SESSION['cliente_id']." AND ".$proprietario_query." AND ".$tipo_inf_query." AND ".$assunto_query." AND ".$bloco_query." AND ".$apto_query." ";
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);

if($pagina == 'consultar_infracoes')
{
	echo "
	<div class='centro'>
		<div class='titulo'> Consultar Infraçoes  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='consultar_infracoes.php?pagina=consultar_infracoes".$autenticacao."'>
			<input name='fil_bloco' id='fil_bloco' value='$fil_bloco' placeholder='Bloco/Quadra'>
			<input name='fil_apto' id='fil_apto' value='$fil_apto' placeholder='Unidade.'>
			<input name='fil_proprietario' id='fil_proprietario' value='$fil_proprietario' placeholder='Proprietário'>
			<input name='fil_assunto' id='fil_assunto' value='$fil_assunto' placeholder='Assunto'>
			<select name='fil_inf_tipo' id='fil_inf_tipo'>
				<option value='$fil_inf_tipo'>$fil_inf_tipo_n</option>
				<option value='Notificação de advertência por infração disciplinar'>Notificação de advertência por infração disciplinar</option>
				<option value='Multa por Infração Interna'>Multa por Infração Interna</option>
				<option value='Notificação de ressarcimento'>Notificação de ressarcimento</option>
				<option value='Comunicação interna'>Comunicação interna</option>
				<option value=''>Todos</option>
			</select>
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
		";
		if ($rows > 0)
		{
			
			echo "
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>N.</td>
					<td class='titulo_tabela'>Tipo</td>
					<td class='titulo_tabela'>Assunto</td>
					<td class='titulo_tabela'>Proprietário</td>
					<td class='titulo_tabela'>Bloco/Quadra/Ap</td>
					<td class='titulo_tabela' align='center'>Data</td>
					<td class='titulo_tabela' align='center'>Advertência/multa</td>
					<td class='titulo_tabela' align='center'>Comprovante</td>
					<td class='titulo_tabela' align='center'>Recurso</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$inf_id = mysql_result($query, $x, 'inf_id');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$inf_ano = mysql_result($query, $x, 'inf_ano');
					$inf_tipo = mysql_result($query, $x, 'inf_tipo');
					$inf_assunto = mysql_result($query, $x, 'inf_assunto');
					$inf_proprietario = mysql_result($query, $x, 'inf_proprietario');
					$inf_bloco = mysql_result($query, $x, 'inf_bloco');
					$inf_apto = mysql_result($query, $x, 'inf_apto');
					$inf_comprovante = mysql_result($query, $x, 'inf_comprovante');
					$inf_data = implode("/",array_reverse(explode("-",mysql_result($query, $x,'inf_data'))));
					$rec_id = mysql_result($query, $x, 'rec_id');
					$rec_status = mysql_result($query, $x, 'rec_status');
					
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
						   	<td>".str_pad($inf_id,3,"0",STR_PAD_LEFT)."/".$inf_ano."</td>
						 	<td>$inf_tipo</td>
							<td>$inf_assunto</td>
							<td>$inf_proprietario</td>
							<td>$inf_bloco/$inf_apto</td>
							<td align='center'>$inf_data</td>
							<td align='center'><a href='infracoes_imprimir.php?inf_id=$inf_id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
							<td align='center'>";if($inf_comprovante != ''){echo "<a href='".$inf_comprovante."' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";} echo "</td>
						  	<td align='right'>
							  	";
							  	if($rec_id != '')
							  	{
									echo "$rec_status <a href='consultar_recurso.php?pagina=consultar_recurso&rec_id=$rec_id$autenticacao'><img src='../imagens/icon-exibir.png' valign='middle'></a>";
								}
								else
								{
									echo "";
									
								}
								echo "</td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=consultar_infracoes&fil_doc_tipo=$fil_doc_tipo&fil_data_inicio=$fil_data_inicio&fil_data_fim=$fil_data_fim".$autenticacao."";
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
