<?php 
$sqlverifica = "SELECT * FROM admin_setores_permissoes
				INNER JOIN ( admin_submodulos 
					INNER JOIN admin_modulos 
					ON admin_modulos.mod_id = admin_submodulos.sub_modulo )
				ON admin_submodulos.sub_id = admin_setores_permissoes.sep_submodulo
				INNER JOIN ( admin_setores 
					INNER JOIN admin_usuarios 
					ON admin_usuarios.usu_setor = admin_setores.set_id )
				ON admin_setores.set_id = admin_setores_permissoes.sep_setor
				WHERE sep_setor = '".$_SESSION['setor']."' AND sub_link = '".$pagina_link."' AND usu_nome = \"$n\" AND usu_login = \"$login\" AND usu_status = 1";
$queryverifica = mysql_query($sqlverifica, $conexao);
$rowsverifica = mysql_num_rows($queryverifica);
if($rowsverifica > 0)
{
	
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
?>
