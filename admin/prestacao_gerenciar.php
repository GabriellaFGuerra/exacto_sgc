<?php
session_start (); 
$pagina_link = 'prestacao_gerenciar';
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
$page = "Prestação de Contas &raquo; <a href='prestacao_gerenciar.php?pagina=prestacao_gerenciar".$autenticacao."'>Gerenciar</a>";
if($action == "adicionar")
{
	$pre_cliente = $_POST['pre_cliente_id'];
	$pre_referencia = $_POST['pre_ref_mes']."/".$_POST['pre_ref_ano'];
	$pre_data_envio = implode("-",array_reverse(explode("/",$_POST['pre_data_envio'])));
	$pre_enviado_por = $_POST['pre_enviado_por'];
	$pre_observacoes = $_POST['pre_observacoes'];
	$sql = "INSERT INTO prestacao_gerenciar (
	pre_cliente,
	pre_referencia,
	pre_data_envio,
	pre_enviado_por,
	pre_observacoes
	) 
	VALUES 
	(
	'$pre_cliente',
	'$pre_referencia',
	'$pre_data_envio',
	'$pre_enviado_por',
	'$pre_observacoes'
	)";
	
	if(mysql_query($sql,$conexao))
	{		
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
	$pre_id = $_GET['pre_id'];
	$pre_referencia = $_POST['pre_ref_mes']."/".$_POST['pre_ref_ano'];
	$pre_data_envio = implode("-",array_reverse(explode("/",$_POST['pre_data_envio'])));
	$pre_enviado_por = $_POST['pre_enviado_por'];
	$pre_observacoes = $_POST['pre_observacoes'];

	$sqlEnviaEdit = "UPDATE prestacao_gerenciar SET 
					 pre_referencia = '$pre_referencia',
					 pre_data_envio = '$pre_data_envio',
					 pre_enviado_por = '$pre_enviado_por',
					 pre_observacoes = '$pre_observacoes'
					 WHERE pre_id = $pre_id ";
	
	if(mysql_query($sqlEnviaEdit,$conexao))
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

if($action == 'excluir')
{
	$pre_id = $_GET['pre_id'];
	$sql = "DELETE FROM prestacao_gerenciar WHERE pre_id = '$pre_id'";
				
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
if($action == 'ativar')
{
	$pre_id = $_GET['pre_id'];
	$sql = "UPDATE prestacao_gerenciar SET pre_status = 1 WHERE pre_id = '$pre_id'";
				
	if(mysql_query($sql,$conexao))
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Ativação realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
	}
}
if($action == 'desativar')
{
	$pre_id = $_GET['pre_id'];
	$sql = "UPDATE prestacao_gerenciar SET pre_status = 0 WHERE pre_id = '$pre_id'";
				
	if(mysql_query($sql,$conexao))
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Desativação realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
			";
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
	}
}

if($action == 'comprovante')
{
	$erro = 0;
	$pre_id = $_GET['pre_id'];
	$pre_comprovante = $_FILES['pre_comprovante']["name"];
	$tmp_comprovante = $_FILES['pre_comprovante']["tmp_name"];
	$caminho = "../admin/prestacao_comprovante/$pre_id/";
	if(!file_exists($caminho))
	{
		 mkdir($caminho, 0755, true); 
	}
	foreach($pre_comprovante as $k => $value)
	{
		if($pre_comprovante[$k] != '')
		{
			$extensao = pathinfo($pre_comprovante[$k], PATHINFO_EXTENSION);
			$arquivo = $caminho;
			$arquivo .= md5(mt_rand(1,10000).$pre_comprovante[$k]).'.'.$extensao;
			move_uploaded_file($tmp_comprovante[$k], ($arquivo));
		}
		
		$sql = "UPDATE prestacao_gerenciar SET 
				pre_comprovante = '".$arquivo."'
				WHERE pre_id = $pre_id ";
		if(mysql_query($sql,$conexao)){ }else { $erro = 1; }
	}
	if($erro == 0)
	{            
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Anexo enviado com sucesso.<br><br>'+
			'<input value=\' Ok \' type=\'button\'class=\'close_janela\'>' );
		</SCRIPT>";           
	}
	else
	{
		echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao enviar anexo.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
		";
	}
}

$num_por_pagina = 10;
if(!$pag){$primeiro_registro = 0; $pag = 1;}
else{$primeiro_registro = ($pag - 1) * $num_por_pagina;}
$fil_prestacao = $_REQUEST['fil_prestacao'];
if($fil_prestacao == '')
{
	$prestacao_query = " 1 = 1 ";
}
else
{
	$prestacao_query = " (pre_id = '".$fil_prestacao."') ";
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
	$data_query = " pre_data_cadastro <= '$fil_data_fim 23:59:59' ";
}
elseif($fil_data_inicio != '' && $fil_data_fim != '')
{
	$data_query = " pre_data_cadastro BETWEEN '$fil_data_inicio' AND '$fil_data_fim 23:59:59' ";
}
$sql = "SELECT * FROM prestacao_gerenciar 
		LEFT JOIN ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
		WHERE cli_status = 1 and cli_deletado = 1 and ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$prestacao_query." AND ".$nome_query." AND ".$referencia_query." AND ".$data_query."  
		ORDER BY pre_data_cadastro DESC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM prestacao_gerenciar
		LEFT JOIN ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
		WHERE cli_status = 1 and cli_deletado = 1 and ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$prestacao_query." AND ".$nome_query." AND ".$referencia_query." AND ".$data_query." 
		";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "prestacao_gerenciar")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Nova Prestação de Conta' type='button' onclick=javascript:window.location.href='prestacao_gerenciar.php?pagina=adicionar_prestacao_gerenciar".$autenticacao."'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='prestacao_gerenciar.php?pagina=prestacao_gerenciar".$autenticacao."'>
			<input name='fil_prestacao' id='fil_prestacao' value='$fil_prestacao' placeholder='N° '>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input name='fil_referencia' id='fil_referencia' value='$fil_referencia' placeholder='Referência '>
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
					<td class='titulo_tabela'>N° Prestação de Conta</td>
					<td class='titulo_tabela'>Cliente</td>
					<td class='titulo_tabela'>Referência</td>
					<td class='titulo_tabela'>Data Envio</td>
					<td class='titulo_tabela'>Por</td>
					<td class='titulo_tabela'>Observação</td>
					<td class='titulo_tabela' align='center'>Data Cadastro</td>
					<td class='titulo_tabela' align='center'>Gerar Protocolo</td>
					<td class='titulo_tabela' align='center'>Protocolo Assinado</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
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
							$('#normal-button-$pre_id').toolbar({content: '#user-options-$pre_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$pre_id' class='toolbar-icons' style='display: none;'>
						<a href='#' title='Anexar comprovante'  onclick=\"
							abreMaskAcao(
								'<form name=\'form_envia_comprovante\' id=\'form_envia_comprovante\' enctype=\'multipart/form-data\' method=\'post\' action=\'prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=comprovante&pre_id=$pre_id$autenticacao\'>'+
								'<table align=center>'+
									'<tr>'+
										'<td>'+
											'<input type=\'file\' name=\'pre_comprovante[]\' id=\'pre_comprovante\'><br><br>'+
											'<input id=\'bt_envia_comprovante\' value=\' Salvar \' type=\'submit\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
											'<input value=\' Cancelar \' type=\'button\' class=\'close_janela\'>'+
										'</td>'+
									'</tr>'+
								'<table>'+
								'</form>');
							\">
							<img border='0' src='../imagens/icon-comprovante.png'>
						</a>
						<a title='Editar' href='prestacao_gerenciar.php?pagina=editar_prestacao_gerenciar&pre_id=$pre_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a  title='Excluir' onclick=\"
							abreMask(
								'Deseja realmente excluir a prestação <b>$pre_id</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=excluir&pre_id=$pre_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
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
							  <td align='center'><a href='prestacao_imprimir.php?pre_id=$pre_id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
							  <td align='center'>";if($pre_comprovante != ''){echo "<a href='".$pre_comprovante."' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";} echo "</td>
							  <td align=center><div id='normal-button-$pre_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=prestacao_gerenciar&fil_prestacao=$fil_prestacao&fil_nome=$fil_nome&fil_referencia=$fil_referencia&fil_data_inicio=$fil_data_inicio&fil_data_fim=$fil_data_fim".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhuma prestação de conta cadastrada.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_prestacao_gerenciar')
{
	echo "	
	<form name='form_prestacao_gerenciar' id='form_prestacao_gerenciar' enctype='multipart/form-data' method='post' action='prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='950'>
			<tr>
				<td align='left'>
					<div class='formtitulo'>Selecione o cliente</div>
					<div class='suggestion'>
						<input name='pre_cliente_id' id='pre_cliente_id'  type='hidden' value='' />
						<input name='pre_cliente' id='pre_cliente' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' />
						<div class='suggestionsBox' id='suggestions' style='display: none;'>
							<div class='suggestionList' id='autoSuggestionsList'>
								&nbsp;
							</div>
						</div>
					</div>
					<p>
					<br><br>
					Referência:<br>
					<select name='pre_ref_mes' id='pre_ref_mes'>
						<option value=''>Mês</option>
						<option value='01'>Janeiro</option>
						<option value='02'>Fevereiro</option>
						<option value='03'>Março</option>
						<option value='04'>Abril</option>
						<option value='05'>Maio</option>
						<option value='06'>Junho</option>
						<option value='07'>Julho</option>
						<option value='08'>Agosto</option>
						<option value='09'>Setembro</option>
						<option value='10'>Outubro</option>
						<option value='11'>Novembro</option>
						<option value='12'>Dezembro</option>
					</select>
					/<input type='text' id='pre_ref_ano' name='pre_ref_ano' value='' placeholder='Ano' />
					<p>
					<input type='text' id='pre_data_envio' name='pre_data_envio' value='' placeholder='Data Envio' onkeypress='return mascaraData(this,event);' />
					<p>
					<input type='text' id='pre_enviado_por' name='pre_enviado_por' value='' placeholder='Enviado por:' />
					<p>
					<textarea name='pre_observacoes' id='pre_observacoes' placeholder='Observações'></textarea>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_prestacao_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='prestacao_gerenciar.php?pagina=prestacao_gerenciar".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_prestacao_gerenciar')
{
	$pre_id = $_GET['pre_id'];
	$sqledit = "SELECT * FROM prestacao_gerenciar 
				LEFT JOIN ( cadastro_clientes 
					INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
				ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
				WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND pre_id = '$pre_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$pre_cliente = mysql_result($queryedit, 0, 'pre_cliente');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$pre_referencia = mysql_result($queryedit, 0, 'pre_referencia');
		$ref = explode("/",$pre_referencia);
		$pre_ref_mes = $ref[0];
		switch($pre_ref_mes)
		{
			case 1: $pre_ref_mes_n = "Janeiro"; break;
        	case 2: $pre_ref_mes_n = "Fevereiro"; break;
        	case 3: $pre_ref_mes_n = "Março"; break;
        	case 4: $pre_ref_mes_n = "Abril"; break;
        	case 5: $pre_ref_mes_n = "Maio"; break;
        	case 6: $pre_ref_mes_n = "Junho"; break;
        	case 7: $pre_ref_mes_n = "Julho"; break;
        	case 8: $pre_ref_mes_n = "Agosto"; break;
        	case 9: $pre_ref_mes_n = "Setembro"; break;
        	case 10: $pre_ref_mes_n = "Outubro"; break;
        	case 11: $pre_ref_mes_n = "Novembro"; break;
        	case 12: $pre_ref_mes_n = "Dezembro"; break;
		}
		$pre_ref_ano = $ref[1];
		$pre_data_envio = implode("/",array_reverse(explode("-",mysql_result($queryedit, 0, 'pre_data_envio'))));
		$pre_enviado_por = mysql_result($queryedit, 0, 'pre_enviado_por');
		$pre_observacoes = mysql_result($queryedit, 0, 'pre_observacoes');
		echo "
		<form name='form_prestacao_gerenciar' id='form_prestacao_gerenciar' enctype='multipart/form-data' method='post' action='prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=editar&pre_id=$pre_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $pre_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<div class='formtitulo'>Selecione o cliente</div>
						<div class='suggestion'>
							<input name='pre_cliente_id' id='pre_cliente_id'  type='hidden' value='$pre_cliente' />
							<input name='pre_cliente_block' id='pre_cliente_block' value='$cli_nome_razao ($cli_cnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>
									&nbsp;
								</div>
							</div>
						</div>
						<p>
						<br><br>
						Referência:<br>
						<select name='pre_ref_mes' id='pre_ref_mes'>
							<option value='$pre_ref_mes'>$pre_ref_mes_n</option>
							<option value='01'>Janeiro</option>
							<option value='02'>Fevereiro</option>
							<option value='03'>Março</option>
							<option value='04'>Abril</option>
							<option value='05'>Maio</option>
							<option value='06'>Junho</option>
							<option value='07'>Julho</option>
							<option value='08'>Agosto</option>
							<option value='09'>Setembro</option>
							<option value='10'>Outubro</option>
							<option value='11'>Novembro</option>
							<option value='12'>Dezembro</option>
						</select>
						/<input type='text' id='pre_ref_ano' name='pre_ref_ano' value='$pre_ref_ano' placeholder='Ano' />
						<p>
						<input type='text' id='pre_data_envio' name='pre_data_envio' value='$pre_data_envio' placeholder='Data Envio' onkeypress='return mascaraData(this,event);' />
						<p>
						<input type='text' id='pre_enviado_por' name='pre_enviado_por' value='$pre_enviado_por' placeholder='Enviado por:' />
						<p>
						<textarea name='pre_observacoes' id='pre_observacoes' placeholder='Observações'>$pre_observacoes</textarea>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_prestacao_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='prestacao_gerenciar.php?pagina=prestacao_gerenciar$autenticacao'; value='Cancelar'/></center>
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