<?php
require_once 'connect.php';

$email = $_POST['email'] ?? '';
$cli_id = (int) ($_POST['cli_id'] ?? 0);

$sql = 'SELECT COUNT(*) FROM cadastro_clientes WHERE cli_email = :email';
$params = [':email' => $email];

if ($cli_id) {
	$sql .= ' AND cli_id <> :cli_id';
	$params[':cli_id'] = $cli_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo $stmt->fetchColumn() > 0 ? 'true' : 'false';
?>