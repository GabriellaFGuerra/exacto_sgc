<?php
session_start (); 
$pagina_link = 'infracoes_gerenciar';
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
<script src="../mod_includes/js/tinymce/tinymce.min.js"></script>
<script src="../mod_includes/js/placeholder/plugin.js"></script>
<script>
tinymce.init({ 
	selector:'textarea',
  	plugins: " placeholder image jbimages imagetools advlist link table textcolor media paste",
	toolbar: "undo redo fontsizeselect format bold italic forecolor backcolor alignleft aligncenter alignright alignjustify bullist numlist outdent indent table link media image jbimages",
   imagetools_toolbar: "rotateleft rotateright | flipv fliph | editimage imageoptions",
   paste_data_images: true,
   media_live_embeds: true,
     relative_urls : false,
	 elements : 'nourlconvert',
convert_urls : false,
paste_auto_cleanup_on_paste : true,
paste_remove_styles: true,
paste_remove_styles_if_webkit: true,
paste_as_text: true
});</script>

</head>
<body>
<?php	
include		('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogin.php');
include		("../mod_topo/topo.php");
require_once('../mod_includes/php/verificapermissao.php');

?>

<?php
$page = "Infrações &raquo; <a href='infracoes_gerenciar.php?pagina=infracoes_gerenciar".$autenticacao."'>Gerenciar</a>";
if($action == "adicionar")
{
	$inf_cliente = $_POST['inf_cliente_id'];
	$inf_tipo = $_POST['inf_tipo'];
	$inf_ano = date("Y");
	$inf_cidade = $_POST['inf_cidade'];
	$inf_data = implode("-",array_reverse(explode("/",$_POST['inf_data'])));
	$inf_proprietario = $_POST['inf_proprietario'];
	$inf_apto = $_POST['inf_apto'];
	$inf_bloco = $_POST['inf_bloco'];
	$inf_endereco = $_POST['inf_endereco'];
	$inf_email = $_POST['inf_email'];
	$inf_desc_irregularidade = $_POST['inf_desc_irregularidade'];
	$inf_assunto = $_POST['inf_assunto'];
	$inf_desc_artigo = $_POST['inf_desc_artigo'];
	$inf_desc_notificacao = $_POST['inf_desc_notificacao'];
	$sql = "INSERT INTO infracoes_gerenciar (
	inf_cliente,
	inf_tipo,
	inf_ano,
	inf_cidade,
	inf_data,
	inf_proprietario,
	inf_apto,
	inf_bloco,
	inf_endereco,
	inf_email,
	inf_desc_irregularidade,
	inf_assunto,
	inf_desc_artigo,
	inf_desc_notificacao
	) 
	VALUES 
	(
	'$inf_cliente',
	'$inf_tipo',
	'$inf_ano',
	'$inf_cidade',
	'$inf_data',
	'$inf_proprietario',
	'$inf_apto',
	'$inf_bloco',
	'$inf_endereco',
	'$inf_email',
	'$inf_desc_irregularidade',
	'$inf_assunto',
	'$inf_desc_artigo',
	'$inf_desc_notificacao'
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
	$inf_id = $_GET['inf_id'];
	$inf_tipo = $_POST['inf_tipo'];
	$inf_ano = date("Y");
	$inf_cidade = $_POST['inf_cidade'];
	$inf_data = implode("-",array_reverse(explode("/",$_POST['inf_data'])));
	$inf_proprietario = $_POST['inf_proprietario'];
	$inf_apto = $_POST['inf_apto'];
	$inf_bloco = $_POST['inf_bloco'];
	$inf_endereco = $_POST['inf_endereco'];
	$inf_email = $_POST['inf_email'];
	$inf_desc_irregularidade = $_POST['inf_desc_irregularidade'];
	$inf_assunto = $_POST['inf_assunto'];
	$inf_desc_artigo = $_POST['inf_desc_artigo'];
	$inf_desc_notificacao = $_POST['inf_desc_notificacao'];
	$sqlEnviaEdit = "UPDATE infracoes_gerenciar SET 
					 inf_tipo = '$inf_tipo',
					 inf_cidade = '$inf_cidade',
					 inf_data = '$inf_data',
					 inf_proprietario = '$inf_proprietario',
					 inf_apto = '$inf_apto',
					 inf_bloco = '$inf_bloco',
					 inf_endereco = '$inf_endereco',
					 inf_email = '$inf_email',
					 inf_desc_irregularidade = '$inf_desc_irregularidade',
					 inf_assunto = '$inf_assunto',
					 inf_desc_artigo = '$inf_desc_artigo',
					 inf_desc_notificacao = '$inf_desc_notificacao'
					 WHERE inf_id = $inf_id ";

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

if($action == "duplicar")
{
	$inf_cliente = $_POST['inf_cliente_id'];
	$inf_tipo = $_POST['inf_tipo'];
	$inf_ano = date("Y");
	$inf_cidade = $_POST['inf_cidade'];
	$inf_data = implode("-",array_reverse(explode("/",$_POST['inf_data'])));
	$inf_proprietario = $_POST['inf_proprietario'];
	$inf_apto = $_POST['inf_apto'];
	$inf_bloco = $_POST['inf_bloco'];
	$inf_endereco = $_POST['inf_endereco'];
	$inf_email = $_POST['inf_email'];
	$inf_desc_irregularidade = $_POST['inf_desc_irregularidade'];
	$inf_assunto = $_POST['inf_assunto'];
	$inf_desc_artigo = $_POST['inf_desc_artigo'];
	$inf_desc_notificacao = $_POST['inf_desc_notificacao'];
	$sql = "INSERT INTO infracoes_gerenciar (
	inf_cliente,
	inf_tipo,
	inf_ano,
	inf_cidade,
	inf_data,
	inf_proprietario,
	inf_apto,
	inf_bloco,
	inf_endereco,
	inf_email,
	inf_desc_irregularidade,
	inf_assunto,
	inf_desc_artigo,
	inf_desc_notificacao
	) 
	VALUES 
	(
	'$inf_cliente',
	'$inf_tipo',
	'$inf_ano',
	'$inf_cidade',
	'$inf_data',
	'$inf_proprietario',
	'$inf_apto',
	'$inf_bloco',
	'$inf_endereco',
	'$inf_email',
	'$inf_desc_irregularidade',
	'$inf_assunto',
	'$inf_desc_artigo',
	'$inf_desc_notificacao'
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
if($action == "adicionar_recurso")
{
	$rec_infracao = $_POST['inf_id'];
	$rec_assunto = $_POST['rec_assunto'];
	$rec_descricao = $_POST['rec_descricao'];
	$rec_status = $_POST['rec_status'];
	
	$sql = "INSERT INTO recurso_gerenciar (
	rec_infracao,
	rec_assunto,
	rec_descricao,
	rec_status
	) 
	VALUES 
	(
	'$rec_infracao',
	'$rec_assunto',
	'$rec_descricao',
	'$rec_status'
	)";
	if(mysql_query($sql,$conexao))
	{		
		$rec_recurso = $_FILES['rec_recurso']["name"];
		$tmp_anexo = $_FILES['rec_recurso']["tmp_name"];
		$ultimo_id = mysql_insert_id();
		$caminho = "../admin/recurso/$ultimo_id/";
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		foreach($rec_recurso as $k => $value)
		{
			if($rec_recurso[$k] != '')
			{
				$extensao = pathinfo($rec_recurso[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho;
				$arquivo .= md5(mt_rand(1,10000).$rec_recurso[$k]).'.'.$extensao;
				move_uploaded_file($tmp_anexo[$k], ($arquivo));
			}
			
			$sql = "UPDATE recurso_gerenciar SET 
					rec_recurso = '".$arquivo."'
					WHERE rec_id = $ultimo_id ";
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

if($action == 'excluir')
{
	$inf_id = $_GET['inf_id'];
	$sql = "DELETE FROM infracoes_gerenciar WHERE inf_id = '$inf_id'";
				
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
if($action == 'excluir_recurso')
{
	$rec_id = $_GET['rec_id'];
	$sql = "DELETE FROM recurso_gerenciar WHERE rec_id = '$rec_id'";
				
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
if($action == 'comprovante')
{
	$erro = 0;
	$inf_id = $_GET['inf_id'];
	$inf_comprovante = $_FILES['inf_comprovante']["name"];
	
	$tmp_comprovante = $_FILES['inf_comprovante']["tmp_name"];
	$caminho = "../admin/infracoes_comprovante/$inf_id/";
	if(!file_exists($caminho))
	{
		 mkdir($caminho, 0755, true); 
	}
	foreach($inf_comprovante as $k => $value)
	{
		if($inf_comprovante[$k] != '')
		{
			$extensao = pathinfo($inf_comprovante[$k], PATHINFO_EXTENSION);
			$arquivo = $caminho;
			$arquivo .= md5(mt_rand(1,10000).$inf_comprovante[$k]).'.'.$extensao;
			move_uploaded_file($tmp_comprovante[$k], ($arquivo));
		}
		
		$sql = "UPDATE infracoes_gerenciar SET 
				inf_comprovante = '".$arquivo."'
				WHERE inf_id = $inf_id ";
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
$fil_nome = $_REQUEST['fil_nome'];
if($fil_nome == '')
{
	$nome_query = " 1 = 1 ";
}
else
{
	$nome_query = " (cli_nome_razao LIKE '%".$fil_nome."%') ";
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
		LEFT JOIN ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
		LEFT JOIN recurso_gerenciar ON recurso_gerenciar.rec_infracao = infracoes_gerenciar.inf_id
		WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$nome_query." AND ".$proprietario_query." AND ".$tipo_inf_query." AND ".$assunto_query." AND ".$bloco_query." AND ".$apto_query." 
		ORDER BY inf_data DESC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM infracoes_gerenciar
		LEFT JOIN ( cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
		ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
		LEFT JOIN recurso_gerenciar ON recurso_gerenciar.rec_infracao = infracoes_gerenciar.inf_id
		WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$nome_query." AND ".$proprietario_query." AND ".$tipo_inf_query." AND ".$assunto_query." AND ".$bloco_query." AND ".$apto_query." ";
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "infracoes_gerenciar")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Nova Infração' type='button' onclick=javascript:window.location.href='infracoes_gerenciar.php?pagina=adicionar_infracoes_gerenciar".$autenticacao."'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar".$autenticacao."'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input name='fil_bloco' id='fil_bloco' value='$fil_bloco' placeholder='Bloco/Quadra'>
			<input name='fil_apto' id='fil_apto' value='$fil_apto' placeholder='Apto.'>
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
					<td class='titulo_tabela'>Cliente</td>
					<td class='titulo_tabela'>Tipo</td>
					<td class='titulo_tabela'>Assunto</td>
					<td class='titulo_tabela'>Proprietário</td>
					<td class='titulo_tabela'>Bloco/Quadra/Ap</td>
					<td class='titulo_tabela'>Data</td>
					<td class='titulo_tabela' align='center'>Gerar advertência/multa</td>
					<td class='titulo_tabela' align='center'>Gerar protocolo</td>
					<td class='titulo_tabela' align='center'>Comprovante</td>
					<td class='titulo_tabela' align='center'>Recurso</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$rec_id = mysql_result($query, $x, 'rec_id');
					$inf_id = mysql_result($query, $x, 'inf_id');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$inf_ano = mysql_result($query, $x, 'inf_ano');
					$inf_tipo = mysql_result($query, $x, 'inf_tipo');
					$inf_assunto = mysql_result($query, $x, 'inf_assunto');
					$inf_bloco = mysql_result($query, $x, 'inf_bloco');
					$inf_apto = mysql_result($query, $x, 'inf_apto');
					$inf_proprietario = mysql_result($query, $x, 'inf_proprietario');
					$inf_comprovante = mysql_result($query, $x, 'inf_comprovante');
					$inf_data = implode("/",array_reverse(explode("-",mysql_result($query, $x,'inf_data'))));
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
							$('#normal-button-$inf_id').toolbar({content: '#user-options-$inf_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$inf_id' class='toolbar-icons' style='display: none;'>
						<a href='infracoes_gerenciar.php?pagina=duplicar_infracoes_gerenciar&inf_id=$inf_id$autenticacao'><img border='0' src='../imagens/icon-duplicar.png' title='Duplicar' ></a>
						<a href='#' title='Anexar comprovante'  onclick=\"
							abreMaskAcao(
								'<form name=\'form_envia_comprovante\' id=\'form_envia_comprovante\' enctype=\'multipart/form-data\' method=\'post\' action=\'infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=comprovante&inf_id=$inf_id$autenticacao\'>'+
								'<table align=center>'+
									'<tr>'+
										'<td>'+
											'<input type=\'file\' name=\'inf_comprovante[]\' id=\'inf_comprovante\'><br><br>'+
											'<input id=\'bt_envia_comprovante\' value=\' Salvar \' type=\'submit\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
											'<input value=\' Cancelar \' type=\'button\' class=\'close_janela\'>'+
										'</td>'+
									'</tr>'+
								'<table>'+
								'</form>');
							\">
							<img border='0' src='../imagens/icon-comprovante.png'>
						</a>
						<a href='infracoes_gerenciar.php?pagina=editar_infracoes_gerenciar&inf_id=$inf_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir esta infração?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=excluir&inf_id=$inf_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>".str_pad($inf_id,3,"0",STR_PAD_LEFT)."/".$inf_ano."</td>
							  <td>$cli_nome_razao</td>
							  <td>$inf_tipo</td>
							  <td>$inf_assunto</td>
							  <td>$inf_proprietario</td>
							  <td>$inf_bloco/$inf_apto</td>
							  <td>$inf_data</td>
							  <td align='center'><a href='infracoes_imprimir.php?inf_id=$inf_id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
							  <td align='center'><a href='infracoes_protocolo_imprimir.php?inf_id=$inf_id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
							  <td align='center'>";if($inf_comprovante != ''){echo "<a href='".$inf_comprovante."' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";} echo "</td>
							  <td align='center'>
							  	";
							  	if($rec_id != '')
							  	{
									echo "<a href='recurso_gerenciar.php?pagina=recurso_gerenciar&rec_id=$rec_id$autenticacao'><img src='../imagens/icon-exibir.png'></a>";
								}
								else
								{
									echo "<a href='infracoes_gerenciar.php?pagina=recurso_gerenciar&inf_id=$inf_id$autenticacao'>Gerar Recurso</a>";
									
								}
								echo "</td>
							  <td align=center>
							  <div id='normal-button-$inf_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div>
							  </td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=infracoes_gerenciar&fil_nome=$fil_nome&fil_inf_tipo=$fil_inf_tipo".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhuma infração cadastrada.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_infracoes_gerenciar')
{
	echo "	
	<form name='form_infracoes_gerenciar' id='form_infracoes_gerenciar' enctype='multipart/form-data' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='950'>
			<tr>
				<td align='left'>
					<div class='suggestion'>
						<input name='inf_cliente_id' id='inf_cliente_id'  type='hidden' value='' />
						<input name='inf_cliente' id='inf_cliente' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' />
						<div class='suggestionsBox' id='suggestions' style='display: none;'>
							<div class='suggestionList' id='autoSuggestionsList'>
								&nbsp;
							</div>
						</div>
					</div>
					<p>
					<br><br>
					<select name='inf_tipo' id='inf_tipo'>
						<option value=''>Selecione o tipo de infração</option>
						<option value='Notificação de advertência por infração disciplinar'>Notificação de advertência por infração disciplinar</option>
						<option value='Multa por Infração Interna'>Multa por Infração Interna</option>
						<option value='Notificação de ressarcimento'>Notificação de ressarcimento</option>
						<option value='Comunicação interna'>Comunicação interna</option>
					</select>
					<p>
					<input type='text' name='inf_cidade' id='inf_cidade' placeholder='Cidade'>
					<input type='text' name='inf_data' id='inf_data' placeholder='Data' onkeypress='return mascaraData(this,event);'>
					<p>
					<input type='text' name='inf_proprietario' id='inf_proprietario' placeholder='Proprietário'>
					<input type='text' name='inf_apto' id='inf_apto' placeholder='Apto.'>
					<input type='text' name='inf_bloco' id='inf_bloco' placeholder='Bloco/Quadra'>
					<p>
					<input type='text' name='inf_endereco' id='inf_endereco' placeholder='Endereço'>
					<p>
					<input type='text' name='inf_email' id='inf_email' placeholder='Email'>
					<p>
					<input type='text' name='inf_assunto' id='inf_assunto' placeholder='Assunto'>
					<p>
					<textarea name='inf_desc_irregularidade'  rows='15' id='inf_desc_irregularidade' placeholder='Descrição da irregularidade/ocorrência, data e hora:'></textarea>
					<p>
					<textarea name='inf_desc_artigo'  rows='15' id='inf_desc_artigo' placeholder='Descrição do(s) artigo(s) que regulam o assunto:'></textarea>
					<p>
					<textarea name='inf_desc_notificacao' rows='15' id='inf_desc_notificacao' placeholder='Notificação Disciplinar:'></textarea>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_infracoes_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_infracoes_gerenciar')
{
	$inf_id = $_GET['inf_id'];
	$sqledit = "SELECT * FROM infracoes_gerenciar 
				LEFT JOIN ( cadastro_clientes 
					INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
				ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
				WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND inf_id = '$inf_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$inf_cliente = mysql_result($queryedit, 0, 'inf_cliente');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$inf_tipo = mysql_result($queryedit, 0, 'inf_tipo');
		$inf_cidade = mysql_result($queryedit, 0, 'inf_cidade');
		$inf_data = implode("/",array_reverse(explode("-",mysql_result($queryedit, 0, 'inf_data'))));
		$inf_proprietario = mysql_result($queryedit, 0, 'inf_proprietario');
		$inf_apto = mysql_result($queryedit, 0, 'inf_apto');
		$inf_bloco = mysql_result($queryedit, 0, 'inf_bloco');
		$inf_endereco = mysql_result($queryedit, 0, 'inf_endereco');
		$inf_email = mysql_result($queryedit, 0, 'inf_email');
		$inf_desc_irregularidade = mysql_result($queryedit, 0, 'inf_desc_irregularidade');
		$inf_assunto = mysql_result($queryedit, 0, 'inf_assunto');
		$inf_desc_artigo = mysql_result($queryedit, 0, 'inf_desc_artigo');
		$inf_desc_notificacao = mysql_result($queryedit, 0, 'inf_desc_notificacao');
		echo "
		<form name='form_infracoes_gerenciar' id='form_infracoes_gerenciar' enctype='multipart/form-data' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=editar&inf_id=$inf_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $inf_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='inf_id' id='inf_id' value='$inf_id' placeholder='ID'>
						<div class='suggestion'>
							<input name='inf_cliente_id' id='inf_cliente_id'  type='hidden' value='$inf_cliente' />
							<input name='inf_cliente_block' id='inf_cliente_block' value='$cli_nome_razao ($cli_cnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>
									&nbsp;
								</div>
							</div>
						</div>
						<p>
						<br><br>
						<select name='inf_tipo' id='inf_tipo'>
							<option value='$inf_tipo'>$inf_tipo</option>
							<option value='Notificação de advertência por infração disciplinar'>Notificação de advertência por infração disciplinar</option>
							<option value='Multa por Infração Interna'>Multa por Infração Interna</option>
							<option value='Notificação de ressarcimento'>Notificação de ressarcimento</option>
							<option value='Comunicação interna'>Comunicação interna</option>
						</select>
						<p>
						<input type='text' name='inf_cidade' id='inf_cidade' value='$inf_cidade' placeholder='Cidade'>
						<input type='text' name='inf_data' id='inf_data' value='$inf_data' placeholder='Data' onkeypress='return mascaraData(this,event);'>
						<p>
						<input type='text' name='inf_proprietario' id='inf_proprietario' value='$inf_proprietario' placeholder='Proprietário'>
						<input type='text' name='inf_apto' id='inf_apto' value='$inf_apto' placeholder='Apto.'>
						<input type='text' name='inf_bloco' id='inf_bloco' value='$inf_bloco' placeholder='Bloco/Quadra'>
						<p>
						<input type='text' name='inf_endereco' id='inf_endereco' value='$inf_endereco' placeholder='Endereço'>
						<p>
						<input type='text' name='inf_email' id='inf_email' value='$inf_email' placeholder='Email'>
						<p>
						<input type='text' name='inf_assunto' id='inf_assunto' value='$inf_assunto' placeholder='Assunto'>
						<p>
						<textarea name='inf_desc_irregularidade'  rows='15' id='inf_desc_irregularidade' placeholder='Descrição da irregularidade/ocorrência, data e hora:'>$inf_desc_irregularidade</textarea>
						<p>
						<textarea name='inf_desc_artigo'  rows='15' id='inf_desc_artigo' placeholder='Descrição do(s) artigo(s) que regulam o assunto:'>$inf_desc_artigo</textarea>
						<p>
						<textarea name='inf_desc_notificacao' rows='15' id='inf_desc_notificacao' placeholder='Notificação Disciplinar:'>$inf_desc_notificacao</textarea>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_infracoes_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'; value='Cancelar'/></center>
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
if($pagina == 'duplicar_infracoes_gerenciar')
{
	$inf_id = $_GET['inf_id'];
	$sqledit = "SELECT * FROM infracoes_gerenciar 
				LEFT JOIN ( cadastro_clientes 
					INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
				ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
				WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND inf_id = '$inf_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$inf_cliente = mysql_result($queryedit, 0, 'inf_cliente');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$inf_tipo = mysql_result($queryedit, 0, 'inf_tipo');
		$inf_cidade = mysql_result($queryedit, 0, 'inf_cidade');
		$inf_data = implode("/",array_reverse(explode("-",mysql_result($queryedit, 0, 'inf_data'))));
		$inf_proprietario = mysql_result($queryedit, 0, 'inf_proprietario');
		$inf_apto = mysql_result($queryedit, 0, 'inf_apto');
		$inf_bloco = mysql_result($queryedit, 0, 'inf_bloco');
		$inf_endereco = mysql_result($queryedit, 0, 'inf_endereco');
		$inf_email = mysql_result($queryedit, 0, 'inf_email');
		$inf_desc_irregularidade = mysql_result($queryedit, 0, 'inf_desc_irregularidade');
		$inf_assunto = mysql_result($queryedit, 0, 'inf_assunto');
		$inf_desc_artigo = mysql_result($queryedit, 0, 'inf_desc_artigo');
		$inf_desc_notificacao = mysql_result($queryedit, 0, 'inf_desc_notificacao');
		echo "
		<form name='form_infracoes_gerenciar' id='form_infracoes_gerenciar' enctype='multipart/form-data' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=duplicar&inf_id=$inf_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Duplicar: $inf_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='inf_id' id='inf_id' value='$inf_id' placeholder='ID'>
						<div class='suggestion'>
							<input name='inf_cliente_id' id='inf_cliente_id'  type='hidden' value='$inf_cliente' />
							<input name='inf_cliente_block' id='inf_cliente_block' value='$cli_nome_razao ($cli_cnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>
									&nbsp;
								</div>
							</div>
						</div>
						<p>
						<br><br>
						<select name='inf_tipo' id='inf_tipo'>
							<option value='$inf_tipo'>$inf_tipo</option>
							<option value='Notificação de advertência por infração disciplinar'>Notificação de advertência por infração disciplinar</option>
							<option value='Multa por Infração Interna'>Multa por Infração Interna</option>
							<option value='Notificação de ressarcimento'>Notificação de ressarcimento</option>
							<option value='Comunicação interna'>Comunicação interna</option>
						</select>
						<p>
						<input type='text' name='inf_cidade' id='inf_cidade' value='$inf_cidade' placeholder='Cidade'>
						<input type='text' name='inf_data' id='inf_data' value='$inf_data' placeholder='Data' onkeypress='return mascaraData(this,event);'>
						<p>
						<input type='text' name='inf_proprietario' id='inf_proprietario' value='' placeholder='Proprietário'>
						<input type='text' name='inf_apto' id='inf_apto' value='' placeholder='Apto.'>
						<input type='text' name='inf_bloco' id='inf_bloco' value='' placeholder='Bloco/Quadra'>
						<p>
						<input type='text' name='inf_endereco' id='inf_endereco' value='$inf_endereco' placeholder='Endereço'>
						<p>
						<input type='text' name='inf_email' id='inf_email' value='' placeholder='Email'>
						<p>
						<input type='text' name='inf_assunto' id='inf_assunto' value='$inf_assunto' placeholder='Assunto'>
						<p>
						<textarea name='inf_desc_irregularidade'  rows='15' id='inf_desc_irregularidade' placeholder='Descrição da irregularidade/ocorrência, data e hora:'>$inf_desc_irregularidade</textarea>
						<p>
						<textarea name='inf_desc_artigo'  rows='15' id='inf_desc_artigo' placeholder='Descrição do(s) artigo(s) que regulam o assunto:'>$inf_desc_artigo</textarea>
						<p>
						<textarea name='inf_desc_notificacao' rows='15' id='inf_desc_notificacao' placeholder='Notificação Disciplinar:'>$inf_desc_notificacao</textarea>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_infracoes_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'; value='Cancelar'/></center>
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
if($pagina == 'recurso_gerenciar')
{
	$inf_id = $_GET['inf_id'];
	$sqledit = "SELECT * FROM infracoes_gerenciar 
				LEFT JOIN ( cadastro_clientes 
					INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
				ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
				
				WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND inf_id = '$inf_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$inf_cliente = mysql_result($queryedit, 0, 'inf_cliente');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$inf_tipo = mysql_result($queryedit, 0, 'inf_tipo');
		$inf_cidade = mysql_result($queryedit, 0, 'inf_cidade');
		$inf_data = implode("/",array_reverse(explode("-",mysql_result($queryedit, 0, 'inf_data'))));
		$inf_proprietario = mysql_result($queryedit, 0, 'inf_proprietario');
		$inf_apto = mysql_result($queryedit, 0, 'inf_apto');
		$inf_bloco = mysql_result($queryedit, 0, 'inf_bloco');
		$inf_endereco = mysql_result($queryedit, 0, 'inf_endereco');
		$inf_email = mysql_result($queryedit, 0, 'inf_email');
		$inf_desc_irregularidade = mysql_result($queryedit, 0, 'inf_desc_irregularidade');
		$inf_assunto = mysql_result($queryedit, 0, 'inf_assunto');
		$inf_desc_artigo = mysql_result($queryedit, 0, 'inf_desc_artigo');
		$inf_desc_notificacao = mysql_result($queryedit, 0, 'inf_desc_notificacao');
		echo "
		<form name='form_recurso_gerenciar' id='form_recurso_gerenciar' enctype='multipart/form-data' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=adicionar_recurso&inf_id=$inf_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Duplicar: $inf_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='inf_id' id='inf_id' value='$inf_id' placeholder='ID'>
						<div class='suggestion'>
							<input name='inf_cliente_id' id='inf_cliente_id'  type='hidden' value='$inf_cliente' />
							<input name='inf_cliente_block' id='inf_cliente_block' value='$cli_nome_razao ($cli_cnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>
									&nbsp;
								</div>
							</div>
						</div>
						<p>
						<br><br>
						<input type='text' name='rec_assunto' id='rec_assunto' value='' placeholder='Assunto'>
						<p>
						<textarea name='rec_descricao'  rows='15' id='rec_descricao' placeholder='Descrição'></textarea>
						<p>
						<input name='rec_recurso[]' id='rec_recurso' type='file' onchange='verificaExtensao(this);'>
						<p>
						<select name='rec_status' id='rec_status'>
							<option value=''>Decisão</option>
							<option value='Deferido'>Deferido</option>
							<option value='Indeferido'>Indeferido</option>
						</select>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_recurso_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'; value='Cancelar'/></center>
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