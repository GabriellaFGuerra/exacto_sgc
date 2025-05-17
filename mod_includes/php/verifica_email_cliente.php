<?php
include('connect.php');

$email = $_POST['email'] ?? '';
$cli_id = $_POST['cli_id'] ?? 0;

// Consulta segura com `Prepared Statements`
$sql = "SELECT COUNT(*) FROM cadastro_clientes WHERE cli_email = :email";
if ($cli_id != 0) {
	$sql .= " AND cli_id <> :cli_id";
}

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
if ($cli_id != 0) {
	$stmt->bindParam(':cli_id', $cli_id, PDO::PARAM_INT);
}
$stmt->execute();
$rows = $stmt->fetchColumn();

// Retorno seguro
echo ($rows > 0) ? "true" : "false";
?>