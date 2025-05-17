<?php
include('connect.php');

$login = $_POST['login'] ?? '';
$usu_id = $_POST['usu_id'] ?? 0;

// Consulta segura com `Prepared Statements`
$sql = "SELECT COUNT(*) FROM admin_usuarios WHERE usu_login = :login";
if ($usu_id != 0) {
	$sql .= " AND usu_id <> :usu_id";
}

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':login', $login, PDO::PARAM_STR);
if ($usu_id != 0) {
	$stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
}
$stmt->execute();
$rows = $stmt->fetchColumn();

// Retorno seguro
echo ($rows > 0) ? "true" : "false";
?>