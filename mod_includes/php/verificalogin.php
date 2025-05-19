<?php
session_start();
require_once 'connect.php';

function verifySession($pdo, $userTable, $idField, $statusField, $sessionIdKey)
{
	if (empty($_SESSION[$sessionIdKey])) {
		return false;
	}

	$userId = $_SESSION[$sessionIdKey];

	$sql = "SELECT $statusField FROM $userTable WHERE $idField = :id LIMIT 1";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':id', $userId, PDO::PARAM_INT);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	return ($user && $user[$statusField] == 1);
}

// Verificação para admin
if (isset($_SESSION['exactoadm']) && verifySession($pdo, 'admin_usuarios', 'usu_id', 'usu_status', 'usu_id')) {
	// Permissão concedida para admin
	return;
}

// Verificação para usuário interno
if (isset($_SESSION['usuario_protocolo']) && verifySession($pdo, 'usuarios_internos', 'int_id', 'int_status', 'int_id')) {
	// Permissão concedida para usuário interno
	return;
}

// Caso não tenha permissão, redireciona
header('Location: /admin/login.php');
exit;