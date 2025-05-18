<?php
session_start (); 
$pagina_link = 'cadastro_clientes_inativos';
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
$page = "Cadastros &raquo; <a href='cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos".$autenticacao."'>Clientes Inativos</a>";
if($action == "adicionar")
{
	$cli_nome_razao = $_POST['cli_nome_razao'];
	$cli_cnpj = $_POST['cli_cnpj'];
	$cli_cep = $_POST['cli_cep'];
	$cli_uf = $_POST['cli_uf'];
	$cli_municipio = $_POST['cli_municipio'];
	$cli_bairro = $_POST['cli_bairro'];
	$cli_endereco = $_POST['cli_endereco'];
	$cli_numero = $_POST['cli_numero'];
	$cli_comp = $_POST['cli_comp'];
	$cli_telefone = $_POST['cli_telefone'];
	$cli_email = $_POST['cli_email'];
	$cli_senha = md5($_POST['cli_senha']);
	$cli_status = $_POST['cli_status'];
	$numeroCampos = 1;
	for ($i = 0; $i < $numeroCampos; $i++) 
	{
		$nomeArquivo[$i] = $_FILES["cli_foto"]["name"][$i];
		$tamanhoArquivo[$i] = $_FILES["cli_foto"]["size"][$i];
		$nomeTemporario[$i] = $_FILES["cli_foto"]["tmp_name"][$i];
	}
	$sql = "INSERT INTO cadastro_clientes (
	cli_nome_razao,
	cli_cnpj,
	cli_cep,
	cli_uf,
	cli_municipio,
	cli_bairro,
	cli_endereco,
	cli_numero,
	cli_comp,
	cli_telefone,
	cli_email,
	cli_senha,
	cli_status
	) 
	VALUES 
	(
	'$cli_nome_razao',
	'$cli_cnpj',
	'$cli_cep',
	'$cli_uf',
	'$cli_municipio',
	'$cli_bairro',
	'$cli_endereco',
	'$cli_numero',
	'$cli_comp',
	'$cli_telefone',
	'$cli_email',
	'$cli_senha',
	'$cli_status'
	)";
	if(mysql_query($sql,$conexao))
	{		
		$ultimo_id = mysql_insert_id();
		$caminho = "../admin/clientes/";
		for ($i = 0; $i < $numeroCampos; $i++) 
		{
			if(!empty($nomeArquivo[$i]))
			{
				if(!file_exists($caminho))
				{
					 mkdir($caminho, 0755, true); 
				}
				$extensao = pathinfo($nomeArquivo[$i], PATHINFO_EXTENSION);
				$arquivo[$i] = $caminho;
				$arquivo[$i] .= md5(mt_rand(1,10000).$nomeArquivo[$i]).'.'.$extensao;
			}
		}
		$sql_update = "UPDATE cadastro_clientes SET 
						cli_foto = '".$arquivo[0]."'
						WHERE cli_id = '$ultimo_id' ";
		if(mysql_query($sql_update,$conexao))
		{
			for ($i = 0; $i < $numeroCampos; $i++) 
			{
				if(!empty($nomeArquivo[$i]))
				{
					move_uploaded_file($nomeTemporario[$i], ($arquivo[$i]));
					$msgOK[$i] = "<img src=../img/ok.gif> O arquivo <b>".$nomeArquivo[$i]."</b> foi enviado com sucesso. <br />";
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
	$cli_id = $_GET['cli_id'];
	$cli_nome_razao = $_POST['cli_nome_razao'];
	$cli_cnpj = $_POST['cli_cnpj'];
	$cli_cep = $_POST['cli_cep'];
	$cli_uf = $_POST['cli_uf'];
	$cli_municipio = $_POST['cli_municipio'];
	$cli_bairro = $_POST['cli_bairro'];
	$cli_endereco = $_POST['cli_endereco'];
	$cli_numero = $_POST['cli_numero'];
	$cli_comp = $_POST['cli_comp'];
	$cli_telefone = $_POST['cli_telefone'];
	$cli_email = $_POST['cli_email'];
	$cli_senha = md5($_POST['cli_senha']);
	$cli_status = $_POST['cli_status'];
	$sqledit = "SELECT * FROM cadastro_clientes WHERE cli_id = '$cli_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);
	if($rowsedit > 0)
	{
		$senhacompara = mysql_result($queryedit, 0, 'cli_senha');		
	}
	if($_POST['cli_senha'] == $senhacompara)
	{
		$cli_senha = $senhacompara;
	}
	$numeroCampos = 1;
	for ($i = 0; $i < $numeroCampos; $i++) 
	{
		$nomeArquivo[$i] = $_FILES["cli_foto"]["name"][$i];
		$tamanhoArquivo[$i] = $_FILES["cli_foto"]["size"][$i];
		$nomeTemporario[$i] = $_FILES["cli_foto"]["tmp_name"][$i];
	}
	$sqlEnviaEdit = "UPDATE cadastro_clientes SET 
					 cli_nome_razao = '$cli_nome_razao',
					 cli_cnpj = '$cli_cnpj',
					 cli_cep = '$cli_cep',
					 cli_uf = '$cli_uf',
					 cli_municipio = '$cli_municipio',
					 cli_bairro = '$cli_bairro',
					 cli_endereco = '$cli_endereco',
					 cli_numero = '$cli_numero',
					 cli_comp = '$cli_comp',
					 cli_telefone = '$cli_telefone',
					 cli_email = '$cli_email',
					 cli_senha = '$cli_senha',
					 cli_status = '$cli_status'
					 WHERE cli_id = $cli_id ";
	if(mysql_query($sqlEnviaEdit,$conexao))
	{
		$ultimo_id = $cli_id;
		$caminho = "../admin/clientes/";
		for ($i = 0; $i < $numeroCampos; $i++) 
		{
			if(!empty($nomeArquivo[$i]))
			{
				if(!file_exists($caminho))
				{
					 mkdir($caminho, 0755, true); 
				}
				$extensao = pathinfo($nomeArquivo[$i], PATHINFO_EXTENSION);
				$arquivo[$i] = $caminho;
				$arquivo[$i] .= md5(mt_rand(1,10000).$nomeArquivo[$i]).'.'.$extensao;
				$sql_img_antiga = "SELECT * FROM cadastro_clientes WHERE cli_id = $cli_id";
				$query_img_antiga = mysql_query($sql_img_antiga,$conexao);
				$rows_img_antiga = mysql_num_rows($query_img_antiga);
				if($rows_img_antiga > 0)
				{
					$cli_foto_old = mysql_result($query_img_antiga, 0, 'cli_foto');
				}
				if($cli_foto_old != '')
				{
					unlink($cli_foto_old);
				}
			}
		}
		if(!empty($nomeArquivo[0]))
		{
			$sql_update = "UPDATE cadastro_clientes SET 
						cli_foto = '".$arquivo[0]."'
						WHERE cli_id = '$ultimo_id' ";
			if(mysql_query($sql_update,$conexao))
			{
				for ($i = 0; $i < $numeroCampos; $i++) 
				{
					if(!empty($nomeArquivo[$i]))
					{
						move_uploaded_file($nomeTemporario[$i], ($arquivo[$i]));
						$msgOK[$i] = "<img src=../img/ok.gif> O arquivo <b>".$nomeArquivo[$i]."</b> foi enviado com sucesso. <br />";
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
		}
		else
		{
			echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br>'+
				'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
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
	$cli_id = $_GET['cli_id'];
	
// 	$sql_gerenciar = "DELETE FROM orcamento_gerenciar WHERE orc_cliente = '$cli_id'";
// 	mysql_query($sql_gerenciar,$conexao);
	
// 	$sql_prestacao = "DELETE FROM prestacao_gerenciar WHERE pre_cliente = '$cli_id'";
// 	mysql_query($sql_prestacao,$conexao);
	
// 	$sql_malote = "DELETE FROM malote_grenciar WHERE mal_cliente = '$cli_id'";
// 	mysql_query($sql_malote,$conexao);
	
// 	$sql_documento = "DELETE FROM documento_gerenciar WHERE doc_cliente = '$cli_id'";
// 	mysql_query($sql_documento,$conexao);
	
// 	$sql = "DELETE FROM cadastro_clientes WHERE cli_id = '$cli_id'";

    $sql = "UPDATE cadastro_clientes SET cli_deletado = 0 WHERE cli_id = '$cli_id'";
				
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
	$cli_id = $_GET['cli_id'];
	$sql = "UPDATE cadastro_clientes SET cli_status = 1 WHERE cli_id = '$cli_id'";
				
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
	$cli_id = $_GET['cli_id'];
	$sql = "UPDATE cadastro_clientes SET cli_status = 0 WHERE cli_id = '$cli_id'";
				
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
$fil_nome = $_REQUEST['fil_nome'];
if($fil_nome == '')
{
	$nome_query = " 1 = 1 ";
}
else
{
	$nome_query = " (cli_nome_razao LIKE '%".$fil_nome."%') ";
}
$fil_cli_cnpj = str_replace(".","",str_replace("-","",$_REQUEST['fil_cli_cnpj']));
if($fil_cli_cnpj == '')
{
	$cnpj_query = " 1 = 1 ";
}
else
{
	$cnpj_query = " REPLACE(REPLACE(cli_cnpj, '.', ''), '-', '') LIKE '%".$fil_cli_cnpj."%' ";
}
$sql = "SELECT * FROM cadastro_clientes 
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
		WHERE cli_status = 0 and cli_deletado = 1 and ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$nome_query." AND ".$cnpj_query."
		ORDER BY cli_nome_razao ASC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM cadastro_clientes 
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
		WHERE cli_status = 0 and cli_deletado = 1 and ucl_usuario = '".$_SESSION['usuario_id']."' AND  ".$nome_query." AND ".$cnpj_query."";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "cadastro_clientes_inativos")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos".$autenticacao."'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Nome/Razão Social'>
			<input type='text' name='fil_cli_cnpj' id='fil_cli_cnpj' placeholder='C.N.P.J' value='$fil_cli_cnpj'>						
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
		";
		if ($rows > 0)
		{
		  
			echo "
			<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
				<tr>
					<td class='titulo_tabela'>Logo</td>
					<td class='titulo_tabela'>Razão Social</td>
					<td class='titulo_tabela'>CNPJ</td>
					<td class='titulo_tabela'>Telefone</td>
					<td class='titulo_tabela'>Email</td>
					<td class='titulo_tabela'>Status</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$cli_id = mysql_result($query, $x, 'cli_id');
					$cli_nome_razao = mysql_result($query, $x, 'cli_nome_razao');
					$cli_cnpj = mysql_result($query, $x, 'cli_cnpj');
					$cli_telefone = mysql_result($query, $x, 'cli_telefone');
					$cli_email = mysql_result($query, $x, 'cli_email');
					$cli_foto = mysql_result($query, $x, 'cli_foto');
					$cli_status = mysql_result($query, $x, 'cli_status');
					$cli_deletado = mysql_result($query, $x, 'cli_deletado');
					
					    if($cli_foto == '')
					{
						$cli_foto = '../imagens/nophoto.png';
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
							$('#normal-button-$cli_id').toolbar({content: '#user-options-$cli_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$cli_id' class='toolbar-icons' style='display: none;'>
						";
						if($cli_status == 1)
						{
							echo "<a href='cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=desativar&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
						}
						else
						{
							echo "<a href='cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=ativar&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
						}
						echo "
						
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o cliente <b>$cli_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=excluir&cli_id=$cli_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td><img src='".$cli_foto."' width='100'></td>
							  <td>$cli_nome_razao</td>
							  <td>$cli_cnpj</td>
							  <td>$cli_telefone</td>
							  <td>$cli_email</td>
							  <td align=center>";
							  if($cli_status == 1)
							  {
								echo "<img border='0' src='../imagens/icon-ativo.png' width='15' height='15'>";
							  }
							  else
							  {
								echo "<img border='0' src='../imagens/icon-inativo.png' width='15' height='15'>";
							  }
							  echo "
							  </td>
							  <td align=center><div id='normal-button-$cli_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
						  </tr>";
					
					
					
				}
				echo "</table>";
				$variavel = "&pagina=cadastro_clientes_inativos".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum cliente inativo.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}

	
include('../mod_rodape/rodape.php');
?>
</body>
</html>