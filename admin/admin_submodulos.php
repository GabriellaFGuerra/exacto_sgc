<?php
session_start (); 
$pagina_link = 'admin_modulos';
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
$mod_id = $_GET['mod_id'];
$sql_nome_mod = "SELECT * FROM admin_modulos WHERE mod_id = $mod_id";
$query_nome_mod = mysql_query($sql_nome_mod,$conexao);
$nome_modulo = mysql_result($query_nome_mod, 0, 'mod_nome');
$page = "Administradores &raquo; <a href='admin_modulos.php?pagina=admin_modulos".$autenticacao."'>Módulos</a>: $nome_modulo &raquo;  <a href='admin_submodulos.php?pagina=admin_submodulos&mod_id=$mod_id".$autenticacao."'>Submódulos</a>";
if($action == "adicionar")
{
	$sub_nome = $_POST['sub_nome'];
	$sub_link = $_POST['sub_link'];
	$sql = "INSERT INTO admin_submodulos (
	sub_modulo,
	sub_nome,
	sub_link
	) 
	VALUES 
	(
	'$mod_id',
	'$sub_nome',
	'$sub_link'
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
	$sub_id = $_GET['sub_id'];
	$sub_nome = $_POST['sub_nome'];
	$sub_link = $_POST['sub_link'];
	$sqlEnviaEdit = "UPDATE admin_submodulos SET 
					 sub_nome = '$sub_nome',
					 sub_link = '$sub_link'
					 WHERE sub_id = $sub_id ";
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
	$sub_id = $_GET['sub_id'];
	$sql = "DELETE FROM admin_submodulos WHERE sub_id = '$sub_id'";
				
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
$sql = "SELECT * FROM admin_submodulos 
		LEFT JOIN admin_modulos ON admin_modulos.mod_id = admin_submodulos.sub_modulo
		WHERE mod_id = $mod_id
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM admin_submodulos 
		LEFT JOIN admin_modulos ON admin_modulos.mod_id = admin_submodulos.sub_modulo
		WHERE mod_id = $mod_id";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "admin_submodulos")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Submódulo' type='button' onclick=javascript:window.location.href='admin_submodulos.php?pagina=adicionar_admin_submodulos&mod_id=$mod_id".$autenticacao."'; /></div>
		";
		if ($rows > 0)
		{
			echo "
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>Nome</td>
					<td class='titulo_tabela'>Link</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$sub_id = mysql_result($query, $x, 'sub_id');
					$sub_nome = mysql_result($query, $x, 'sub_nome');
					$sub_link = mysql_result($query, $x, 'sub_link');
					
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
							$('#normal-button-$sub_id').toolbar({content: '#user-options-$sub_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$sub_id' class='toolbar-icons' style='display: none;'>
						<a href='admin_submodulos.php?pagina=editar_admin_submodulos&sub_id=$sub_id&mod_id=$mod_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o módulo <b>$sub_nome</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'admin_submodulos.php?pagina=admin_submodulos&action=excluir&sub_id=$sub_id&mod_id=$mod_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$sub_nome</td>
							  <td>$sub_link</td>
							  <td align=center><div id='normal-button-$sub_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=admin_submodulos&mod_id=$mod_id".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum submódulo cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_admin_submodulos')
{
	echo "	
	<form name='form_admin_submodulos' id='form_admin_submodulos' enctype='multipart/form-data' method='post' action='admin_submodulos.php?pagina=admin_submodulos&action=adicionar&mod_id=$mod_id$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0'>
			<tr>
				<td align='left'>
					<input name='sub_nome' id='sub_nome' placeholder='Nome do Submódulo'>
					<p>
					<input name='sub_link' id='sub_link' placeholder='Link'>
					<p>
					<center>
					<input type='submit' id='bt_admin_submodulos' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_submodulos.php?pagina=admin_submodulos&mod_id=$mod_id".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_admin_submodulos')
{
	$sub_id = $_GET['sub_id'];
	$sqledit = "SELECT * FROM admin_submodulos WHERE sub_id = '$sub_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$sub_nome = mysql_result($queryedit, 0, 'sub_nome');
		$sub_link = mysql_result($queryedit, 0, 'sub_link');
		echo "
		<form name='form_admin_submodulos' id='form_admin_submodulos' enctype='multipart/form-data' method='post' action='admin_submodulos.php?pagina=admin_submodulos&action=editar&sub_id=$sub_id&mod_id=$mod_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $sub_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input name='sub_nome' id='sub_nome' value='$sub_nome' placeholder='Nome do Submódulo'>
						<p>
						<input name='sub_link' id='sub_link' value='$sub_link' placeholder='Link'>
						<center>
						<input type='submit' id='bt_admin_submodulos' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_submodulos.php?pagina=admin_submodulos&mod_id=$mod_id".$autenticacao."'; value='Cancelar'/></center>
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