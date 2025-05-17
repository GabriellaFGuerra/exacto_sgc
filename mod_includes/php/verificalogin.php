<div id='janela' class='janela' style='display:none;'> </div>
<?php 
if(isset($_SESSION['exactoadm']))
{ 

	$sqlverifica = "SELECT * FROM admin_usuarios
					LEFT JOIN admin_log_login h1 ON h1.log_usuario = admin_usuarios.usu_id 
					WHERE h1.log_id = (SELECT MAX(h2.log_id) FROM admin_log_login h2 where h2.log_usuario = h1.log_usuario) 
					AND usu_nome = \"$n\" AND usu_login = \"$login\" AND usu_status = 1";
	$queryverifica = mysql_query($sqlverifica, $conexao);
	$rowsverifica = mysql_num_rows($queryverifica);
	if($rowsverifica > 0)
	{
		if($_SESSION['exactoadm'] != mysql_result($queryverifica,0,'log_hash'))
		{
			unset($_SESSION['exactoadm']);
			unset($_SESSION['setor']);
			unset($_SESSION['setor_nome']);
			unset($_SESSION['usuario_id']);
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
		unset($_SESSION['exactoadm']);
		unset($_SESSION['setor']);
		unset($_SESSION['setor_nome']);
		unset($_SESSION['usuario_id']);
		session_write_close();
		echo "&nbsp;
			 <SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>'+
				'<input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>' );
			</SCRIPT>
			 ";
			 
	}
	
	if ((!isset($_SESSION['exactoadm']))  OR ($_SESSION['exactoadm'] != $login.md5($n)))
	{	
		unset($_SESSION['exactoadm']);
		unset($_SESSION['setor']);
		unset($_SESSION['setor_nome']);
		unset($_SESSION['usuario_id']);
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
}
elseif(isset($_SESSION['usuario_protocolo']))
{ 
	$sqlverifica = "SELECT * FROM `usuarios_internos`
					LEFT JOIN `usuario_log_login` h1 ON h1.log_usuario = `usuarios_internos`.int_id 
					WHERE h1.log_id = (SELECT MAX(h2.log_id) FROM `usuario_log_login` h2 where h2.log_usuario = h1.log_usuario) 
					AND int_nome = \"$n\" AND int_login = \"$login\" AND int_status = 1";
	$queryverifica = mysql_query($sqlverifica, $conexao);
	$rowsverifica = mysql_num_rows($queryverifica);
	if($rowsverifica > 0)
	{
		if($_SESSION['usuario_protocolo'] != mysql_result($queryverifica,0,'log_hash'))
		{
			unset($_SESSION['usuario_protocolo']);
			unset($_SESSION['setor']);
			unset($_SESSION['setor_nome']);
			unset($_SESSION['usuario_id']);
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
		unset($_SESSION['usuario_protocolo']);
		unset($_SESSION['setor']);
		unset($_SESSION['setor_nome']);
		unset($_SESSION['usuario_id']);
		session_write_close();
		echo "&nbsp;
			 <SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>'+
				'<input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>' );
			</SCRIPT>
			 ";
			 
	}
	
	if ((!isset($_SESSION['usuario_protocolo']))  OR ($_SESSION['usuario_protocolo'] != $login.md5($n)))
	{	
		unset($_SESSION['usuario_protocolo']);
		unset($_SESSION['setor']);
		unset($_SESSION['setor_nome']);
		unset($_SESSION['usuario_id']);
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
}
else
{
	unset($_SESSION['usuario_protocolo']);
	unset($_SESSION['setor']);
	unset($_SESSION['setor_nome']);
	unset($_SESSION['usuario_id']);
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
