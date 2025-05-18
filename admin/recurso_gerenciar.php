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
$page = "<a href='infracoes_gerenciar.php?pagina=infracoes_gerenciar".$autenticacao."'>Infrações</a> &raquo;  Recurso ";

if($action == 'editar')
{
	$rec_id = $_GET['rec_id'];
	$rec_assunto = $_POST['rec_assunto'];
	$rec_descricao = $_POST['rec_descricao'];
	$rec_status = $_POST['rec_status'];
	$sqlEnviaEdit = "UPDATE recurso_gerenciar SET 
					 rec_assunto = '$rec_assunto',
					 rec_descricao = '$rec_descricao',
					 rec_status = '$rec_status'
					 WHERE rec_id = $rec_id ";

	if(mysql_query($sqlEnviaEdit,$conexao))
	{
		
		$ultimo_id = $rec_id;
		$erro=0;
		
		$rec_recurso = $_FILES['rec_recurso']["name"];
		$tmp_anexo = $_FILES['rec_recurso']["tmp_name"];
		$caminho = "../admin/recurso/$ultimo_id/";
		if(!file_exists($caminho))
		{
			 mkdir($caminho, 0755, true); 
		}
		$sql_orcamento = "SELECT * FROM recurso_gerenciar
						  WHERE rec_id = $rec_id ";
		$query_orcamento = mysql_query($sql_orcamento,$conexao);
		$query_f = mysql_query($sql_orcamento,$conexao);
		$rows_orcamento = mysql_num_rows($query_orcamento);
		if($rows_orcamento > 0)
		{
			$anexo = mysql_result($query_orcamento, 0, 'rec_recurso');
		}
		foreach($rec_recurso as $k => $value)
		{
			if($rec_recurso[$k] != '')
			{
				$extensao = pathinfo($rec_recurso[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho;
				$arquivo .= md5(mt_rand(1,10000).$rec_recurso[$k]).'.'.$extensao;
				move_uploaded_file($tmp_anexo[$k], ($arquivo));
				unlink($anexo);
				$sql_update = "UPDATE recurso_gerenciar SET 
							   rec_recurso = '".$arquivo."' 
							   WHERE rec_id = $rec_id 
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

if($pagina == "recurso_gerenciar")
{
	$rec_id = $_GET['rec_id'];
	$sqledit = "SELECT * FROM recurso_gerenciar 
				LEFT JOIN ( infracoes_gerenciar 
					LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente	)
				ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao										
				WHERE rec_id = '$rec_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$inf_cliente = mysql_result($queryedit, 0, 'inf_cliente');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$rec_assunto = mysql_result($queryedit, 0, 'rec_assunto');
		$rec_descricao = mysql_result($queryedit, 0, 'rec_descricao');
		$rec_recurso = mysql_result($queryedit, 0, 'rec_recurso');
		$rec_status = mysql_result($queryedit, 0, 'rec_status');
		echo "
		<form name='form_recurso_gerenciar' id='form_recurso_gerenciar' enctype='multipart/form-data' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=adicionar_recurso&inf_id=$inf_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Gerenciar: $inf_nome </div>
			<div style='width:100%; text-align:right;'>
				<a href='recurso_protocolo_imprimir.php?rec_id=$rec_id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle' title='Gerar Protocolo'></a>
				<a href='recurso_imprimir.php?rec_id=$rec_id&autenticacao' target='_blank'><img src='../imagens/icon-carta.png' border='0' valign='middle' title='Gerar Carta'></a>
				<a href='recurso_gerenciar.php?pagina=editar_recurso_gerenciar&rec_id=$rec_id$autenticacao'><img src='../imagens/icon-editar.png' border='0' valign='middle' title='Editar'></a>
				<a onclick=\"
							abreMask(
								'Deseja realmente excluir este recurso?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=excluir_recurso&rec_id=$rec_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\"><img src='../imagens/icon-excluir.png' border='0' valign='middle' title='Excluir'></a>
			</div>
			<table align='center' cellspacing='0' width='90%'>
				<tr>
					<td align='left'>
						<input type='hidden' name='inf_id' id='inf_id' value='$inf_id' placeholder='ID'>
						<b>Cliente:</b> $cli_nome_razao ($cli_cnpj)
						<p>
						<b>Recurso:</b> <a href='$rec_recurso' target='_blank'><img src='../imagens/icon-pdf.png' border='0' valign='middle'></a>
						<p>
						<b>Status:</b> $rec_status
						<p>
						Mogi das Cruzes, ".date('d/m/Y')."
						<p>
						$rec_assunto
						<p>
						$rec_descricao
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'; value='Voltar'/></center>
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
if($pagina == 'editar_recurso_gerenciar')
{
	$rec_id = $_GET['rec_id'];
	$sqledit = "SELECT * FROM recurso_gerenciar 
				WHERE rec_id = '$rec_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);
	if($rowsedit > 0)
	{
		$rec_assunto = mysql_result($queryedit, 0, 'rec_assunto');
		$rec_descricao = mysql_result($queryedit, 0, 'rec_descricao');
		$rec_recurso = mysql_result($queryedit, 0, 'rec_recurso');
		$rec_status = mysql_result($queryedit, 0, 'rec_status');
		echo "
		<form name='form_recurso_gerenciar' id='form_recurso_gerenciar' enctype='multipart/form-data' method='post' action='recurso_gerenciar.php?pagina=recurso_gerenciar&action=editar&rec_id=$rec_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Duplicar: $inf_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='text' name='rec_assunto' id='rec_assunto' value='$rec_assunto' placeholder='Assunto'>
						<p>
						<textarea name='rec_descricao'  rows='15' id='rec_descricao' placeholder='Descrição'>$rec_descricao</textarea>
						<p>
						Recurso atual: <a href='$rec_recurso' target='_blank'><img src='../imagens/icon-pdf.png' border='0' valign='middle'></a>
						<br>
						Alterar recurso: <input name='rec_recurso[]' id='rec_recurso' type='file' onchange='verificaExtensao(this);'>
						<p>
						<select name='rec_status' id='rec_status'>
							<option value='$rec_status'>$rec_status</option>
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