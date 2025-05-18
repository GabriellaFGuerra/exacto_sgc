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
$page = "Administradores &raquo; <a href='admin_modulos.php?pagina=admin_modulos".$autenticacao."'>Módulos</a>";
if($action == "adicionar")
{
	$mod_nome = $_POST['mod_nome'];
	$sql = "INSERT INTO admin_modulos (
	mod_nome
	) 
	VALUES 
	(
	'$mod_nome'
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
	$mod_id = $_GET['mod_id'];
	$mod_nome = $_POST['mod_nome'];
	$sqlEnviaEdit = "UPDATE admin_modulos SET 
					 mod_nome = '$mod_nome'
					 WHERE mod_id = $mod_id ";
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
	$mod_id = $_GET['mod_id'];
	$sql = "DELETE FROM admin_modulos WHERE mod_id = '$mod_id'";
				
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
$sql = "SELECT * FROM admin_modulos 
		ORDER BY mod_nome ASC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM admin_modulos ";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "admin_modulos")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Módulo' type='button' onclick=javascript:window.location.href='admin_modulos.php?pagina=adicionar_admin_modulos".$autenticacao."'; /></div>
		";
		if ($rows > 0)
		{
			echo "
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>Nome</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$mod_id = mysql_result($query, $x, 'mod_id');
					$mod_nome = mysql_result($query, $x, 'mod_nome');
					
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
							$('#normal-button-$mod_id').toolbar({content: '#user-options-$mod_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$mod_id' class='toolbar-icons' style='display: none;'>
						<a href='admin_submodulos.php?pagina=admin_submodulos&mod_id=$mod_id&$autenticacao'><img border='0' src='../imagens/icon-submodulo.png'></a>
						<a href='admin_modulos.php?pagina=editar_admin_modulos&mod_id=$mod_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o módulo <b>$mod_nome</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'admin_modulos.php?pagina=admin_modulos&action=excluir&mod_id=$mod_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$mod_nome</td>
							  <td align=center><div id='normal-button-$mod_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=admin_modulos".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum módulo cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_admin_modulos')
{
	echo "	
	<form name='form_admin_modulos' id='form_admin_modulos' enctype='multipart/form-data' method='post' action='admin_modulos.php?pagina=admin_modulos&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0'>
			<tr>
				<td align='left'>
					<input name='mod_nome' id='mod_nome' placeholder='Nome do Módulo'>
					<br>
					<br>
					<center>
					<input type='submit' id='bt_admin_modulos' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_modulos.php?pagina=admin_modulos".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_admin_modulos')
{
	$mod_id = $_GET['mod_id'];
	$sqledit = "SELECT * FROM admin_modulos WHERE mod_id = '$mod_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$mod_nome = mysql_result($queryedit, 0, 'mod_nome');
		echo "
		<form name='form_admin_modulos' id='form_admin_modulos' enctype='multipart/form-data' method='post' action='admin_modulos.php?pagina=admin_modulos&action=editar&mod_id=$mod_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $mod_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input name='mod_nome' id='mod_nome' value='$mod_nome' placeholder='Nome do Módulo'>
						<br><br>
						<center>
						<input type='submit' id='bt_admin_modulos' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_modulos.php?pagina=admin_modulos$autenticacao'; value='Cancelar'/></center>
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