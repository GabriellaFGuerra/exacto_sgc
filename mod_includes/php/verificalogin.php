<?php
session_start();
include('connect.php');

if (isset($_SESSION['exactoadm'])) {
	$sqlverifica = "SELECT * FROM admin_usuarios
                    LEFT JOIN admin_log_login h1 ON h1.log_usuario = admin_usuarios.usu_id
                    WHERE h1.log_id = (SELECT MAX(h2.log_id) FROM admin_log_login h2 WHERE h2.log_usuario = h1.log_usuario)
                    AND usu_nome = :nome AND usu_login = :login AND usu_status = 1";

	$stmt = $pdo->prepare($sqlverifica);
	$stmt->bindParam(':nome', $n, PDO::PARAM_STR);
	$stmt->bindParam(':login', $login, PDO::PARAM_STR);
	$stmt->execute();
	$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($usuario) {
		if ($_SESSION['exactoadm'] !== $usuario['log_hash']) {
			session_destroy();
			echo "<SCRIPT>abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>');</SCRIPT>";
			exit;
		}
	} else {
		session_destroy();
		echo "<SCRIPT>abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>');</SCRIPT>";
		exit;
	}
} elseif (isset($_SESSION['usuario_protocolo'])) {
	$sqlverifica = "SELECT * FROM usuarios_internos
                    LEFT JOIN usuario_log_login h1 ON h1.log_usuario = usuarios_internos.int_id
                    WHERE h1.log_id = (SELECT MAX(h2.log_id) FROM usuario_log_login h2 WHERE h2.log_usuario = h1.log_usuario)
                    AND int_nome = :nome AND int_login = :login AND int_status = 1";

	$stmt = $pdo->prepare($sqlverifica);
	$stmt->bindParam(':nome', $n, PDO::PARAM_STR);
	$stmt->bindParam(':login', $login, PDO::PARAM_STR);
	$stmt->execute();
	$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($usuario) {
		if ($_SESSION['usuario_protocolo'] !== $usuario['log_hash']) {
			session_destroy();
			echo "<SCRIPT>abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>');</SCRIPT>";
			exit;
		}
	} else {
		session_destroy();
		echo "<SCRIPT>abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>');</SCRIPT>";
		exit;
	}
} else {
	session_destroy();
	echo "<SCRIPT>abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>');</SCRIPT>";
	exit;
}
?>