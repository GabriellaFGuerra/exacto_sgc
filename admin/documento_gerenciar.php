<?php
session_start (); 
$pagina_link = 'documento_gerenciar';
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
$page = "Documentos &raquo; <a href='documento_gerenciar.php?pagina=documento_gerenciar".$autenticacao."'>Gerenciar</a>";
if($action == "adicionar")
{
	$doc_cliente = $_POST['doc_cliente_id'];
	$doc_orcamento = $_POST['doc_orcamento'];if($doc_orcamento == ''){$doc_orcamento = "null";}
	$doc_tipo = $_POST['doc_tipo'];
	$doc_data_emissao = implode("-",array_reverse(explode("/",$_POST['doc_data_emissao'])));
	$doc_periodicidade = $_POST['doc_periodicidade'];
	if($doc_periodicidade == 6)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 6 month",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 12)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 1 year",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 24)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 2 year",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 36)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 3 year",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 48)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 4 year",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 60)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 5 year",strtotime($doc_data_emissao)));
	}
	
	$doc_observacoes = $_POST['doc_observacoes'];
	$sql = "INSERT INTO documento_gerenciar (
	doc_cliente,
	doc_orcamento,
	doc_tipo,
	doc_data_emissao,
	doc_periodicidade,
	doc_data_vencimento,
	doc_observacoes
	) 
	VALUES 
	(
	'$doc_cliente',
	$doc_orcamento,
	'$doc_tipo',
	'$doc_data_emissao',
	'$doc_periodicidade',
	'$doc_data_vencimento',
	'$doc_observacoes'
	)";
	if(mysql_query($sql,$conexao))
	{		
		$doc_anexo = $_FILES['doc_anexo']["name"];
		$tmp_anexo = $_FILES['doc_anexo']["tmp_name"];
		$ultimo_id = mysql_insert_id();
		$caminho = "../admin/docs/$ultimo_id/";
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		foreach($doc_anexo as $k => $value)
		{
			if($doc_anexo[$k] != '')
			{
				$extensao = pathinfo($doc_anexo[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho;
				$arquivo .= md5(mt_rand(1,10000).$doc_anexo[$k]).'.'.$extensao;
				move_uploaded_file($tmp_anexo[$k], ($arquivo));
			}
			
			$sql = "UPDATE documento_gerenciar SET 
					doc_anexo = '".$arquivo."'
					WHERE doc_id = $ultimo_id ";
					mysql_query($sql,$conexao);
		}
		
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br>'+
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

if($action == 'editar')
{
	$doc_id = $_GET['doc_id'];
	$doc_cliente = $_POST['doc_cliente_id'];
	$doc_orcamento = $_POST['doc_orcamento'];if($doc_orcamento == ''){$doc_orcamento = "null";}
	$doc_tipo = $_POST['doc_tipo'];
	$doc_data_emissao = implode("-",array_reverse(explode("/",$_POST['doc_data_emissao'])));
	$doc_periodicidade = $_POST['doc_periodicidade'];
	if($doc_periodicidade == 6)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 6 month",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 12)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 1 year",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 24)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 2 year",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 36)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 3 year",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 48)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 4 year",strtotime($doc_data_emissao)));
	}
	elseif($doc_periodicidade == 60)
	{
		$doc_data_vencimento = date("Y-m-d", strtotime("+ 5 year",strtotime($doc_data_emissao)));
	}
	$doc_observacoes = $_POST['doc_observacoes'];
	$sqlEnviaEdit = "UPDATE documento_gerenciar SET 
					 doc_orcamento = $doc_orcamento,
					 doc_tipo = '$doc_tipo',
					 doc_data_emissao = '$doc_data_emissao',
					 doc_periodicidade = '$doc_periodicidade',
					 doc_data_vencimento = '$doc_data_vencimento',
					 doc_observacoes = '$doc_observacoes'
					 WHERE doc_id = $doc_id ";

	if(mysql_query($sqlEnviaEdit,$conexao))
	{
		$ultimo_id = $doc_id;
		$erro=0;
		
		$doc_anexo = $_FILES['doc_anexo']["name"];
		$tmp_anexo = $_FILES['doc_anexo']["tmp_name"];
		$caminho = "../admin/docs/$ultimo_id/";
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		$sql_orcamento = "SELECT * FROM documento_gerenciar
						  WHERE doc_id = $doc_id ";
		$query_orcamento = mysql_query($sql_orcamento,$conexao);
		$query_f = mysql_query($sql_orcamento,$conexao);
		$rows_orcamento = mysql_num_rows($query_orcamento);
		if($rows_orcamento > 0)
		{
			$anexo = mysql_result($query_orcamento, 0, 'doc_anexo');
		}
		foreach($doc_anexo as $k => $value)
		{
			if($doc_anexo[$k] != '')
			{
				$extensao = pathinfo($doc_anexo[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho;
				$arquivo .= md5(mt_rand(1,10000).$doc_anexo[$k]).'.'.$extensao;
				move_uploaded_file($tmp_anexo[$k], ($arquivo));
				unlink($anexo);
				$sql_update = "UPDATE documento_gerenciar SET 
							   doc_anexo = '".$arquivo."' 
							   WHERE doc_id = $doc_id 
							   ";							   
				if(mysql_query($sql_update))
				{
					//echo "update<br>";
				}
				else
				{$erro=1;}
			}
		}
		if($erro != 1)
		{
			echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br>'+
				'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
			</SCRIPT>
				";
		}
		else
		{
			echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
				'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
			</SCRIPT>
			";
		}
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
		";
	}
}

if($action == 'excluir')
{
	$doc_id = $_GET['doc_id'];
	$sql = "DELETE FROM documento_gerenciar WHERE doc_id = '$doc_id'";
				
	if(mysql_query($sql,$conexao))
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
	}
}

$num_por_pagina = 10;
if(!$pag){$primeiro_registro = 0; $pag = 1;}
else{$primeiro_registro = ($pag - 1) * $num_por_pagina;}
$fil_nome = $_REQUEST['fil_nome'];
if($fil_nome == '')
{
	$nome_query = " 1 = 1 ";
}
else
{
	$nome_query = " (cli_nome_razao LIKE '%".$fil_nome."%') ";
}
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
$fil_data_inicio = trim(implode("-",array_reverse(explode("/",$_REQUEST['fil_data_inicio']))));
$fil_data_fim = trim(implode("-",array_reverse(explode("/",$_REQUEST['fil_data_fim']))));
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
	$data_query = " doc_data_vencimento <= '$fil_data_fim 23:59:59' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " doc_data_vencimento BETWEEN '$fil_data_inicio' AND '$fil_data_fim 23:59:59' ";
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
$sql = "SELECT * FROM documento_gerenciar 
		LEFT JOIN  ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
		LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
		LEFT JOIN (orcamento_gerenciar 
			LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
		ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
		WHERE cli_status = 1 and cli_deletado = 1 and ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$nome_query." AND ".$tipo_doc_query." AND ".$data_query." AND ".$vencido_query."
		ORDER BY doc_data_cadastro DESC
		LIMIT $primeiro_registro, $num_por_pagina ";

		
$cnt = "SELECT COUNT(*) FROM documento_gerenciar
		LEFT JOIN  ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
		LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
		LEFT JOIN (orcamento_gerenciar 
			LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
		ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
		WHERE cli_status = 1 and cli_deletado = 1 and ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$nome_query." AND ".$tipo_doc_query." AND ".$data_query." AND ".$vencido_query."";
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "documento_gerenciar")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Documento' type='button' onclick=javascript:window.location.href='documento_gerenciar.php?pagina=adicionar_documento_gerenciar".$autenticacao."'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='documento_gerenciar.php?pagina=documento_gerenciar".$autenticacao."'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
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
			</select>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Venc. Início' style='width:150px;' value='".implode("/",array_reverse(explode("-",$_REQUEST['fil_data_inicio'])))."' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Venc. Fim'  style='width:150px;' value='".implode("/",array_reverse(explode("-",$_REQUEST['fil_data_fim'])))."' onkeypress='return mascaraData(this,event);'>
			<select name='fil_vencido' id='fil_vencido' style='width:150px;'>
				<option value='$fil_vencido'>$fil_vencido</option>
				<option value='Sim'>Sim</option>
				<option value='Não'>Não</option>
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
					<td class='titulo_tabela'>Tipo de Doc</td>
					<td class='titulo_tabela'>Cliente</td>
					<td class='titulo_tabela'>Orçamento</td>
					<td class='titulo_tabela' align='center'>Data Emissão</td>
					<td class='titulo_tabela' align='center'>Periodicidade</td>
					<td class='titulo_tabela' align='center'>Data Vencimento</td>
					<td class='titulo_tabela' align='center'>Data Cadastro</td>
					<td class='titulo_tabela' align='center'>Anexo</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
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
							$('#normal-button-$doc_id').toolbar({content: '#user-options-$doc_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$doc_id' class='toolbar-icons' style='display: none;'>
						<a href='documento_gerenciar.php?pagina=editar_documento_gerenciar&doc_id=$doc_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o documento <b>$doc_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'documento_gerenciar.php?pagina=documento_gerenciar&action=excluir&doc_id=$doc_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$tpd_nome</td>
							  <td>$cli_nome_razao</td>
							  <td>$orc_id ($tps_nome)</td>
							  <td align=center>$doc_data_emissao</td>
							  <td align=center>$doc_periodicidade_n</td>
							  <td align=center>$doc_data_vencimento</td>
							  <td align='center'>$doc_data_cadastro<br><span class='detalhe'>$doc_hora_cadastro</span></td>
							  <td align='center'>";if($doc_anexo != ''){echo "<a href='".$doc_anexo."' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";} echo "</td>
							  <td align=center>
							  ";
							  if($doc_status == 2 && $_SESSION['setor'] != 1 && $_SESSION['setor'] != 2)
							  {
								  
							  }
							  else
							  {
							  	echo "<div id='normal-button-$doc_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div>";
							  }
							  
							  echo "</td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=documento_gerenciar&fil_nome=$fil_nome&fil_doc_tipo=$fil_doc_tipo".$autenticacao."";
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
if($pagina == 'adicionar_documento_gerenciar')
{
	echo "	
	<form name='form_documento_gerenciar' id='form_documento_gerenciar' enctype='multipart/form-data' method='post' action='documento_gerenciar.php?pagina=documento_gerenciar&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='950'>
			<tr>
				<td align='left'>
					<div class='formtitulo'>Selecione o cliente</div>
					<div class='suggestion'>
						<input name='doc_cliente_id' id='doc_cliente_id'  type='hidden' value='' />
						<input name='doc_cliente' id='doc_cliente' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' />
						<div class='suggestionsBox' id='suggestions' style='display: none;'>
							<div class='suggestionList' id='autoSuggestionsList'>
								&nbsp;
							</div>
						</div>
					</div>
					<p>
					<br><br>
					<select name='doc_orcamento' id='doc_orcamento'>
						<option value=''>Selecione o orçamento caso tenha relação</option>
					</select>
					<p>
					<select name='doc_tipo' id='doc_tipo'>
						<option value=''>Selecione o tipo de documento</option>
						"; 
						$sql = " SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC";
						$query = mysql_query($sql,$conexao);
						while($row = mysql_fetch_array($query) )
						{
							echo "<option value='".$row['tpd_id']."'>".$row['tpd_nome']."</option>";
						}
						echo "
					</select>
					<p>
					<input name='doc_anexo[]' id='doc_anexo' type='file' onchange='verificaExtensao(this);'> &nbsp;
					<p>
					<input type='text' name='doc_data_emissao' id='doc_data_emissao' placeholder='Data Emissão' onkeypress='return mascaraData(this,event);'>
					<select name='doc_periodicidade' id='doc_periodicidade'>
						<option value=''>Periodicidade</option>
						<option value='6'>Semestral</option>
						<option value='12'>Anual</option>
						<option value='24'>Bienal</option>
						<option value='36'>Trienal</option>
						<option value='48'>Quadrienal</option>
						<option value='60'>Quinquenal</option>
					</select>
					<p>
					<textarea name='doc_observacoes' id='doc_observacoes' placeholder='Observações'></textarea>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_documento_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='documento_gerenciar.php?pagina=documento_gerenciar".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_documento_gerenciar')
{
	$doc_id = $_GET['doc_id'];
	$sqledit = "SELECT * FROM documento_gerenciar 
				LEFT JOIN ( cadastro_clientes 
					INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
				ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
				LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
				LEFT JOIN (orcamento_gerenciar 
					LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
				ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
				WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND doc_id = '$doc_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$doc_cliente = mysql_result($queryedit, 0, 'doc_cliente');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$doc_orcamento = mysql_result($queryedit, 0, 'doc_orcamento');
		$orc_id = mysql_result($queryedit, 0, 'orc_id');
		$tps_nome = mysql_result($queryedit, 0, 'tps_nome');
		$doc_tipo = mysql_result($queryedit, 0, 'doc_tipo');
		$tpd_nome = mysql_result($queryedit, 0, 'tpd_nome');
		$doc_data_emissao = implode("/",array_reverse(explode("-",mysql_result($queryedit, 0, 'doc_data_emissao'))));
		$doc_periodicidade = mysql_result($queryedit, 0, 'doc_periodicidade');
		$doc_observacoes = mysql_result($queryedit, 0, 'doc_observacoes');
		switch($doc_periodicidade)
		{
			case 6: $doc_periodicidade_n = "Semestral";break;
			case 12: $doc_periodicidade_n = "Anual";break;
			case 24: $doc_periodicidade_n = "Bienal";break;
			case 36: $doc_periodicidade_n = "Trienal";break;
			case 48: $doc_periodicidade_n = "Quadrienal";break;
			case 60: $doc_periodicidade_n = "Quinquenal";break;
		}
		echo "
		<form name='form_documento_gerenciar' id='form_documento_gerenciar' enctype='multipart/form-data' method='post' action='documento_gerenciar.php?pagina=documento_gerenciar&action=editar&doc_id=$doc_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $doc_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='doc_id' id='doc_id' value='$doc_id' placeholder='ID'>
						<div class='formtitulo'>Selecione o cliente que deseja abrir o documento</div>
						<div class='suggestion'>
							<input name='doc_cliente_id' id='doc_cliente_id'  type='hidden' value='$doc_cliente' />
							<input name='doc_cliente_block' id='doc_cliente_block' value='$cli_nome_razao ($cli_cnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>
									&nbsp;
								</div>
							</div>
						</div>
						<p>
						<br><br>
						";
						if($doc_orcamento != '')
						{
						echo "<select name='doc_orcamento' id='doc_orcamento'>
								 <option value='$doc_orcamento'>$orc_id ($tps_nome)</option>
							  </select>
							  <p>";
						}
						echo "
						<select name='doc_tipo' id='doc_tipo'>
							<option value='$doc_tipo'>$tpd_nome</option>
							"; 
							$sql = " SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC";
							$query = mysql_query($sql,$conexao);
							while($row = mysql_fetch_array($query) )
							{
								echo "<option value='".$row['tpd_id']."'>".$row['tpd_nome']."</option>";
							}
							echo "
						</select>
						<p>
						<input name='doc_anexo[]' id='doc_anexo' type='file' onchange='verificaExtensao(this);'> &nbsp;
						";
						if($doc_anexo != '')
						{
							echo "Anexo atual: <a href='".$doc_anexo."' target='_blank'><img src='../imagens/pdf.png' valign='middle'></a>";
						}
						echo "
						<p>
						<input type='text' name='doc_data_emissao' id='doc_data_emissao' value='$doc_data_emissao' placeholder='Data Emissão' onkeypress='return mascaraData(this,event);'>
						<select name='doc_periodicidade' id='doc_periodicidade'>
							<option value='$doc_periodicidade'>$doc_periodicidade_n</option>
							<option value='6'>Semestral</option>
							<option value='12'>Anual</option>
							<option value='24'>Bienal</option>
							<option value='36'>Trienal</option>
							<option value='48'>Quadrienal</option>
							<option value='60'>Quinquenal</option>
						</select>
						<p>
						<textarea name='doc_observacoes' id='doc_observacoes' placeholder='Observações'>$doc_observacoes</textarea>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_documento_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='documento_gerenciar.php?pagina=documento_gerenciar$autenticacao'; value='Cancelar'/></center>
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
</body>
</html>