<?php
require_once 'connect.php';

$cnpj = $_POST['cnpj'] ?? '';
$forId = $_POST['for_id'] ?? 0;

$query = 'SELECT COUNT(*) FROM cadastro_fornecedores WHERE for_cnpj = :cnpj';
$params = [':cnpj' => $cnpj];

if ($forId) {
	$query .= ' AND for_id <> :for_id';
	$params[':for_id'] = $forId;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);

echo $stmt->fetchColumn() > 0 ? 'true' : 'false';
?>