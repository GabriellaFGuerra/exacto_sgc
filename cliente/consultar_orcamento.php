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

if($action == "aprovar")
{
	$orc_id = $_GET['orc_id'];
	$orc_data_aprovacao = date("Y-m-d");
	$sql_update_data = "UPDATE orcamento_gerenciar SET orc_data_aprovacao = '".$orc_data_aprovacao."' WHERE orc_id = $orc_id ";
	mysql_query($sql_update_data,$conexao);

	$sql_unidade = "SELECT * FROM cadastro_clientes 
					LEFT JOIN (orcamento_gerenciar 
						LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
					ON orcamento_gerenciar.orc_cliente = cadastro_clientes.cli_id
					WHERE orc_id = $orc_id";
	$query_unidade = mysql_query($sql_unidade,$conexao);
	$rows_unidade = mysql_num_rows($query_unidade);
	if($rows_unidade > 0 )
	{
		$cli_nome_razao = mysql_result($query_unidade,0,'cli_nome_razao');
		$tps_nome = mysql_result($query_unidade,0,'tps_nome');
		if($tps_nome == ''){$tps_nome = mysql_result($query_unidade, 0, 'orc_tipo_servico_cliente');}
	}
	
	$sto_fornecedor_aprovado = $_POST['sto_fornecedor_aprovado'];
	$sql_fornecedor = "SELECT * FROM cadastro_fornecedores WHERE for_id = $sto_fornecedor_aprovado";
	$query_fornecedor = mysql_query($sql_fornecedor,$conexao);
	$rows_fornecedor = mysql_num_rows($query_fornecedor);
	if($rows_fornecedor > 0)
	{
		$for_nome_razao = mysql_result($query_fornecedor,0,'for_nome_razao');
	}
	$sto_observacao = $_POST['sto_observacao'];
	$sql_status = "INSERT INTO cadastro_status_orcamento (
				   sto_orcamento,
				   sto_status,
				   sto_fornecedor_aprovado,
				   sto_observacao 
				   )
				   VALUES
				   (
				   '$orc_id',
				   3,
				   '$sto_fornecedor_aprovado',
				   '$sto_observacao'
				   )";
	if(mysql_query($sql_status,$conexao))
	{
		include("../mail/envia_email_orcamento_aprovado.php");
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Orçamento aprovado com sucesso.<br><br>'+
			'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
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

if($action == "reprovar")
{
	$orc_id = $_GET['orc_id'];
	$orc_data_reprovacao = date("Y-m-d");
	$sql_update_data = "UPDATE orcamento_gerenciar SET orc_data_aprovacao = '".$orc_data_reprovacao."' WHERE orc_id = $orc_id ";
	mysql_query($sql_update_data,$conexao);

	$sql_unidade = "SELECT * FROM cadastro_clientes 
					LEFT JOIN (orcamento_gerenciar 
						LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
					ON orcamento_gerenciar.orc_cliente = cadastro_clientes.cli_id
					WHERE orc_id = $orc_id";
	$query_unidade = mysql_query($sql_unidade,$conexao);
	$rows_unidade = mysql_num_rows($query_unidade);
	if($rows_unidade > 0 )
	{
		$cli_nome_razao = mysql_result($query_unidade,0,'cli_nome_razao');
		$tps_nome = mysql_result($query_unidade,0,'tps_nome');
		if($tps_nome == ''){$tps_nome = mysql_result($query_unidade, 0, 'orc_tipo_servico_cliente');}
	}
	
	/*$sto_fornecedor_aprovado = $_POST['sto_fornecedor_aprovado'];
	$sql_fornecedor = "SELECT * FROM cadastro_fornecedores WHERE for_id = $sto_fornecedor_aprovado";
	$query_fornecedor = mysql_query($sql_fornecedor,$conexao);
	$rows_fornecedor = mysql_num_rows($query_fornecedor);
	if($rows_fornecedor > 0)
	{
		$for_nome_razao = mysql_result($query_fornecedor,0,'for_nome_razao');
	}*/
	$sto_observacao = $_POST['sto_observacao'];
	$sql_status = "INSERT INTO cadastro_status_orcamento (
				   sto_orcamento,
				   sto_status,
				   sto_observacao 
				   )
				   VALUES
				   (
				   '$orc_id',
				   4,
				   '$sto_observacao'
				   )";
	if(mysql_query($sql_status,$conexao))
	{
		include("../mail/envia_email_orcamento_reprovado.php");
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Orçamento reprovado com sucesso.<br><br>'+
			'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
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

$fil_orcamento = $_REQUEST['fil_orcamento'];
if($fil_orcamento == '')
{
	$orcamento_query = " 1 = 1 ";
}
else
{
	$orcamento_query = " (orc_id LIKE '%".$fil_orcamento."%') ";
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
        $status_query = " sto_status = '".$fil_status."' ";
       switch($fil_status)
		{
			case 1: $fil_status_n = "Pendente";break;
			case 2: $fil_status_n = "Calculado";break;
			case 3: $fil_status_n = "Aprovado";break;
			case 4: $fil_status_n = "Reprovado";break;
		}
    }
$sql = "SELECT * FROM orcamento_gerenciar 
		LEFT JOIN cadastro_clientes	ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente 
		LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico 
		LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
		WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND
			  cli_id = ".$_SESSION['cliente_id']." AND ".$orcamento_query." AND ".$data_query." AND ".$status_query." 
		ORDER BY orc_data_cadastro DESC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM orcamento_gerenciar 
		LEFT JOIN cadastro_clientes	ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente 
		LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico 
		LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
		WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND
			  cli_id = ".$_SESSION['cliente_id']." AND ".$orcamento_query." AND ".$data_query." AND ".$status_query."  ";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == 'consultar_orcamento')
{
	echo "
	<div class='centro'>
		<div class='titulo'> Consultar Orçamento  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='consultar_orcamento.php?pagina=consultar_orcamento".$autenticacao."'>
			<input name='fil_orcamento' id='fil_orcamento' value='$fil_orcamento' placeholder='Nº Orçamento'>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='".implode('/',array_reverse(explode('-',$fil_data_inicio)))."' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='".implode('/',array_reverse(explode('-',$fil_data_fim)))."' onkeypress='return mascaraData(this,event);'>
			<select name='fil_status' id='fil_status'>
					<option value='$fil_status'>$fil_status_n</option>
					<option value='1'>Pendente</option>
					<option value='2'>Calculado</option>
					<option value='3'>Aprovado</option>
					<option value='4'>Reprovado</option>
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
					<td class='titulo_tabela'>N. Orçamento</td>
					<td class='titulo_tabela'>Tipo de Serviço</td>
					<td class='titulo_tabela'>Observações</td>
					<td class='titulo_tabela' align='center'>Data de Abertura</td>
					<td class='titulo_tabela' align='center'>Status</td>
					<td class='titulo_tabela' align='center'>Visualizar</td>
					<td class='titulo_tabela' align='center'>Aprovar/Reprovar</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$orc_id = mysql_result($query, $x, 'orc_id');
					$tps_nome = mysql_result($query, $x, 'tps_nome');
					if($tps_nome == ''){$tps_nome = mysql_result($query, $x, 'orc_tipo_servico_cliente');}
					$orc_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($query, $x, 'orc_data_cadastro'),0,10))));
					$orc_hora_cadastro = substr(mysql_result($query, $x, 'orc_data_cadastro'),11,5);
					$orc_observacoes = mysql_result($query, $x, 'orc_observacoes');
					$sto_status = mysql_result($query, $x, 'sto_status');
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
					echo "<tr class='$c1'>
							  <td>$orc_id</td>
							  <td>$tps_nome</td>
							  <td>$orc_observacoes</td>
							  <td align='center'>$orc_data_cadastro<br><span class='detalhe'>$orc_hora_cadastro</span></td>
							  <td align='center'>$sto_status_n</td>
							  <td align='center'>
							  ";
							 // if($sto_status == 1)
							  //{}
							  //elseif($sto_status == 2 || $sto_status == 3 || $sto_status == 4)
							  //{
								  echo "<img class='mouse' src='../imagens/icon-pdf.png' onclick=javascript:window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');>";
							  //}
							  echo "</td>
							  <td align='center'>
							  ";
							  if($sto_status != 1 && $sto_status != 3 && $sto_status != 4)
							  {
								  echo "
								  <a href='#'  onclick=\"
									abreMaskAcao(
										'<form name=\'form_aprovar_orcamento\' id=\'form_aprovar_orcamento\' enctype=\'multipart/form-data\' method=\'post\' action=\'consultar_orcamento.php?pagina=consultar_orcamento&action=aprovar&orc_id=$orc_id$autenticacao\'>'+
										'<table align=center>'+
											'<tr>'+
												'<td>'+
													'<select name=\'sto_fornecedor_aprovado\' id=\'sto_fornecedor_aprovado\'>'+
													'<option value=\'\'>Selecione o fornecedor aprovado</option>'+";
													$sql_fornecedores = "SELECT * FROM orcamento_fornecedor 
																		 LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = orcamento_fornecedor.orf_fornecedor
																		 WHERE orf_orcamento = $orc_id ORDER BY orf_id ASC";
													$query_fornecedores = mysql_query($sql_fornecedores,$conexao);
													$rows_fornecedores = mysql_num_rows($query_fornecedores);
													if($rows_fornecedores > 0)
													{
														while($row_fornecedores = mysql_fetch_array($query_fornecedores))
														{
															echo "'<option value=\'".$row_fornecedores['for_id']."\'>".$row_fornecedores['for_nome_razao']."</option>'+";
														}
													}
													echo "
													'</select>'+
													'<br><br>'+
													'Digite no campo abaixo caso queira fazer alguma obseração sobre o orçamento aprovado.<br>'+
													'<input name=\'sto_observacao\' id=\'sto_observacao\' placeholder=\'Observação\' ><br><br>'+
													'<input id=\'bt_aprovar_orcamento\' value=\' Aprovar \' type=\'button\' onclick=\'enviaAprovacao();\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
													'<input value=\' Cancelar \' type=\'button\' class=\'close_janela\'><br>'+
													'<div id=\'erro\' align=\'center\'>&nbsp;</div>'+
												'</td>'+
											'</tr>'+
										'<table>'+
										'</form>');
									\">
									<img border='0' src='../imagens/icon-aprovar.png'>
								</a>
								&nbsp;&nbsp;
								<a href='#'  onclick=\"
									abreMaskAcao(
										'<form name=\'form_reprovar_orcamento\' id=\'form_reprovar_orcamento\' enctype=\'multipart/form-data\' method=\'post\' action=\'consultar_orcamento.php?pagina=consultar_orcamento&action=reprovar&orc_id=$orc_id$autenticacao\'>'+
										'<table align=center>'+
											'<tr>'+
												'<td>'+
													'<br><br>'+
													'Digite no campo abaixo caso queira fazer alguma obseração sobre o orçamento reprovado.<br>'+
													'<input name=\'sto_observacao\' id=\'sto_observacao\' placeholder=\'Observação\' ><br><br>'+
													'<input id=\'bt_reprovar_orcamento\' value=\' reprovar \' type=\'button\' onclick=\'enviaReprovacao();\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
													'<input value=\' Cancelar \' type=\'button\' class=\'close_janela\'><br>'+
													'<div id=\'erro\' align=\'center\'>&nbsp;</div>'+
												'</td>'+
											'</tr>'+
										'<table>'+
										'</form>');
									\">
									<img border='0' src='../imagens/icon-reprovar.png'>
								</a>";
							  }
							  echo "
							  </td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=consultar_orcamento".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum orçamento cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}


include('../mod_rodape/rodape.php');
?>
