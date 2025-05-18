<?php
session_start (); 
$pagina_link = 'admin_usuarios';
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
$page = "Administradores &raquo; <a href='admin_usuarios.php?pagina=admin_usuarios".$autenticacao."'>Usuários</a>";
if($action == "adicionar")
{
	$usu_setor = $_POST['usu_setor'];
	$usu_nome = $_POST['usu_nome'];
	$usu_email = $_POST['usu_email'];
	$usu_login = $_POST['usu_login'];
	$usu_senha = md5($_POST['usu_senha']);
	$usu_status = $_POST['usu_status'];
	$usu_notificacao = $_POST['usu_notificacao'];
	$sql = "INSERT INTO admin_usuarios (
	usu_setor,
	usu_nome,
	usu_email,
	usu_login,
	usu_senha,
	usu_status,
	usu_notificacao
	) 
	VALUES 
	(
	'$usu_setor',
	'$usu_nome',
	'$usu_email',
	'$usu_login',
	'$usu_senha',
	'$usu_status',
	'$usu_notificacao'
	)";
	if(mysql_query($sql,$conexao))
	{		

		$ultimo_id = mysql_insert_id();
		$erro=0;
		$sql_itens = "SELECT * FROM cadastro_clientes ";
		$query_itens = mysql_query($sql_itens, $conexao);
		$rows_itens = mysql_num_rows($query_itens);
		if($rows_itens > 0 )
		{
			for($x=0; $x < $rows_itens; $x++)
			{				
				$cli_id = mysql_result($query_itens,$x,'cli_id');
				$cliente = $_POST['item_check_'.$cli_id];
				if($cliente != '')
				{
					$sql_insere_item = "INSERT INTO cadastro_usuarios_clientes (ucl_usuario, ucl_cliente) VALUES ($ultimo_id, $cliente) ";
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
	$usu_id = $_GET['usu_id'];
	$usu_setor = $_POST['usu_setor'];
	$usu_nome = $_POST['usu_nome'];
	$usu_email = $_POST['usu_email'];
	$usu_login = $_POST['usu_login'];
	$usu_senha = md5($_POST['usu_senha']);
	$usu_status = $_POST['usu_status'];
	$usu_notificacao = $_POST['usu_notificacao'];
	$sqledit = "SELECT * FROM admin_usuarios WHERE usu_id = '$usu_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);
	if($rowsedit > 0)
	{
		$senhacompara = mysql_result($queryedit, 0, 'usu_senha');		
	}
	if($_POST['usu_senha'] == $senhacompara)
	{
		$usu_senha = $senhacompara;
	}
	$sqlEnviaEdit = "UPDATE admin_usuarios SET 
					 usu_setor = '$usu_setor',
					 usu_nome = '$usu_nome',
					 usu_email = '$usu_email',
					 usu_login = '$usu_login',
					 usu_senha = '$usu_senha',
					 usu_status = '$usu_status',
					 usu_notificacao = '$usu_notificacao'
					 WHERE usu_id = $usu_id ";
	if(mysql_query($sqlEnviaEdit,$conexao))
	{
		$ultimo_id = $usu_id;
		$erro=0;
		$sql_itens = "SELECT * FROM cadastro_clientes 
					  ";
		$query_itens = mysql_query($sql_itens, $conexao);
		$rows_itens = mysql_num_rows($query_itens);
		if($rows_itens > 0 )
		{
			for($x=0; $x < $rows_itens; $x++)
			{
				$cli_id = mysql_result($query_itens,$x,'cli_id');
				$cliente = $_POST['item_check_'.$cli_id];
				
				$sql_compara = "SELECT * FROM cadastro_usuarios_clientes WHERE ucl_usuario = $ultimo_id AND ucl_cliente = $cli_id ";
				$query_compara = mysql_query($sql_compara,$conexao);
				$rows_compara = mysql_num_rows($query_compara);
				if($rows_compara == 0 && $cliente != '')
				{
					
					$sql_insere_item = "INSERT INTO cadastro_usuarios_clientes (ucl_usuario, ucl_cliente) VALUES ($ultimo_id, $cliente) ";
					if(mysql_query($sql_insere_item))
					{
					}
					else
					{
						$erro=1;
					}
				}
				elseif($rows_compara > 0 && $cliente == '')
				{
					$ucl_id = mysql_result($query_compara,0,'ucl_id');
					$sql_deleta_item = "DELETE FROM cadastro_usuarios_clientes WHERE ucl_id = $ucl_id ";
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
	$usu_id = $_GET['usu_id'];
	$sql = "DELETE FROM admin_usuarios WHERE usu_id = '$usu_id'";
				
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
			'<img src=../imagens/x.png> Este usuário não pode ser excluído pois está relacionado com algum usuário.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
	}
}
if($action == 'ativar')
{
	$usu_id = $_GET['usu_id'];
	$sql = "UPDATE admin_usuarios SET usu_status = 1 WHERE usu_id = '$usu_id'";
				
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
	$usu_id = $_GET['usu_id'];
	$sql = "UPDATE admin_usuarios SET usu_status = 0 WHERE usu_id = '$usu_id'";
				
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
$num_por_pagina = 10;
if(!$pag){$primeiro_registro = 0; $pag = 1;}
else{$primeiro_registro = ($pag - 1) * $num_por_pagina;}
$sql = "SELECT * FROM admin_usuarios 
		LEFT JOIN admin_setores ON admin_setores.set_id = admin_usuarios.usu_setor
		ORDER BY usu_nome ASC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM admin_usuarios ";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "admin_usuarios")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Usuário' type='button' onclick=javascript:window.location.href='admin_usuarios.php?pagina=adicionar_admin_usuarios".$autenticacao."'; /></div>
		";
		if ($rows > 0)
		{
			echo "
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>Nome</td>
					<td class='titulo_tabela'>Email</td>
					<td class='titulo_tabela'>Setor</td>
					<td class='titulo_tabela'>Login</td>
					<td class='titulo_tabela' align='center'>Status</td>
					<td class='titulo_tabela' align='center'>Recebe notificação?</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$usu_id = mysql_result($query, $x, 'usu_id');
					$set_nome = mysql_result($query, $x, 'set_nome');
					$usu_nome = mysql_result($query, $x, 'usu_nome');
					$usu_email = mysql_result($query, $x, 'usu_email');
					$usu_login = mysql_result($query, $x, 'usu_login');
					$usu_status = mysql_result($query, $x, 'usu_status');
					$usu_notificacao = mysql_result($query, $x, 'usu_notificacao');
					
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
							$('#normal-button-$usu_id').toolbar({content: '#user-options-$usu_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$usu_id' class='toolbar-icons' style='display: none;'>
						";
						if($usu_status == 1)
						{
							echo "<a href='admin_usuarios.php?pagina=admin_usuarios&action=desativar&usu_id=$usu_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
						}
						else
						{
							echo "<a href='admin_usuarios.php?pagina=admin_usuarios&action=ativar&usu_id=$usu_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
						}
						echo "
						<a href='admin_usuarios.php?pagina=editar_admin_usuarios&usu_id=$usu_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o usuário <b>$usu_nome</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'admin_usuarios.php?pagina=admin_usuarios&action=excluir&usu_id=$usu_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$usu_nome</td>
							  <td>$usu_email</td>
							  <td>$set_nome</td>
							  <td>$usu_login</td>
							  <td align=center>";
							  if($usu_status == 1)
							  {
								echo "<img border='0' src='../imagens/icon-ativo.png' width='15' height='15'>";
							  }
							  else
							  {
								echo "<img border='0' src='../imagens/icon-inativo.png' width='15' height='15'>";
							  }
							  echo "
							  </td>
							  <td align=center>";
							  if($usu_notificacao == 1)
							  {
								echo "<img border='0' src='../imagens/ok.png' width='15' height='15'>";
							  }
							  else
							  {
								echo "<img border='0' src='../imagens/x.png' width='15' height='15'>";
							  }
							  echo "
							  </td>
							  <td align=center><div id='normal-button-$usu_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=admin_usuarios".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum usuário cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_admin_usuarios')
{
	echo "	
	<form name='form_admin_usuarios' id='form_admin_usuarios' enctype='multipart/form-data' method='post' action='admin_usuarios.php?pagina=admin_usuarios&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='100%'>
			<tr>
				<td align='center'>
					<select name='usu_setor' id='usu_setor'>
						<option value=''>Setor</option>";
						$sql = " SELECT * FROM admin_setores ORDER BY set_nome";
						$query = mysql_query($sql,$conexao);
						while($row = mysql_fetch_array($query) )
						{
							echo "<option value='".$row['set_id']."'>".$row['set_nome']."</option>";
						}
						echo "
					</select>
					<p>
					<div id='usu_setor_erro' class='left'>&nbsp;</div>
					<p>
					<input name='usu_nome' id='usu_nome' placeholder='Nome do Usuário'>
					<p>
					<input name='usu_email' id='usu_email' placeholder='Email'>
					<p>
					<div id='usu_nome_erro' class='left'>&nbsp;</div>
					<p>
					<input type='text' name='usu_login' id='usu_login' placeholder='Login'>
					<input type='password' name='usu_senha' id='usu_senha' placeholder='Senha'>
					<p>
					<input type='radio' name='usu_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
					<input type='radio' name='usu_status' value='0'> Inativo<br>
					<p>
					Recebe notificação via email de novos chamados realizados?<br>
					<input type='radio' name='usu_notificacao' value='1' checked> Sim &nbsp;&nbsp;&nbsp;
					<input type='radio' name='usu_notificacao' value='0'> Não<br>
					<p>
					
					<div class='formtitulo'>Clientes que este usuário poderá visualizar</div>
					<input type='checkbox' class='todos' onclick='marcardesmarcar();' /> Marcar/desmarcar todos
					<p><br>
					<table width='100%' align='center'>
					<tr>
					";	
					$sql_submodulos = "SELECT * FROM cadastro_clientes ORDER BY cli_nome_razao ASC
										
										";
					$query_submodulos = mysql_query($sql_submodulos, $conexao);
					$rows_submodulos = mysql_num_rows($query_submodulos);
					if($rows_submodulos > 0)
					{
						$i=0;
						while($row = mysql_fetch_array($query_submodulos))
						{
							$i++;
							if($i % 3 == 0 ? $coluna="</td></tr><tr>" : $coluna="</td>")
							echo "<td align='left' width='25%'>";
							echo "
								<input type='checkbox' class='marcar' name='item_check_".$row['cli_id']."' id='item_check_".$row['cli_id']."' value='".$row['cli_id']."' > ".$row['cli_nome_razao']." ";
							echo $coluna;
						}
					}
					else
					{
						echo "<tr><td>Não há clientes cadastrados.</td><tr>";
					}
					echo "</table>

					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_admin_usuarios' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_usuarios.php?pagina=admin_usuarios".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_admin_usuarios')
{
	$usu_id = $_GET['usu_id'];
	$sqledit = "SELECT * FROM admin_usuarios
				LEFT JOIN admin_setores ON admin_setores.set_id = admin_usuarios.usu_setor
				WHERE usu_id = '$usu_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$usu_setor = mysql_result($queryedit, 0, 'usu_setor');
		$set_nome = mysql_result($queryedit, 0, 'set_nome');
		$usu_nome = mysql_result($queryedit, 0, 'usu_nome');
		$usu_email = mysql_result($queryedit, 0, 'usu_email');
		$usu_login = mysql_result($queryedit, 0, 'usu_login');
		$usu_senha = mysql_result($queryedit, 0, 'usu_senha');
		$usu_status = mysql_result($queryedit, 0, 'usu_status');
		$usu_notificacao = mysql_result($queryedit, 0, 'usu_notificacao');
		echo "
		<form name='form_admin_usuarios' id='form_admin_usuarios' enctype='multipart/form-data' method='post' action='admin_usuarios.php?pagina=admin_usuarios&action=editar&usu_id=$usu_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $usu_nome </div>
			<table align='center' cellspacing='0' width='100%'>
				<tr>
					<td align='center'>
						<input type='hidden' name='usu_id' id='usu_id' value='$usu_id' placeholder='ID'>
						<select name='usu_setor' id='usu_setor'>
							<option value='$usu_setor'>$set_nome</option>";
							$sql = " SELECT * FROM admin_setores ORDER BY set_nome";
							$query = mysql_query($sql,$conexao);
							while($row = mysql_fetch_array($query) )
							{
								echo "<option value='".$row['set_id']."'>".$row['set_nome']."</option>";
							}
							echo "
						</select>
						<p>
						<input name='usu_nome' id='usu_nome' value='$usu_nome' placeholder='Nome do Usuário'>
						<p>
						<input name='usu_email' id='usu_email' value='$usu_email' placeholder='Email'>
						<p>
						<input type='text' name='usu_login' id='usu_login' value='$usu_login' placeholder='Login'>
						<input type='password' name='usu_senha' id='usu_senha' value='$usu_senha' placeholder='Senha'>
						<p>";
						if($usu_status == 1)
						{
							echo "<input type='radio' name='usu_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='usu_status' value='0'> Inativo
								 ";
						}
						else
						{
							echo "<input type='radio' name='usu_status' value='1'> Ativo &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='usu_status' value='0' checked> Inativo
								 ";
						}
						echo "
						<p>
						Recebe notificação via email de novos chamados realizados?<br>
						";
						if($usu_notificacao == 1)
						{
							echo "<input type='radio' name='usu_notificacao' value='1' checked> Sim &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='usu_notificacao' value='0'> Não
								 ";
						}
						else
						{
							echo "<input type='radio' name='usu_notificacao' value='1'> Sim &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='usu_notificacao' value='0' checked> Não
								 ";
						}
						echo "
						<p>						
						<div class='formtitulo'>Clientes que este usuário poderá visualizar</div>
						<input type='checkbox' class='todos' onclick='marcardesmarcar();' /> Marcar/desmarcar todos
						<p><br>
						<table width='100%'>
						<tr>";
						$sql_submodulos = "SELECT * FROM cadastro_clientes where cli_status = 1 and cli_deletado = 1 ORDER BY cli_nome_razao";
						$query_submodulos = mysql_query($sql_submodulos, $conexao);
						$rows_submodulos = mysql_num_rows($query_submodulos);
						if($rows_submodulos > 0)
						{
							$i=0;
							while($row = mysql_fetch_array($query_submodulos))
							{
								$i++;
								if($i % 3 == 0 ? $coluna="</td></tr><tr>" : $coluna="</td>")
								echo "<td align='left' width='25%'>";
								
								$sql_itens_cad = "SELECT * FROM cadastro_usuarios_clientes 
													WHERE ucl_usuario = $usu_id AND ucl_cliente = ".$row['cli_id']." ";
								$query_itens_cad = mysql_query($sql_itens_cad,$conexao);
								$rows_itens_cad = mysql_num_rows($query_itens_cad);
								if($rows_itens_cad > 0)
								{
									echo "
									<input checked type='checkbox' class='marcar' name='item_check_".$row['cli_id']."' id='item_check_".$row['cli_id']."' value='".$row['cli_id']."' > ".$row['cli_nome_razao']." ";
								}
								else
								{
									echo "
									<input type='checkbox' class='marcar' name='item_check_".$row['cli_id']."' id='item_check_".$row['cli_id']."' value='".$row['cli_id']."'> ".$row['cli_nome_razao']." ";
								}
								echo $coluna;
							}
						}
						else
						{
							echo "<tr><td>Não há clientes.</td><tr>";
						}
						echo "</table>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_admin_usuarios' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_usuarios.php?pagina=admin_usuarios$autenticacao'; value='Cancelar'/></center>
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