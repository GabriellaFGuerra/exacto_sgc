<?php
session_start (); 
$pagina_link = 'cadastro_clientes';
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
$page = "Cadastros &raquo; <a href='cadastro_clientes.php?pagina=cadastro_clientes".$autenticacao."'>Clientes</a>";
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
		WHERE cli_deletado = 1 and cli_status = 1 and ucl_usuario = '".$_SESSION['usuario_id']."' AND ".$nome_query." AND ".$cnpj_query."
		ORDER BY cli_nome_razao ASC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(*) FROM cadastro_clientes 
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
		WHERE cli_deletado = 1 and cli_status = 1 and ucl_usuario = '".$_SESSION['usuario_id']."' AND  ".$nome_query." AND ".$cnpj_query."";

$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "cadastro_clientes")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Cliente' type='button' onclick=javascript:window.location.href='cadastro_clientes.php?pagina=adicionar_cadastro_clientes".$autenticacao."'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='cadastro_clientes.php?pagina=cadastro_clientes".$autenticacao."'>
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
							echo "<a href='cadastro_clientes.php?pagina=cadastro_clientes&action=desativar&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
						}
						else
						{
							echo "<a href='cadastro_clientes.php?pagina=cadastro_clientes&action=ativar&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
						}
						echo "
						<a href='cadastro_clientes.php?pagina=editar_cadastro_clientes&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						
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
				$variavel = "&pagina=cadastro_clientes".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum cliente cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_cadastro_clientes')
{
	echo "	
	<form name='form_cadastro_clientes' id='form_cadastro_clientes' enctype='multipart/form-data' method='post' action='cadastro_clientes.php?pagina=cadastro_clientes&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='580'>
			<tr>
				<td align='left'>
					<input type='file' name='cli_foto[]' id='cli_foto'> Logo
					<p>
					<input name='cli_nome_razao' id='cli_nome_razao' placeholder='Razão Social'>
					<p>
					<div style='display:table; width:100%'>
					<input name='cli_cnpj' id='cli_cnpj' placeholder='CNPJ' maxlength='18' onkeypress='mascaraCNPJ(this); return SomenteNumero(event);' class='left'>
					<div id='cli_cnpj_erro' class='left'>&nbsp;</div>
					</div>
					<p>
					<div class='formtitulo'>Endereço</div>
					<input name='cli_cep' id='cli_cep' placeholder='CEP' maxlength='9' onkeypress='mascaraCEP(this); return SomenteNumero(event);' />
					<select name='cli_uf' id='cli_uf'>
						<option value=''>UF</option>
						"; 
						$sql = " SELECT * FROM end_uf ORDER BY uf_sigla";
						$query = mysql_query($sql,$conexao);
						while($row = mysql_fetch_array($query) )
						{
							echo "<option value='".$row['uf_id']."'>".$row['uf_sigla']."</option>";
						}
						echo "
					</select>
					<select name='cli_municipio' id='cli_municipio'>
						<option value=''>Município</option>
					</select>
					<input name='cli_bairro' id='cli_bairro' placeholder='Bairro' />
					<p>
					<input name='cli_endereco' id='cli_endereco' placeholder='Endereço' />
					<input name='cli_numero' id='cli_numero' placeholder='Número' />
					<input name='cli_comp' id='cli_comp' placeholder='Complemento' />
					<p>
					<div class='formtitulo'>Dados de Contato</div>
					<input name='cli_telefone' id='cli_telefone' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
					<p>
					<input name='cli_email' id='cli_email' placeholder='Email'>
					<input name='cli_senha' id='cli_senha' placeholder='Senha'>
					<div id='cli_email_erro'>&nbsp;</div>
					<p>";
					/*
					<div class='formtitulo'>Equipamentos vinculados a este cliente</div>
					<table width='90%' align='center'>
					<tr>
					";	
					$sql_submodulos = "SELECT * FROM cadastro_equipamentos
									   ORDER BY equ_modelo ASC
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
								<input type='checkbox' name='item_check_".$row['equ_id']."' id='item_check_".$row['equ_id']."' value='".$row['equ_id']."' > ".$row['equ_modelo']." ";
							echo $coluna;
						}
					}
					else
					{
						echo "<tr><td>Não há equipamentos cadastrados.</td><tr>";
					}
					echo "
					</table>*/
					echo "
					<div class='formtitulo'>Status do Cliente</div>
					<input type='radio' name='cli_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
					<input type='radio' name='cli_status' value='0'> Inativo<br>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_cadastro_clientes' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='cadastro_clientes.php?pagina=cadastro_clientes".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_cadastro_clientes')
{
	$cli_id = $_GET['cli_id'];
	$sqledit = "SELECT * FROM cadastro_clientes 
				LEFT JOIN end_uf ON end_uf.uf_id = cadastro_clientes.cli_uf
				LEFT JOIN end_municipios ON end_municipios.mun_id = cadastro_clientes.cli_municipio
				WHERE cli_id = '$cli_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
		$cli_cep = mysql_result($queryedit, 0, 'cli_cep');
		$cli_uf = mysql_result($queryedit, 0, 'cli_uf');
		$uf_sigla = mysql_result($queryedit, 0, 'uf_sigla');
		$cli_municipio = mysql_result($queryedit, 0, 'cli_municipio');
		$mun_nome = mysql_result($queryedit, 0, 'mun_nome');
		$cli_bairro = mysql_result($queryedit, 0, 'cli_bairro');
		$cli_endereco = mysql_result($queryedit, 0, 'cli_endereco');
		$cli_numero = mysql_result($queryedit, 0, 'cli_numero');
		$cli_comp = mysql_result($queryedit, 0, 'cli_comp');
		$cli_telefone = mysql_result($queryedit, 0, 'cli_telefone');
		$cli_email = mysql_result($queryedit, 0, 'cli_email');
		$cli_foto = mysql_result($queryedit, 0, 'cli_foto');
		$cli_senha = mysql_result($queryedit, 0, 'cli_senha');
		$cli_status = mysql_result($queryedit, 0, 'cli_status');
		echo "
		<form name='form_cadastro_clientes' id='form_cadastro_clientes' enctype='multipart/form-data' method='post' action='cadastro_clientes.php?pagina=cadastro_clientes&action=editar&cli_id=$cli_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $cli_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='cli_id' id='cli_id' value='$cli_id' placeholder='ID'>
						Foto Atual:<br>
						<img src='$cli_foto'><br>
						<input type='file' name='cli_foto[]' id='cli_foto' value='$cli_foto' > Alterar Foto
						<p>
						<input name='cli_nome_razao' id='cli_nome_razao' value='$cli_nome_razao' placeholder='Razão Social'>
						<p>
						<div style='display:table; width:100%'>
						<input name='cli_cnpj' id='cli_cnpj' value='$cli_cnpj' placeholder='CNPJ' maxlength='18' onkeypress='mascaraCNPJ(this); return SomenteNumero(event);' class='left'>
						<div id='cli_cnpj_erro' class='left'>&nbsp;</div>
						</div>
						<p>
						<div class='formtitulo'>Endereço</div>
						<input name='cli_cep' id='cli_cep' value='$cli_cep' placeholder='CEP' maxlength='9' onkeypress='mascaraCEP(this); return SomenteNumero(event);' />
						<select name='cli_uf' id='cli_uf'>
							<option value='$cli_uf'>$uf_sigla</option>
							"; 
							$sql = " SELECT * FROM end_uf ORDER BY uf_sigla";
							$query = mysql_query($sql,$conexao);
							while($row = mysql_fetch_array($query) )
							{
								echo "<option value='".$row['uf_id']."'>".$row['uf_sigla']."</option>";
							}
							echo "
						</select>
						<select name='cli_municipio' id='cli_municipio'>
							<option value='$cli_municipio'>$mun_nome</option>
						</select>
						<input name='cli_bairro' id='cli_bairro' value='$cli_bairro'  placeholder='Bairro' />
						<p>
						<input name='cli_endereco' id='cli_endereco' value='$cli_endereco' placeholder='Endereço' />
						<input name='cli_numero' id='cli_numero' value='$cli_numero' placeholder='Número' />
						<input name='cli_comp' id='cli_comp' value='$cli_comp' placeholder='Complemento' />
						<p>
						<div class='formtitulo'>Dados de Contato</div>
						<input name='cli_telefone' id='cli_telefone' value='$cli_telefone' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
						<p>
						<input name='cli_email' id='cli_email' value='$cli_email' placeholder='Email'>
						<input type='password' name='cli_senha' id='cli_senha' value='$cli_senha' placeholder='Senha'>
						<div id='cli_email_erro'>&nbsp;</div>
						<p>
						<div class='formtitulo'>Status do Cliente</div>
						";
						/*
						<div class='formtitulo'>Equipamentos vinculados a este cliente</div>
						<table width='100%'>
								<tr>";
								$sql_submodulos = "SELECT * FROM cadastro_equipamentos ORDER BY equ_modelo ASC
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
										
										$sql_itens_cad = "SELECT * FROM cadastro_clientes_equipamentos 
														  WHERE ceq_cliente = $cli_id AND ceq_equipamento = ".$row['equ_id']." ";
										$query_itens_cad = mysql_query($sql_itens_cad,$conexao);
										$rows_itens_cad = mysql_num_rows($query_itens_cad);
										if($rows_itens_cad > 0)
										{
											echo "
											<input checked type='checkbox' name='item_check_".$row['equ_id']."' id='item_check_".$row['equ_id']."' value='".$row['equ_id']."' > ".$row['equ_modelo']." ";
										}
										else
										{
											echo "
											<input type='checkbox' name='item_check_".$row['equ_id']."' id='item_check_".$row['equ_id']."' value='".$row['equ_id']."'> ".$row['equ_modelo']." ";
										}
										echo $coluna;
									}
								}
								else
								{
									echo "<tr><td>Não há equipamentos cadastrados.</td><tr>";
								}
								echo "</table>
						<p>";*/
						if($cli_status == 1)
						{
							echo "<input type='radio' name='cli_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='cli_status' value='0'> Inativo
								 ";
						}
						else
						{
							echo "<input type='radio' name='cli_status' value='1'> Ativo &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='cli_status' value='0' checked> Inativo
								 ";
						}
						echo "
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_cadastro_clientes' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='cadastro_clientes.php?pagina=cadastro_clientes$autenticacao'; value='Cancelar'/></center>
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