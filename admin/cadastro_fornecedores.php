<?php
session_start (); 
$pagina_link = 'cadastro_fornecedores';
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
$page = "Cadastros &raquo; <a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores".$autenticacao."'>Fornecedores</a>";
if($action == "adicionar")
{
	$for_nome_razao = $_POST['for_nome_razao'];
	$for_cnpj = $_POST['for_cnpj'];
	$for_autonomo = $_POST['for_autonomo']; if($for_autonomo == ""){$for_autonomo = "0";}
	$for_nome_mae = $_POST['for_nome_mae'];
	$for_data_nasc = implode("-",array_reverse(explode("/",$_POST['for_data_nasc'])));
	if($for_data_nasc != ""){$for_data_nasc = "'".$for_data_nasc."'";}else{ $for_data_nasc = 'null';}
	$for_rg = $_POST['for_rg'];
	$for_cpf = $_POST['for_cpf'];
	$for_pis = $_POST['for_pis'];
	$for_cep = $_POST['for_cep'];
	$for_uf = $_POST['for_uf'];if($for_uf == ""){$for_uf = "null";}
	$for_municipio = $_POST['for_municipio'];if($for_municipio == ""){$for_municipio = "null";}
	$for_bairro = $_POST['for_bairro'];
	$for_endereco = $_POST['for_endereco'];
	$for_numero = $_POST['for_numero'];
	$for_comp = $_POST['for_comp'];
	$for_telefone = $_POST['for_telefone'];
	$for_telefone2 = $_POST['for_telefone2'];
	$for_telefone3 = $_POST['for_telefone3'];
	$for_email = $_POST['for_email'];
	$for_banco = $_POST['for_banco'];
	$for_agencia = $_POST['for_agencia'];
	$for_cc = $_POST['for_cc'];
	$for_status = $_POST['for_status'];
	$for_observacoes = $_POST['for_observacoes'];
	$sql = "INSERT INTO cadastro_fornecedores (
	for_nome_razao,
	for_cnpj,
	for_autonomo,
	for_nome_mae,
	for_data_nasc,
	for_rg,
	for_cpf,
	for_pis,
	for_cep,
	for_uf,
	for_municipio,
	for_bairro,
	for_endereco,
	for_numero,
	for_comp,
	for_telefone,
	for_telefone2,
	for_telefone3,
	for_email,
	for_banco,
	for_agencia,
	for_cc,
	for_status,
	for_observacoes
	) 
	VALUES 
	(
	'$for_nome_razao',
	'$for_cnpj',
	$for_autonomo,
	'$for_nome_mae',
	$for_data_nasc,
	'$for_rg',
	'$for_cpf',
	'$for_pis',
	'$for_cep',
	$for_uf,
	$for_municipio,
	'$for_bairro',
	'$for_endereco',
	'$for_numero',
	'$for_comp',
	'$for_telefone',
	'$for_telefone2',
	'$for_telefone3',
	'$for_email',
	'$for_banco',
	'$for_agencia',
	'$for_cc',
	'$for_status',
	'$for_observacoes'
	)";

	if(mysql_query($sql,$conexao))
	{		
		$ultimo_id = mysql_insert_id();
		$erro=0;
		$sql_itens = "SELECT * FROM cadastro_tipos_servicos";
		$query_itens = mysql_query($sql_itens, $conexao);
		$rows_itens = mysql_num_rows($query_itens);
		if($rows_itens > 0 )
		{
			for($x=0; $x < $rows_itens; $x++)
			{
				$tps_id = mysql_result($query_itens,$x,'tps_id');
				$fse_servico = $_POST['item_check_'.$tps_id];
				if($fse_servico != '')
				{
					$sql_insere_item = "INSERT INTO cadastro_fornecedores_servicos (fse_fornecedor, fse_servico) VALUES ($ultimo_id, $fse_servico) ";
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
			echo "$sql_insere_item
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>'+
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
			'<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
			"; 
	}
}

if($action == 'editar')
{
	$for_id = $_GET['for_id'];
	$for_nome_razao = $_POST['for_nome_razao'];
	$for_cnpj = $_POST['for_cnpj'];	
	$for_autonomo = $_POST['for_autonomo']; if($for_autonomo == ""){$for_autonomo = "0";}
	$for_nome_mae = $_POST['for_nome_mae'];
	$for_data_nasc = implode("-",array_reverse(explode("/",$_POST['for_data_nasc'])));
	if($for_data_nasc != ""){$for_data_nasc = "'".$for_data_nasc."'";}else{ $for_data_nasc = 'null';}
	$for_rg = $_POST['for_rg'];
	$for_cpf = $_POST['for_cpf'];
	$for_pis = $_POST['for_pis'];
	$for_cep = $_POST['for_cep'];
	$for_uf = $_POST['for_uf'];if($for_uf == ""){$for_uf = "null";}
	$for_municipio = $_POST['for_municipio'];if($for_municipio == ""){$for_municipio = "null";}
	$for_bairro = $_POST['for_bairro'];
	$for_endereco = $_POST['for_endereco'];
	$for_numero = $_POST['for_numero'];
	$for_comp = $_POST['for_comp'];
	$for_telefone = $_POST['for_telefone'];
	$for_telefone2 = $_POST['for_telefone2'];
	$for_telefone3 = $_POST['for_telefone3'];
	$for_email = $_POST['for_email'];
	$for_banco = $_POST['for_banco'];
	$for_agencia = $_POST['for_agencia'];
	$for_cc = $_POST['for_cc'];
	$for_status = $_POST['for_status'];
	$for_observacoes = $_POST['for_observacoes'];
	$sqlEnviaEdit = "UPDATE cadastro_fornecedores SET 
					 for_nome_razao = '$for_nome_razao',
					 for_cnpj = '$for_cnpj',
					 for_autonomo = $for_autonomo,
					 for_nome_mae = '$for_nome_mae',
					 for_data_nasc = $for_data_nasc,
					 for_rg = '$for_rg',
					 for_cpf = '$for_cpf',
					 for_pis = '$for_pis',
					 for_cep = '$for_cep',
					 for_uf = $for_uf,
					 for_municipio = $for_municipio,
					 for_bairro = '$for_bairro',
					 for_endereco = '$for_endereco',
					 for_numero = '$for_numero',
					 for_comp = '$for_comp',
					 for_telefone = '$for_telefone',
					 for_telefone2 = '$for_telefone2',
					 for_telefone3 = '$for_telefone3',
					 for_email = '$for_email',
					 for_banco = '$for_banco',
					 for_agencia = '$for_agencia',
					 for_cc = '$for_cc',
					 for_status = '$for_status',
					 for_observacoes = '$for_observacoes'
					 WHERE for_id = $for_id ";
	if(mysql_query($sqlEnviaEdit,$conexao))
	{
		$ultimo_id = $for_id;
		$erro=0;
		$sql_itens = "SELECT * FROM cadastro_tipos_servicos";
		$query_itens = mysql_query($sql_itens, $conexao);
		$rows_itens = mysql_num_rows($query_itens);
		if($rows_itens > 0 )
		{
			for($x=0; $x < $rows_itens; $x++)
			{
				$tps_id = mysql_result($query_itens,$x,'tps_id');
				$servico = $_POST['item_check_'.$tps_id];
				
				$sql_compara = "SELECT * FROM cadastro_fornecedores_servicos WHERE fse_fornecedor = $ultimo_id AND fse_servico = $tps_id ";
				$query_compara = mysql_query($sql_compara,$conexao);
				$rows_compara = mysql_num_rows($query_compara);
				if($rows_compara == 0 && $servico != '')
				{
					
					$sql_insere_item = "INSERT INTO cadastro_fornecedores_servicos (fse_fornecedor, fse_servico) VALUES ($ultimo_id, $servico) ";
					if(mysql_query($sql_insere_item))
					{
					}
					else
					{
						$erro=1;
					}
				}
				elseif($rows_compara > 0 && $servico == '')
				{
					$fse_id = mysql_result($query_compara,0,'fse_id');
					$sql_deleta_item = "DELETE FROM cadastro_fornecedores_servicos WHERE fse_id = $fse_id ";
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

if($action == 'excluir')
{
	$for_id = $_GET['for_id'];
	$sql = "DELETE FROM cadastro_fornecedores WHERE for_id = '$for_id'";
				
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
	$for_id = $_GET['for_id'];
	$sql = "UPDATE cadastro_fornecedores SET for_status = 1 WHERE for_id = '$for_id'";
				
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
	$for_id = $_GET['for_id'];
	$sql = "UPDATE cadastro_fornecedores SET for_status = 0 WHERE for_id = '$for_id'";
				
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
	$nome_query = " (for_nome_razao LIKE '%".$fil_nome."%') ";
}
$fil_for_cnpj = str_replace(".","",str_replace("-","",$_REQUEST['fil_for_cnpj']));
if($fil_for_cnpj == '')
{
	$cnpj_query = " 1 = 1 ";
}
else
{
	$cnpj_query = " REPLACE(REPLACE(for_cnpj, '.', ''), '-', '') LIKE '%".$fil_for_cnpj."%' ";
}
$fil_tipo_servico = $_REQUEST['fil_tipo_servico'];
if($fil_tipo_servico == '')
{
	$tipo_servico_query = " 1 = 1 ";
	$fil_tipo_servico_n = "Tipo de Serviço Prestado";
}
else
{
	$tipo_servico_query = " fse_servico = '".$fil_tipo_servico."' ";
	$sql_tipos_servicos = "SELECT * FROM cadastro_tipos_servicos WHERE tps_id = $fil_tipo_servico ";
	$query_tipos_servicos = mysql_query($sql_tipos_servicos,$conexao);
	$fil_tipo_servico_n = mysql_result($query_tipos_servicos,0,'tps_nome');
}
$sql = "SELECT * FROM cadastro_fornecedores 
		LEFT JOIN cadastro_fornecedores_servicos ON cadastro_fornecedores_servicos.fse_fornecedor = cadastro_fornecedores.for_id
		WHERE ".$nome_query." AND ".$cnpj_query." AND ".$tipo_servico_query."
		GROUP BY for_id
		ORDER BY for_nome_razao ASC
		LIMIT $primeiro_registro, $num_por_pagina ";
$cnt = "SELECT COUNT(DISTINCT(for_id)) FROM cadastro_fornecedores
		LEFT JOIN cadastro_fornecedores_servicos ON cadastro_fornecedores_servicos.fse_fornecedor = cadastro_fornecedores.for_id
		WHERE ".$nome_query." AND ".$cnpj_query." AND ".$tipo_servico_query."
		";
		
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);
if($pagina == "cadastro_fornecedores")
{
	echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Fornecedor' type='button' onclick=javascript:window.location.href='cadastro_fornecedores.php?pagina=adicionar_cadastro_fornecedores".$autenticacao."'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='cadastro_fornecedores.php?pagina=cadastro_fornecedores".$autenticacao."'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input type='text' name='fil_for_cnpj' id='fil_for_cnpj' placeholder='C.N.P.J' value='$fil_for_cnpj'>						
			<select name='fil_tipo_servico' id='fil_tipo_servico'>
				<option value='$fil_tipo_servico'>$fil_tipo_servico_n</option>
				"; 
				$sql_tipo_servico = " SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome";
				$query_tipo_servico = mysql_query($sql_tipo_servico,$conexao);
				while($row_tipo_servico = mysql_fetch_array($query_tipo_servico) )
				{
					echo "<option value='".$row_tipo_servico['tps_id']."'>".$row_tipo_servico['tps_nome']."</option>";
				}
				echo "
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
					<td class='titulo_tabela'>Razão Social</td>
					<td class='titulo_tabela'>Telefone</td>
					<td class='titulo_tabela'>Email</td>
					<td class='titulo_tabela'>Serviços Vinculados</td>
					<td class='titulo_tabela'  align=center>Status</td>
					<td class='titulo_tabela' align='center'>Gerenciar</td>
				</tr>";
				$c=0;
				for($x = 0; $x < $rows ; $x++)
				{
					$for_id = mysql_result($query, $x, 'for_id');
					$for_nome_razao = mysql_result($query, $x, 'for_nome_razao');
					$for_cnpj = mysql_result($query, $x, 'for_cnpj');
					$for_telefone = mysql_result($query, $x, 'for_telefone');
					$for_telefone2 = mysql_result($query, $x, 'for_telefone2');
					if($for_telefone2 != ''){ $for_telefone2 = "<br>".$for_telefone2;}
					$for_telefone3 = mysql_result($query, $x, 'for_telefone3');
					if($for_telefone3 != ''){ $for_telefone3 = "<br>".$for_telefone3;}
					$for_email = mysql_result($query, $x, 'for_email');
					$for_status = mysql_result($query, $x, 'for_status');
					
					$sql_ser = "SELECT * FROM cadastro_fornecedores_servicos 
							LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = cadastro_fornecedores_servicos.fse_servico
							WHERE fse_fornecedor = $for_id ";
					$query_ser = mysql_query($sql_ser,$conexao);
					$rows_ser = mysql_num_rows($query_ser);
					if($rows_ser > 0)
					{
						$servicos='';
						while($row_ser = mysql_fetch_array($query_ser))
						{
							$servicos .= $row_ser['tps_nome']."<br>";
						}
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
							$('#normal-button-$for_id').toolbar({content: '#user-options-$for_id', position: 'top', hideOnClick: true});
							$('#normal-button-bottom').toolbar({content: '#user-options', position: 'bottom'});
							$('#normal-button-small').toolbar({content: '#user-options-small', position: 'top', hideOnClick: true});
							$('#button-left').toolbar({content: '#user-options', position: 'left'});
							$('#button-right').toolbar({content: '#user-options', position: 'right'});
							$('#link-toolbar').toolbar({content: '#user-options', position: 'top' });
						});
					</script>
					<div id='user-options-$for_id' class='toolbar-icons' style='display: none;'>
						";
						if($for_status == 1)
						{
							echo "<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=desativar&for_id=$for_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
						}
						else
						{
							echo "<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=ativar&for_id=$for_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
						}
						echo "
						<a href='cadastro_fornecedores.php?pagina=editar_cadastro_fornecedores&for_id=$for_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"
							abreMask(
								'Deseja realmente excluir o fornecedor <b>$for_nome_razao</b>?<br><br>'+
								'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=excluir&for_id=$for_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
								'<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
							\">
							<img border='0' src='../imagens/icon-excluir.png'></i>
						</a>
					</div>
					";
					echo "<tr class='$c1'>
							  <td>$for_nome_razao</td>
							  <td>$for_telefone $for_telefone2 $for_telefone3</td>
							  <td>$for_email</td>
							  <td>$servicos</td>
							  <td align=center>";
							  if($for_status == 1)
							  {
								echo "<img border='0' src='../imagens/icon-ativo.png' width='15' height='15'>";
							  }
							  else
							  {
								echo "<img border='0' src='../imagens/icon-inativo.png' width='15' height='15'>";
							  }
							  echo "
							  </td>
							  <td align=center><div id='normal-button-$for_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
						  </tr>";
				}
				echo "</table>";
				$variavel = "&pagina=cadastro_fornecedores".$autenticacao."";
				include("../mod_includes/php/paginacao.php");
		}
		else
		{
			echo "<br><br><br>Não há nenhum fornecedor cadastrado.";
		}
		echo "
		<div class='titulo'>  </div>				
	</div>";
}
if($pagina == 'adicionar_cadastro_fornecedores')
{
	echo "	
	<form name='form_cadastro_fornecedores' id='form_cadastro_fornecedores' enctype='multipart/form-data' method='post' action='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='680'>
			<tr>
				<td align='left'>
					<input name='for_nome_razao' id='for_nome_razao' placeholder='Razão Social'>
					<p>
					<div style='display:table; width:100%'>
					<input name='for_cnpj' id='for_cnpj' placeholder='CNPJ' maxlength='18' onkeypress='mascaraCNPJ(this); return SomenteNumero(event);' class='left'>
					<div id='for_cnpj_erro' class='left'>&nbsp;</div>
					</div>
					<p>
					<input type='checkbox' name='for_autonomo' id='for_autonomo' value='1'> Autônomo com recolhimento de INSS?
					<p>
					<input type='text' name='for_nome_mae' id='for_nome_mae' placeholder='Nome da Mãe' style='display:none;'>
					<p>
					<input type='text' name='for_data_nasc' id='for_data_nasc' placeholder='Data Nascimento' onkeypress='return mascaraData(this,event);'style='display:none;' >
					<p>
					<input type='text' name='for_rg' id='for_rg' placeholder='RG' style='display:none;' >
					<input type='text' name='for_cpf' id='for_cpf' placeholder='CPF' onkeypress='mascaraCPF(this); return SomenteNumero(event);' style='display:none;' >
					<input type='text' name='for_pis' id='for_pis' placeholder='PIS ou NIT' style='display:none;' >
					<p>
					<div class='formtitulo'>Endereço</div>
					<input name='for_cep' id='for_cep' placeholder='CEP' maxlength='9' onkeypress='mascaraCEP(this); return SomenteNumero(event);' />
					<select name='for_uf' id='for_uf'>
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
					<select name='for_municipio' id='for_municipio'>
						<option value=''>Município</option>
					</select>
					<input name='for_bairro' id='for_bairro' placeholder='Bairro' />
					<p>
					<input name='for_endereco' id='for_endereco' placeholder='Endereço' />
					<input name='for_numero' id='for_numero' placeholder='Número' />
					<input name='for_comp' id='for_comp' placeholder='Complemento' />
					<p>
					<div class='formtitulo'>Dados de Contato</div>
					<input name='for_telefone' id='for_telefone' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
					<input name='for_telefone2' id='for_telefone2' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
					<input name='for_telefone3' id='for_telefone3' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
					<p>
					<div style='display:table; width:100%;'>
					<input name='for_email' id='for_email' placeholder='Email' style='float:left;'>
					<div id='for_email_erro'>&nbsp;</div>
					</div>
					<p>
					<div class='formtitulo'>Dados Bancários</div>
					<input type='text' name='for_banco' id='for_banco' placeholder='Banco'>
					<input type='text' name='for_agencia' id='for_agencia' placeholder='Agência' >
					<input type='text' name='for_cc' id='for_cc' placeholder='C/C'>
					<p>
					<div class='formtitulo'>Serviços vinculados a este fornecedor</div>
					<table width='90%' align='center'>
					<tr>
					";	
					$sql_submodulos = "SELECT * FROM cadastro_tipos_servicos
									   ORDER BY tps_nome ASC
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
								<input type='checkbox' name='item_check_".$row['tps_id']."' id='item_check_".$row['tps_id']."' value='".$row['tps_id']."' > ".$row['tps_nome']." ";
							echo $coluna;
						}
					}
					else
					{
						echo "<tr><td>Não há serviços cadastrados.</td><tr>";
					}
					echo "
					</table>
					<div class='formtitulo'>Status do Fornecedor</div>
					<input type='radio' name='for_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
					<input type='radio' name='for_status' value='0'> Inativo<br>
					<p>
					<textarea nome='for_observacoes' id='for_observacoes' placeholder='Observações:'></textarea>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_cadastro_fornecedores' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='cadastro_fornecedores.php?pagina=cadastro_fornecedores".$autenticacao."'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

if($pagina == 'editar_cadastro_fornecedores')
{
	$for_id = $_GET['for_id'];
	$sqledit = "SELECT * FROM cadastro_fornecedores 
				LEFT JOIN end_uf ON end_uf.uf_id = cadastro_fornecedores.for_uf
				LEFT JOIN end_municipios ON end_municipios.mun_id = cadastro_fornecedores.for_municipio
				WHERE for_id = '$for_id'";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$for_nome_razao = mysql_result($queryedit, 0, 'for_nome_razao');
		$for_cnpj = mysql_result($queryedit, 0, 'for_cnpj');
		$for_autonomo = mysql_result($queryedit, 0, 'for_autonomo');
		$for_nome_mae = mysql_result($queryedit, 0, 'for_nome_mae');
		$for_data_nasc = implode("/",array_reverse(explode("-",mysql_result($queryedit, 0, 'for_data_nasc'))));
		$for_rg = mysql_result($queryedit, 0, 'for_rg');
		$for_cpf = mysql_result($queryedit, 0, 'for_cpf ');
		$for_pis = mysql_result($queryedit, 0, 'for_pis');
		$for_cep = mysql_result($queryedit, 0, 'for_cep');
		$for_uf = mysql_result($queryedit, 0, 'for_uf');
		$uf_sigla = mysql_result($queryedit, 0, 'uf_sigla');
		$for_municipio = mysql_result($queryedit, 0, 'for_municipio');
		$mun_nome = mysql_result($queryedit, 0, 'mun_nome');
		$for_bairro = mysql_result($queryedit, 0, 'for_bairro');
		$for_endereco = mysql_result($queryedit, 0, 'for_endereco');
		$for_numero = mysql_result($queryedit, 0, 'for_numero');
		$for_comp = mysql_result($queryedit, 0, 'for_comp');
		$for_telefone = mysql_result($queryedit, 0, 'for_telefone');
		$for_telefone2 = mysql_result($queryedit, 0, 'for_telefone2');
		$for_telefone3 = mysql_result($queryedit, 0, 'for_telefone3');
		$for_email = mysql_result($queryedit, 0, 'for_email');
		$for_banco = mysql_result($queryedit, 0, 'for_banco');
		$for_agencia = mysql_result($queryedit, 0, 'for_agencia');
		$for_cc = mysql_result($queryedit, 0, 'for_cc');
		$for_status = mysql_result($queryedit, 0, 'for_status');
		$for_observacoes = mysql_result($queryedit, 0, 'for_observacoes');
		echo "
		<form name='form_cadastro_fornecedores' id='form_cadastro_fornecedores' enctype='multipart/form-data' method='post' action='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=editar&for_id=$for_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $for_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='for_id' id='for_id' value='$for_id' placeholder='ID'>
						<input name='for_nome_razao' id='for_nome_razao' value='$for_nome_razao' placeholder='Razão Social'>
						<p>
						<div style='display:table; width:100%'>
						<input name='for_cnpj' id='for_cnpj' value='$for_cnpj' placeholder='CNPJ' maxlength='18' onkeypress='mascaraCNPJ(this); return SomenteNumero(event);' class='left'>
						<div id='for_cnpj_erro' class='left'>&nbsp;</div>
						</div>
						<p>
						<input type='checkbox' name='for_autonomo' id='for_autonomo' value='1' ";if($for_autonomo == 1){ echo " checked ";} echo"> Autônomo com recolhimento de INSS?
						<p>
						";if($for_autonomo == 1)
						{
							echo " 
							<input type='text' name='for_nome_mae' value='$for_nome_mae' id='for_nome_mae' placeholder='Nome da Mãe'>
							<p>
							<input type='text' name='for_data_nasc' value='$for_data_nasc' id='for_data_nasc' placeholder='Data Nascimento' onkeypress='return mascaraData(this,event);'>
							<p>
							<input type='text' name='for_rg' value='$for_rg' id='for_rg' placeholder='RG' >
							<input type='text' name='for_cpf' value='$for_cpf' id='for_cpf' placeholder='CPF' onkeypress='mascaraCPF(this); return SomenteNumero(event);' >
							<input type='text' name='for_pis' value='$for_pis' id='for_pis' placeholder='PIS ou NIT' >
							";
						}
						else
						{
							echo " 
							<input type='text' name='for_nome_mae' id='for_nome_mae' placeholder='Nome da Mãe' style='display:none;'>
							<p>
							<input type='text' name='for_data_nasc' id='for_data_nasc' placeholder='Data Nascimento' onkeypress='return mascaraData(this,event);'style='display:none;' >
							<p>
							<input type='text' name='for_rg' id='for_rg' placeholder='RG' style='display:none;' >
							<input type='text' name='for_cpf' id='for_cpf' placeholder='CPF' onkeypress='mascaraCPF(this); return SomenteNumero(event);' style='display:none;' >
							<input type='text' name='for_pis' id='for_pis' placeholder='PIS ou NIT' style='display:none;' >
							";
						}
						echo "
						<p>
						<div class='formtitulo'>Endereço</div>
						<input name='for_cep' id='for_cep' value='$for_cep' placeholder='CEP' maxlength='9' onkeypress='mascaraCEP(this); return SomenteNumero(event);' />
						<select name='for_uf' id='for_uf'>
							<option value='$for_uf'>$uf_sigla</option>
							"; 
							$sql = " SELECT * FROM end_uf ORDER BY uf_sigla";
							$query = mysql_query($sql,$conexao);
							while($row = mysql_fetch_array($query) )
							{
								echo "<option value='".$row['uf_id']."'>".$row['uf_sigla']."</option>";
							}
							echo "
						</select>
						<select name='for_municipio' id='for_municipio'>
							<option value='$for_municipio'>$mun_nome</option>
						</select>
						<input name='for_bairro' id='for_bairro' value='$for_bairro'  placeholder='Bairro' />
						<p>
						<input name='for_endereco' id='for_endereco' value='$for_endereco' placeholder='Endereço' />
						<input name='for_numero' id='for_numero' value='$for_numero' placeholder='Número' />
						<input name='for_comp' id='for_comp' value='$for_comp' placeholder='Complemento' />
						<p>
						<div class='formtitulo'>Dados de Contato</div>
						<input name='for_telefone' id='for_telefone' value='$for_telefone' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
						<input name='for_telefone2' id='for_telefone2' value='$for_telefone2' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
						<input name='for_telefone3' id='for_telefone3' value='$for_telefone3' placeholder='Telefone (c/ DDD)' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
						<p>
						<div style='display:table; width:100%;'>
						<input name='for_email' id='for_email' value='$for_email' placeholder='Email' style='float:left;'>
						<div id='for_email_erro'>&nbsp;</div>
						</div>
						<p>
						<div class='formtitulo'>Dados Bancários</div>
						<input type='text' name='for_banco' id='for_banco' value='$for_banco' placeholder='Banco'>
						<input type='text' name='for_agencia' id='for_agencia' value='$for_agencia' placeholder='Agência' >
						<input type='text' name='for_cc' id='for_cc' value='$for_cc' placeholder='C/C'>
						<p>
						<div class='formtitulo'>Serviços vinculados a este fornecedor</div>
						<table width='100%'>
								<tr>";
								$sql_submodulos = "SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome ASC
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
										
										$sql_itens_cad = "SELECT * FROM cadastro_fornecedores_servicos 
														  WHERE fse_fornecedor = $for_id AND fse_servico = ".$row['tps_id']." ";
										$query_itens_cad = mysql_query($sql_itens_cad,$conexao);
										$rows_itens_cad = mysql_num_rows($query_itens_cad);
										if($rows_itens_cad > 0)
										{
											echo "
											<input checked type='checkbox' name='item_check_".$row['tps_id']."' id='item_check_".$row['tps_id']."' value='".$row['tps_id']."' > ".$row['tps_nome']." ";
										}
										else
										{
											echo "
											<input type='checkbox' name='item_check_".$row['tps_id']."' id='item_check_".$row['tps_id']."' value='".$row['tps_id']."'> ".$row['tps_nome']." ";
										}
										echo $coluna;
									}
								}
								else
								{
									echo "<tr><td>Não há serviços cadastrados.</td><tr>";
								}
								echo "</table>
						<p>
						<div class='formtitulo'>Status do Fornecedor</div>";
						if($for_status == 1)
						{
							echo "<input type='radio' name='for_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='for_status' value='0'> Inativo
								 ";
						}
						else
						{
							echo "<input type='radio' name='for_status' value='1'> Ativo &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='for_status' value='0' checked> Inativo
								 ";
						}
						echo "
						<p>
						<textarea nome='for_observacoes' id='for_observacoes' placeholder='Observações:'>$for_observacoes</textarea>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_cadastro_fornecedores' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='cadastro_fornecedores.php?pagina=cadastro_fornecedores$autenticacao'; value='Cancelar'/></center>
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