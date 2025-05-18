<?php session_start(); 
include('../mod_includes/php/connect.php');
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
<script src="../mod_includes/js/jquery-1.8.3.min.js" type="text/javascript"></script>
<!-- TOOLBAR -->
<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
<!-- TOOLBAR -->
<link rel="stylesheet" href="../mod_includes/js/colorpicker/css/colorpicker.css" type="text/css" />
<link rel="stylesheet" media="screen" type="text/css" href="../mod_includes/js/colorpicker/css/layout.css" />
<!--<script type="text/javascript" src="../mod_includes/js/colorpicker/js/jquery.js"></script>-->
<script type="text/javascript" src="../mod_includes/js/colorpicker/js/colorpicker.js"></script>
<script type="text/javascript" src="../mod_includes/js/colorpicker/js/eye.js"></script>
<script type="text/javascript" src="../mod_includes/js/colorpicker/js/utils.js"></script>
<script type="text/javascript" src="../mod_includes/js/colorpicker/js/layout.js?ver=1.0.2"></script>
</head>
<body>
<?php	
include		('../mod_includes/php/funcoes-jquery.php');
require_once('../mod_includes/php/verificalogin.php');
include		("../mod_topo/topo.php");
$page = "Parâmetros Gerais";
if($action == 'envia')
{
	$ger_id = $_GET['ger_id'];
	$ger_nome = $_POST['ger_nome'];
	$ger_sigla = $_POST['ger_sigla'];
	$ger_cep = $_POST['ger_cep'];
	$ger_uf = $_POST['ger_uf'];
	$ger_municipio = $_POST['ger_municipio'];
	$ger_bairro = $_POST['ger_bairro'];
	$ger_endereco = $_POST['ger_endereco'];
	$ger_numero = $_POST['ger_numero'];
	$ger_comp = $_POST['ger_comp'];
	$ger_telefone = $_POST['ger_telefone'];
	$ger_email = $_POST['ger_email'];
	$ger_site = $_POST['ger_site'];
	$ger_cor_primaria = $_POST['ger_cor_primaria'];
	$ger_cor_secundaria = $_POST['ger_cor_secundaria'];
	$ger_numeracao_anual = $_POST['ger_numeracao_anual'];
	$ger_guia_anual = $_POST['ger_guia_anual'];
	$ger_status = $_POST['ger_status'];
	$numeroCampos = 1;
	for ($i = 0; $i < $numeroCampos; $i++) 
	{
		$nomeArquivo[$i] = $_FILES["ger_logo"]["name"][$i];
		$tamanhoArquivo[$i] = $_FILES["ger_logo"]["size"][$i];
		$nomeTemporario[$i] = $_FILES["ger_logo"]["tmp_name"][$i];
	}
	
	$sqledit = "SELECT * FROM `parametros_gerais`";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);
	
	if($rowsedit > 0)
	{
		$ger_id = mysql_result($queryedit,0,'ger_id');
		$sqlEnviaEdit = "UPDATE `parametros_gerais` SET 
						 ger_nome = '$ger_nome',
						 ger_sigla = '$ger_sigla',
						 ger_cep = '$ger_cep',
						 ger_uf = '$ger_uf',
						 ger_municipio = '$ger_municipio',
						 ger_bairro = '$ger_bairro',
						 ger_endereco = '$ger_endereco',
						 ger_numero = '$ger_numero',
						 ger_comp = '$ger_comp',
						 ger_telefone = '$ger_telefone',
						 ger_email = '$ger_email',
						 ger_site = '$ger_site',
						 ger_cor_primaria = '$ger_cor_primaria',
						 ger_cor_secundaria = '$ger_cor_secundaria',
						 ger_numeracao_anual = '$ger_numeracao_anual',
						 ger_guia_anual = '$ger_guia_anual',
						 ger_status = '$ger_status'
						 WHERE ger_id = $ger_id ";
	}
	else
	{
		$sqlEnviaEdit = "INSERT INTO `parametros_gerais` ( 
							ger_nome,
							ger_sigla,
							ger_cep,
							ger_uf,
							ger_municipio,
							ger_bairro,
							ger_endereco,
							ger_numero,
							ger_comp,
							ger_telefone,
							ger_email,
							ger_site,
							ger_cor_primaria,
							ger_cor_secundaria,
							ger_numeracao_anual,
							ger_guia_anual,
							ger_status
						 ) VALUES
						 (
						 	'$ger_nome',
							'$ger_sigla',
							'$ger_cep',
							'$ger_uf',
							'$ger_municipio',
							'$ger_bairro',
							'$ger_endereco',
							'$ger_numero',
							'$ger_comp',
							'$ger_telefone',
							'$ger_email',
							'$ger_site',
							'$ger_cor_primaria',
							'$ger_cor_secundaria',
							'$ger_numeracao_anual',
							'$ger_guia_anual',
							'$ger_status'
						 )";
	}

	if(mysql_query($sqlEnviaEdit,$conexao))
	{
		if($rowsedit > 0)
		{
			$ultimo_id = $ger_id;
		}
		else
		{
			$ultimo_id = mysql_insert_id();
		}
		$caminho = "../imagens/";
		for ($i = 0; $i < $numeroCampos; $i++) 
		{
			if(!empty($nomeArquivo[$i]))
			{
				if(!file_exists($caminho))
				{
					 mkdir($caminho, 0755, true); 
				}
				$arquivo[$i] = $caminho;
				$arquivo[$i] .= "logo.png";
			}
		}
		if(!empty($nomeArquivo[0]))
		{
			$sql_update = "UPDATE `parametros_gerais` SET 
							ger_logo = '".$arquivo[0]."'
							WHERE ger_id = '$ultimo_id' ";
			if(mysql_query($sql_update,$conexao))
			{
				for ($i = 0; $i < $numeroCampos; $i++) 
				{
					if(!empty($nomeArquivo[$i]))
					{
						move_uploaded_file($nomeTemporario[$i], ($arquivo[$i]));
						$msgOK[$i] = "<img src=../img/ok.png> O arquivo <b>".$nomeArquivo[$i]."</b> foi enviado com sucesso. <br />";
					}
				} 
			}
			else
			{
				$erro=1;
			}
		}
		if($erro == 1)
		{
			echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Erro ao alterar os dados, por favor tente novamente.<br><br>'+
				'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>');
			</SCRIPT>
			";
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
				'<img src=../imagens/x.png> Erro ao alterar os dados, por favor tente novamente.<br><br>'+
				'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>');
		</SCRIPT>
		";
	}
}

if($pagina == "parametros_gerais")
{
	$sqledit = "SELECT * FROM `parametros_gerais`
				LEFT JOIN end_uf ON end_uf.uf_id = `parametros_gerais`.ger_uf
				LEFT JOIN end_municipios ON end_municipios.mun_id = `parametros_gerais`.ger_municipio
				WHERE ger_id = 1
				";
	$queryedit = mysql_query($sqledit,$conexao);
	$rowsedit = mysql_num_rows($queryedit);

	if($rowsedit > 0)
	{
		$ger_id = mysql_result($queryedit, 0, 'ger_id');
		$ger_nome = mysql_result($queryedit, 0, 'ger_nome');
		$ger_sigla = mysql_result($queryedit, 0, 'ger_sigla');
		$ger_cep = mysql_result($queryedit, 0, 'ger_cep');
		$ger_uf = mysql_result($queryedit, 0, 'ger_uf');
		$uf_sigla = mysql_result($queryedit, 0, 'uf_sigla');if($uf_sigla == ''){ $uf_sigla = "UF";}
		$ger_municipio = mysql_result($queryedit, 0, 'ger_municipio');
		$mun_nome = mysql_result($queryedit, 0, 'mun_nome');if($mun_nome == ''){ $mun_nome = "Município";}
		$ger_bairro = mysql_result($queryedit, 0, 'ger_bairro');
		$ger_endereco = mysql_result($queryedit, 0, 'ger_endereco');
		$ger_numero = mysql_result($queryedit, 0, 'ger_numero');
		$ger_comp = mysql_result($queryedit, 0, 'ger_comp');
		$ger_telefone = mysql_result($queryedit, 0, 'ger_telefone');
		$ger_email = mysql_result($queryedit, 0, 'ger_email');
		$ger_site = mysql_result($queryedit, 0, 'ger_site');
		$ger_logo = mysql_result($queryedit, 0, 'ger_logo');
		$ger_cor_primaria = mysql_result($queryedit, 0, 'ger_cor_primaria');
		$ger_cor_secundaria = mysql_result($queryedit, 0, 'ger_cor_secundaria');
		$ger_numeracao_anual = mysql_result($queryedit, 0, 'ger_numeracao_anual');
		$ger_guia_anual = mysql_result($queryedit, 0, 'ger_guia_anual');
		$ger_status = mysql_result($queryedit, 0, 'ger_status');
		
	}
		echo "
		<form name='form_parametros_gerais' id='form_parametros_gerais' enctype='multipart/form-data' method='post' action='parametros_gerais.php?pagina=parametros_gerais&action=envia&ger_id=$ger_id&$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar</div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<div class='quadro'>
						<div class='formtitulo'>Dados Gerais</div>
						<input name='ger_nome' id='ger_nome' value='$ger_nome' placeholder='Nome'>
						<input name='ger_sigla' id='ger_sigla' value='$ger_sigla' placeholder='Sigla'>
						</div>
						<p>
						<div class='quadro'>
						<div class='formtitulo'>Endereço</div>
						<input name='ger_cep' id='ger_cep' value='$ger_cep' placeholder='CEP' maxlength='9' onkeypress='mascaraCEP(this); return SomenteNumero(event);' />
						<select name='ger_uf' id='ger_uf'>
							<option value='$ger_uf'>$uf_sigla</option>
							"; 
							$sql = " SELECT * FROM end_uf ORDER BY uf_sigla";
							$query = mysql_query($sql,$conexao);
							while($row = mysql_fetch_array($query) )
							{
								echo "<option value='".$row['uf_id']."'>".$row['uf_sigla']."</option>";
							}
							echo "
						</select>
						<select name='ger_municipio' id='ger_municipio'>
							<option value='$ger_municipio'>$mun_nome</option>
						</select>
						<input name='ger_bairro' id='ger_bairro' value='$ger_bairro' placeholder='Bairro' />
						<p>
						<input name='ger_endereco' id='ger_endereco' value='$ger_endereco' placeholder='Endereço' />
						<input name='ger_numero' id='ger_numero' value='$ger_numero' placeholder='Número' />
						<input name='ger_comp' id='ger_comp' value='$ger_comp' placeholder='Complemento' />
						</div>
						<p>
						<div class='quadro'>
						<div class='formtitulo'>Contato</div>
						<input name='ger_telefone' id='ger_telefone' value='$ger_telefone' placeholder='Telefone c/ DDD' onkeypress='mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);'>
						<p>
						<input name='ger_email' id='ger_email' value='$ger_email' placeholder='Email' />
						<input name='ger_site' id='ger_site' value='$ger_site' placeholder='Site' />
						</div>
						<p>
						<div class='quadro'>
						<div class='formtitulo'>Personalização</div>
						<img src='".$ger_logo."' valign='middle' width='39'> Logo: <input type='file' id='ger_logo' name='ger_logo[]' value='$ger_logo' /> 
						<p>
						<input readonly size='2' style='background-color:#$ger_cor_primaria' class='cor' /> Cor primária: &nbsp;&nbsp;&nbsp;&nbsp; <input background-color='#$ger_cor_primaria' type='text' maxlength='6' size='6' id='colorpickerField1' value='$ger_cor_primaria' name='ger_cor_primaria' placeholder='Selecione a cor' />
						<p>
						<input readonly size='2' style='background-color:#$ger_cor_secundaria' class='cor' /> Cor secundária: <input type='text' maxlength='6' size='6' id='colorpickerField3' value='$ger_cor_secundaria' name='ger_cor_secundaria' placeholder='Selecione a cor' />
						<p>
						</div>
						<p>
						<div class='quadro'>
						<div class='formtitulo'>Particularidades</div>
						Numeração do processo zera anualmente? 
						";
						if($ger_numeracao_anual == 1)
						{
							echo "<input type='radio' name='ger_numeracao_anual' value='1' checked> Sim &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								  <input type='radio' name='ger_numeracao_anual' value='0'> Não
								 ";
						}
						else
						{
							echo "<input type='radio' name='ger_numeracao_anual' value='1'> Sim &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								  <input type='radio' name='ger_numeracao_anual' value='0' checked> Não
								 ";
						}
						echo "
						<p>
						Numeração da guia zera anualmente? 
						";
						if($ger_guia_anual == 1)
						{
							echo "<input type='radio' name='ger_guia_anual' value='1' checked> Sim &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								  <input type='radio' name='ger_guia_anual' value='0'> Não
								 ";
						}
						else
						{
							echo "<input type='radio' name='ger_guia_anual' value='1'> Sim &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								  <input type='radio' name='ger_guia_anual' value='0' checked> Não
								 ";
						}
						echo "
						<p>
						Status do portal: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
						";
						if($ger_status == 1)
						{
							echo "<input type='radio' name='ger_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
								  <input type='radio' name='ger_status' value='0'> Inativo
								 ";
						}
						else
						{
							echo "<input type='radio' name='ger_status' value='1'> Ativo &nbsp;&nbsp;&nbsp;&nbsp;
								  <input type='radio' name='ger_status' value='0' checked> Inativo
								 ";
						}
						echo "
						</div>
						<br><br>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='submit' id='bt_parametros_gerais' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='parametros_gerais.php?pagina=parametros_gerais$autenticacao'; value='Cancelar'/>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'>   </div>
		</div>
		</form>
		";
}
include('../mod_rodape/rodape.php');
?>
</body>
</html>