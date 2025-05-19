<?php
session_start();
require_once 'connect.php';

/**
 * Verifica se a sessão do usuário está válida.
 * Agora compara apenas o ID do usuário e se está ativo.
 * Não compara mais hash de sessão/log para evitar problemas de session_id.
 */
function verifySession($pdo, $userTable, $idField, $statusField, $sessionIdKey)
{
	if (empty($_SESSION[$sessionIdKey])) {
		header('Location: /admin/login.php');
		exit;
	}

	$userId = $_SESSION[$sessionIdKey];

	// Verifica se o usuário está ativo
	$sql = "SELECT $statusField FROM $userTable WHERE $idField = :id LIMIT 1";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$user || $user[$statusField] != 1) {
		header('Location: /admin/login.php');
		exit;
	}
}

// Verificação para admin
if (isset($_SESSION['exactoadm']) && isset($_SESSION['usu_id'])) {
	verifySession(
		$pdo,
		'admin_usuarios',
		'usu_id',
		'usu_status',
		'usu_id'
	);
}
// Verificação para usuário interno
elseif (isset($_SESSION['usuario_protocolo']) && isset($_SESSION['int_id'])) {
	verifySession(
		$pdo,
		'usuarios_internos',
		'int_id',
		'int_status',
		'int_id'
	);
} else {
	header('Location: /admin/login.php');
	exit;
}
?>