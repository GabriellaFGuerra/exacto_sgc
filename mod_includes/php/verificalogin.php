<?php
session_start();
require_once 'connect.php';

function showAccessDenied()
{
	session_destroy();
	echo "<SCRIPT>abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>');</SCRIPT>";
	exit;
}

function verifyUser($pdo, $table, $logTable, $idField, $nameField, $loginField, $statusField, $sessionKey, $nome, $login)
{
	$sql = "SELECT * FROM $table
			LEFT JOIN $logTable h1 ON h1.log_usuario = $table.$idField
			WHERE h1.log_id = (
				SELECT MAX(h2.log_id) FROM $logTable h2 WHERE h2.log_usuario = h1.log_usuario
			)
			AND $nameField = :nome AND $loginField = :login AND $statusField = 1";

	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
	$stmt->bindParam(':login', $login, PDO::PARAM_STR);
	$stmt->execute();
	$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$usuario || $_SESSION[$sessionKey] !== $usuario['log_hash']) {
		showAccessDenied();
	}
}

if (isset($_SESSION['exactoadm'])) {
	// Defina $n e $login de acordo com sua lógica de sessão
	$n = $_SESSION['usu_nome'] ?? '';
	$login = $_SESSION['usu_login'] ?? '';
	verifyUser(
		$pdo,
		'admin_usuarios',
		'admin_log_login',
		'usu_id',
		'usu_nome',
		'usu_login',
		'usu_status',
		'exactoadm',
		$n,
		$login
	);
} elseif (isset($_SESSION['usuario_protocolo'])) {
	// Defina $n e $login de acordo com sua lógica de sessão
	$n = $_SESSION['int_nome'] ?? '';
	$login = $_SESSION['int_login'] ?? '';
	verifyUser(
		$pdo,
		'usuarios_internos',
		'usuario_log_login',
		'int_id',
		'int_nome',
		'int_login',
		'int_status',
		'usuario_protocolo',
		$n,
		$login
	);
} else {
	showAccessDenied();
}
?>