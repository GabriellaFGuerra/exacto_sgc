<?php
session_start (); 
$pagina_link = 'cadastro_gerentes';
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
$page = "Cadastros &raquo; <a href='cadastro_gerentes.php?pagina=cadastro_gerentes".$autenticacao."'>Gerentes</a>";
if($action == "adicionar")
{
	$ger_nome = $_POST['ger_nome'];
	
	$sql = "INSERT INTO cadastro_gerentes (
	ger_nome
	) 
	VALUES 
	(
	'$ger_nome'
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
	$ger_id = $_GET['ger_id'];
	$ger_nome = $_POST['ger_nome'];
	
	$sqlEnviaEdit = "UPDATE cadastro_gerentes SET 
					 ger_nome = '$ger_nome'
					 WHERE ger_id = $ger_id ";
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
	$ger_id = $_GET['ger_id'];
	$sql = "DELETE FROM cadastro_gerentes WHERE ger_id = '$ger_id'";
				
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
	$nome_query = " (ger_nome LIKE '%".$fil_nome."%') ";
}


$sql = "SELECT * FROM cadastro_gerentes 
		WHERE ".$nome_query." 
		GROUP BY ger_id
		ORDER BY ger_nome ASC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(DISTINCT(ger_id)) FROM cadastro_gerentes
		WHERE ".$nome_query." 
		";
		
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "cadastro_gerentes")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Gerente' type='button' onclick=javascript:window.location.href='cadastro_gerentes.php?pagina=adicionar_cadastro_gerentes".$autenticacao."'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='cadastro_gerentes.php?pagina=cadastro_gerentes".$autenticacao."'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Nome'>
			
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
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
					$ger_id = mysql_result($query, $x, 'ger_id');
					$ger_nome = mysql_result($query, $x, 'ger_nome');
					
					
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
							$('#normal-button-$ger_id').toolbar({content: '#user-options-$ger_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$ger_id' class='toolbar-icons' style='display: none;'>						
						<a href='cadastro_gerentes.php?pagina=editar_cadastro_gerentes&ger_id=$ger_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o gerente <b>$ger_nome</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'cadastro_gerentes.php?pagina=cadastro_gerentes&action=excluir&ger_id=$ger_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$ger_nome</td>							  
							  <td align=center><div id='normal-button-$ger_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=cadastro_gerentes".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum gerente cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_cadastro_gerentes')
{
	echo "	
	<form name='form_cadastro_gerentes' id='form_cadastro_gerentes' enctype='multipart/form-data' method='post' action='cadastro_gerentes.php?pagina=cadastro_gerentes&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='680'>
			<tr>
				<td align='left'>
					<input name='ger_nome' id='ger_nome' placeholder='Nome'>
					<p>					
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='submit' id='bt_cadastro_gerentes' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='cadastro_gerentes.php?pagina=cadastro_gerentes".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_cadastro_gerentes')
{
	$ger_id = $_GET['ger_id'];
	$sqledit = "SELECT * FROM cadastro_gerentes 
				WHERE ger_id = '$ger_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$ger_nome = mysql_result($queryedit, 0, 'ger_nome');		
		echo "
		<form name='form_cadastro_gerentes' id='form_cadastro_gerentes' enctype='multipart/form-data' method='post' action='cadastro_gerentes.php?pagina=cadastro_gerentes&action=editar&ger_id=$ger_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $ger_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='ger_id' id='ger_id' value='$ger_id' placeholder='ID'>
						<input name='ger_nome' id='ger_nome' value='$ger_nome' placeholder='Nome'>
						<p>						
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='submit' id='bt_cadastro_gerentes' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='cadastro_gerentes.php?pagina=cadastro_gerentes$autenticacao'; value='Cancelar'/></center>
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