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

$sql = "SELECT * FROM cadastro_clientes
		WHERE cli_email = '$login' AND cli_senha = md5('$senha')";
$query = mysql_query($sql,$conexao);
$rows = mysql_num_rows($query);


if ($rows >0)
{
	$status = mysql_result($query, 0, 'cli_status');
	$cli_id = mysql_result($query, 0, 'cli_id');
	$n = mysql_result($query, 0, 'cli_nome_razao');
	
	if ($status == 0)
	{
		
		echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Seu usuário está desativado, por favor contate o administrador do sistema.<br><br>'+
				'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>' );
			</SCRIPT>
			";
	}
	else
	{
		$_SESSION['cliente'] = $login.md5($n);
	   	$_SESSION['cliente_id'] = $cli_id;
		$sql_log = " INSERT INTO cliente_log_login (log_usuario, log_hash, log_ip, log_cidade, log_regiao, log_pais) VALUES ($cli_id, '".$_SESSION['cliente']."', '$ip',  '$cidade', '$regiao', '$pais') ";

		if(mysql_query($sql_log,$conexao))
		{
				
			echo "<script language='JavaScript'>self.location = 'admin.php?login=$login&n=$n'</script>";
		}
	}

}
else
{
  	$_SESSION['cliente'] = 'N';
   	$sql_log = " INSERT INTO cliente_log_login (log_ip, log_observacao, log_cidade, log_regiao, log_pais) VALUES ('$ip', 'Falha login: $login | $senha ', '$cidade', '$regiao', '$pais') ";
	mysql_query($sql_log,$conexao);
  	echo "
   		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Login ou senha incorreta.<br>Por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>' );
		</SCRIPT>
   		";
}



?>