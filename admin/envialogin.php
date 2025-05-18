<?php
session_start ();
include('../mod_includes/php/connect.php');
function getIp()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
	{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta name="author" content="Gustavo Costa">
<meta http-equiv="Content-Language" content="pt-br">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $titulo;?></title>
<?php include("../css/style.php"); ?>
<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<div id='janela' class='janela' style='display:none;'> </div>
<?php

include('../mod_includes/php/funcoes-jquery.php');
/*
include("../mod_includes/php/class.ipdetails.php");
$ip = getIp();
$ipdetails = new ipdetails($ip); 
$ipdetails->scan();
$pais = $ipdetails->get_countrycode();
$regiao = $ipdetails->get_region();
$cidade = $ipdetails->get_city();
include('../mod_includes/php/caracter_especial.php');
*/

$login = mysql_real_escape_string($_POST['login']);
$senha = mysql_real_escape_string($_POST['senha']);

$sql = "SELECT * FROM admin_usuarios
		INNER JOIN admin_setores ON admin_setores.set_id = admin_usuarios.usu_setor
		WHERE usu_login = '$login' AND usu_senha = md5('$senha')";
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);


if ($rows >0)
{
	$status = mysql_result($query, 0, 'usu_status');
	$usu_id = mysql_result($query, 0, 'usu_id');
	$n = mysql_result($query, 0, 'usu_nome');
	$s = mysql_result($query, 0, 'usu_setor');
	$s_n = mysql_result($query, 0, 'set_nome');
	
	if ($status == 0)
	{
		echo "&nbsp;
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Seu usuário está desativado, por favor contate o administrador do sistema.<br><br>'+
				'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>' );
			</SCRIPT>
			";
	}
	else
	{
	   	$_SESSION['exactoadm'] = $login.md5($n);
	   	$_SESSION['setor'] = $s;
	   	$_SESSION['setor_nome'] = $s_n;
	   	$_SESSION['usuario_id'] = $usu_id;
		$sql_log = " INSERT INTO admin_log_login (log_usuario, log_hash, log_ip, log_cidade, log_regiao, log_pais) VALUES ($usu_id, '".$_SESSION['exactoadm']."', '$ip',  '$cidade', '$regiao', '$pais') ";
		if(mysql_query($sql_log,$conexao))
		{
			echo "<script language='JavaScript'>self.location = 'admin.php?login=$login&n=$n'</script>";
		}
	}

}
else
{
  	$_SESSION['exactoadm'] = 'N';
   	$sql_log = " INSERT INTO admin_log_login (log_ip, log_observacao, log_cidade, log_regiao, log_pais) VALUES ('$ip', 'Falha login: $login | $senha ', '$cidade', '$regiao', '$pais') ";
	mysql_query($sql_log,$conexao);
  	echo "&nbsp;
   		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Login ou senha incorreta.<br>Por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>' );
		</SCRIPT>
   		";
}



?>