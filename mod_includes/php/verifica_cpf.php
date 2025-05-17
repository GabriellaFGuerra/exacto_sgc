<?php
include('connect.php');

$cpf = $_POST['cpf'] ?? '';
$req_id = $_POST['req_id'] ?? 0;

// Consulta segura com `Prepared Statements`
$sql = "SELECT COUNT(*) FROM requerente WHERE req_cpf_cnpj = :cpf";
if ($req_id != 0) {
	$sql .= " AND req_id <> :req_id";
}

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cpf', $cpf, PDO::PARAM_STR);
if ($req_id != 0) {
	$stmt->bindParam(':req_id', $req_id, PDO::PARAM_INT);
}
$stmt->execute();
$rows = $stmt->fetchColumn();

// Retorno seguro
echo ($rows > 0) ? "true" : "false";
?>