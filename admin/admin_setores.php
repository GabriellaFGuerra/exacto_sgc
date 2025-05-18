<?php
session_start (); 
$pagina_link = 'admin_setores';
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
$page = "Administradores &raquo; <a href='admin_setores.php?pagina=admin_setores".$autenticacao."'>Setores</a>";
if($action == "adicionar")
{
	$set_nome = $_POST['set_nome'];
	$sql = "INSERT INTO admin_setores (
	set_nome
	) 
	VALUES 
	(
	'$set_nome'
	)";
	if(mysql_query($sql,$conexao))
	{		
		$ultimo_id = mysql_insert_id();
		$erro=0;
		$sql_itens = "SELECT * FROM admin_submodulos
					  INNER JOIN admin_modulos ON admin_modulos.mod_id = admin_submodulos.sub_modulo ";
		$query_itens = mysql_query($sql_itens, $conexao);
		$rows_itens = mysql_num_rows($query_itens);
		if($rows_itens > 0 )
		{
			for($x=0; $x < $rows_itens; $x++)
			{
				$mod_id = mysql_result($query_itens,$x,'mod_id');
				$sub_id = mysql_result($query_itens,$x,'sub_id');
				$submodulo = $_POST['item_check_'.$sub_id];
				if($submodulo != '')
				{
					$sql_insere_item = "INSERT INTO admin_setores_permissoes (sep_setor, sep_modulo, sep_submodulo) VALUES ($ultimo_id, $mod_id, $submodulo) ";
					if(mysql_query($sql_insere_item))
					{
					}
					else
					{
						$erro=1;
					}
				}
			}
		}	
		if($erro != 1)
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
}

if($action == 'editar')
{
	$set_id = $_GET['set_id'];
	$set_nome = $_POST['set_nome'];
	$sqlEnviaEdit = "UPDATE admin_setores SET 
					 set_nome = '$set_nome'
					 WHERE set_id = $set_id ";
	if(mysql_query($sqlEnviaEdit,$conexao))
	{
		$ultimo_id = $set_id;
		$erro=0;
		$sql_itens = "SELECT * FROM admin_submodulos 
					  INNER JOIN admin_modulos ON admin_modulos.mod_id = admin_submodulos.sub_modulo ";
		$query_itens = mysql_query($sql_itens, $conexao);
		$rows_itens = mysql_num_rows($query_itens);
		if($rows_itens > 0 )
		{
			for($x=0; $x < $rows_itens; $x++)
			{
				$mod_id = mysql_result($query_itens,$x,'mod_id');
				$sub_id = mysql_result($query_itens,$x,'sub_id');
				$submodulo = $_POST['item_check_'.$sub_id];
				
				$sql_compara = "SELECT * FROM admin_setores_permissoes WHERE sep_setor = $ultimo_id AND sep_modulo = $mod_id AND sep_submodulo = $sub_id ";
				$query_compara = mysql_query($sql_compara,$conexao);
				$rows_compara = mysql_num_rows($query_compara);
				if($rows_compara == 0 && $submodulo != '')
				{
					
					$sql_insere_item = "INSERT INTO admin_setores_permissoes (sep_setor, sep_modulo, sep_submodulo) VALUES ($ultimo_id, $mod_id, $submodulo) ";
					if(mysql_query($sql_insere_item))
					{
					}
					else
					{
						$erro=1;
					}
				}
				elseif($rows_compara > 0 && $submodulo == '')
				{
					$sep_id = mysql_result($query_compara,0,'sep_id');
					$sql_deleta_item = "DELETE FROM admin_setores_permissoes WHERE sep_id = $sep_id ";
					if(mysql_query($sql_deleta_item))
					{
					}
					else
					{
						$erro=1;
					}
				}
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
		{	echo $sql_insere_item;
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
	$set_id = $_GET['set_id'];
	$sql = "DELETE FROM admin_setores WHERE set_id = '$set_id'";
				
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
$sql = "SELECT * FROM admin_setores 
		ORDER BY set_nome ASC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM admin_setores ";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "admin_setores")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Setor' type='button' onclick=javascript:window.location.href='admin_setores.php?pagina=adicionar_admin_setores".$autenticacao."'; /></div>
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
					$set_id = mysql_result($query, $x, 'set_id');
					$set_nome = mysql_result($query, $x, 'set_nome');
					
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
							$('#normal-button-$set_id').toolbar({content: '#user-options-$set_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$set_id' class='toolbar-icons' style='display: none;'>
						<a href='admin_setores.php?pagina=editar_admin_setores&set_id=$set_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o setor <b>$set_nome</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'admin_setores.php?pagina=admin_setores&action=excluir&set_id=$set_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$set_nome</td>
							  <td align=center><div id='normal-button-$set_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=admin_setores".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum setor cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_admin_setores')
{
	echo "	
	<form name='form_admin_setores' id='form_admin_setores' enctype='multipart/form-data' method='post' action='admin_setores.php?pagina=admin_setores&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='500'>
			<tr>
				<td align='center'>
					<input name='set_nome' id='set_nome' placeholder='Nome do Setor'>
					<p>";
					$sql_modulos = "SELECT * FROM admin_modulos ORDER BY mod_nome ASC";
					$query_modulos = mysql_query($sql_modulos, $conexao);
					$rows_modulos = mysql_num_rows($query_modulos);
					if($rows_modulos > 0)
					{
						while($row_modulos = mysql_fetch_array($query_modulos))
						{
							echo "
							<div class='formtitulo'>".$row_modulos['mod_nome']."</div>
							<table width='90%' align='center'>
							<tr>
							";	
							$sql_submodulos = "SELECT * FROM admin_submodulos
											   LEFT JOIN admin_modulos ON admin_modulos.mod_id = admin_submodulos.sub_modulo
											   WHERE mod_id = '".$row_modulos['mod_id']."'
											   
											   ";
							$query_submodulos = mysql_query($sql_submodulos, $conexao);
							$rows_submodulos = mysql_num_rows($query_submodulos);
							if($rows_submodulos > 0)
							{
								$i=0;
								while($row = mysql_fetch_array($query_submodulos))
								{
									$i++;
									if($i % 2 == 0 ? $coluna="</td></tr><tr>" : $coluna="</td>")
									echo "<td align='left' width='25%'>";
									echo "
										<input type='checkbox' name='item_check_".$row['sub_id']."' id='item_check_".$row['sub_id']."' value='".$row['sub_id']."' > ".$row['sub_nome']." ";
									echo $coluna;
								}
							}
							else
							{
								echo "<tr><td>Não há submódulos.</td><tr>";
							}
							echo "</table>";
						}
					}
					echo "
					<p>
					<center>
					<input type='submit' id='bt_admin_setores' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_setores.php?pagina=admin_setores".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_admin_setores')
{
	$set_id = $_GET['set_id'];
	$sqledit = "SELECT * FROM admin_setores WHERE set_id = '$set_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$set_nome = mysql_result($queryedit, 0, 'set_nome');
		echo "
		<form name='form_admin_setores' id='form_admin_setores' enctype='multipart/form-data' method='post' action='admin_setores.php?pagina=admin_setores&action=editar&set_id=$set_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $set_nome </div>
			<table align='center' cellspacing='0' width='500'>
				<tr>
					<td align='left'>
						<input name='set_nome' id='set_nome' value='$set_nome' placeholder='Nome do Setor'>
						<p>";
						$sql_modulos = "SELECT * FROM admin_modulos ORDER BY mod_nome ASC";
						$query_modulos = mysql_query($sql_modulos, $conexao);
						$rows_modulos = mysql_num_rows($query_modulos);
						if($rows_modulos > 0)
						{
							while($row_modulos = mysql_fetch_array($query_modulos))
							{
								echo "
								<div class='formtitulo'>".$row_modulos['mod_nome']."</div>
								<table width='100%'>
								<tr>";
								$sql_submodulos = "SELECT * FROM admin_submodulos 
												   LEFT JOIN admin_modulos ON admin_modulos.mod_id = admin_submodulos.sub_modulo
												   WHERE mod_id = '".$row_modulos['mod_id']."'";
								$query_submodulos = mysql_query($sql_submodulos, $conexao);
								$rows_submodulos = mysql_num_rows($query_submodulos);
								if($rows_submodulos > 0)
								{
									$i=0;
									while($row = mysql_fetch_array($query_submodulos))
									{
										$i++;
										if($i % 2 == 0 ? $coluna="</td></tr><tr>" : $coluna="</td>")
										echo "<td align='left' width='25%'>";
										
										$sql_itens_cad = "SELECT * FROM admin_setores_permissoes 
														  WHERE sep_setor = $set_id AND sep_submodulo = ".$row['sub_id']." ";
										$query_itens_cad = mysql_query($sql_itens_cad,$conexao);
										$rows_itens_cad = mysql_num_rows($query_itens_cad);
										if($rows_itens_cad > 0)
										{
											echo "
											<input checked type='checkbox' name='item_check_".$row['sub_id']."' id='item_check_".$row['sub_id']."' value='".$row['sub_id']."' > ".$row['sub_nome']." ";
										}
										else
										{
											echo "
											<input type='checkbox' name='item_check_".$row['sub_id']."' id='item_check_".$row['sub_id']."' value='".$row['sub_id']."'> ".$row['sub_nome']." ";
										}
										echo $coluna;
									}
								}
								else
								{
									echo "<tr><td>Não há submódulos.</td><tr>";
								}
								echo "</table>";
							}
						}
						echo "
						<p>
						<center>
						<input type='submit' id='bt_admin_setores' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_setores.php?pagina=admin_setores$autenticacao'; value='Cancelar'/></center>
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