<?php
include('connect.php');

$cnpj = $_POST['cnpj'] ?? '';
$cli_id = $_POST['cli_id'] ?? 0;

// Consulta segura com `Prepared Statements`
$sql = "SELECT COUNT(*) FROM cadastro_clientes WHERE cli_cnpj = :cnpj";
if ($cli_id != 0) {
	$sql .= " AND cli_id <> :cli_id";
}

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cnpj', $cnpj, PDO::PARAM_STR);
if ($cli_id != 0) {
	$stmt->bindParam(':cli_id', $cli_id, PDO::PARAM_INT);
}
$stmt->execute();
$rows = $stmt->fetchColumn();

// Retorno seguro
echo ($rows > 0) ? "true" : "false";
?>