<div id='janela' class='janela' style='display:none;'> </div>
<?php 
$sqlverifica = "SELECT * FROM cadastro_clientes
				LEFT JOIN cliente_log_login h1 ON h1.log_usuario = cadastro_clientes.cli_id 
				WHERE h1.log_id = (SELECT MAX(h2.log_id) FROM cliente_log_login h2 where h2.log_usuario = h1.log_usuario) 
				AND cli_nome_razao = \"$n\" AND cli_email = \"$login\" AND cli_status = 1";
$queryverifica = mysql_query($sqlverifica, $conexao);
$rowsverifica = mysql_num_rows($queryverifica);
if($rowsverifica > 0)
{
	if($_SESSION['cliente'] != mysql_result($queryverifica,0,'log_hash'))
	{
		unset($_SESSION['cliente']);
		unset($_SESSION['cliente_id']);
		unset($_SESSION['contato_id']);
		session_write_close();
		echo "&nbsp;
			 <SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>'+
				'<input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>' );
			</SCRIPT>
			 ";
	}
}
else
{
	unset($_SESSION['cliente']);
	unset($_SESSION['cliente_id']);
	unset($_SESSION['contato_id']);
	session_write_close();
	echo "&nbsp;
		 <SCRIPT language='JavaScript'>
		 	abreMask(
			'<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>' );
		</SCRIPT>
		 ";
		 
}

//($_SESSION['cliente'] != $login.md5($n))

if ((!isset($_SESSION['cliente'])))
{	
    
    // echo $login.md5($n);
    // echo "-";
    // echo $_SESSION['cliente'];
    // exit();
    
	unset($_SESSION['cliente']);
	unset($_SESSION['cliente_id']);
	unset($_SESSION['contato_id']);
	session_write_close();
	echo "&nbsp;
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>' );
		</SCRIPT>
		 ";
	exit;
}
?>
