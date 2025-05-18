<?php
require_once 'connect.php';

$login = $_POST['login'] ?? '';
$usu_id = (int) ($_POST['usu_id'] ?? 0);

$sql = 'SELECT COUNT(*) FROM admin_usuarios WHERE usu_login = :login';
$params = [':login' => $login];

if ($usu_id) {
	$sql .= ' AND usu_id <> :usu_id';
	$params[':usu_id'] = $usu_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo $stmt->fetchColumn() > 0 ? 'true' : 'false';
?>