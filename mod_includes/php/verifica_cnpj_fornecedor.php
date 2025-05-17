<?php
include('connect.php');

$cnpj = $_POST['cnpj'] ?? '';
$for_id = $_POST['for_id'] ?? 0;

// Consulta segura com `Prepared Statements`
$sql = "SELECT COUNT(*) FROM cadastro_fornecedores WHERE for_cnpj = :cnpj";
if ($for_id != 0) {
	$sql .= " AND for_id <> :for_id";
}

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cnpj', $cnpj, PDO::PARAM_STR);
if ($for_id != 0) {
	$stmt->bindParam(':for_id', $for_id, PDO::PARAM_INT);
}
$stmt->execute();
$rows = $stmt->fetchColumn();

// Retorno seguro
echo ($rows > 0) ? "true" : "false";
?>