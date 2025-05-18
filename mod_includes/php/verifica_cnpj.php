<?php
require_once 'connect.php';

$cnpj = $_POST['cnpj'] ?? '';
$cli_id = (int) ($_POST['cli_id'] ?? 0);

$sql = 'SELECT COUNT(*) FROM cadastro_clientes WHERE cli_cnpj = :cnpj';
$params = [':cnpj' => $cnpj];

if ($cli_id) {
	$sql .= ' AND cli_id <> :cli_id';
	$params[':cli_id'] = $cli_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo $stmt->fetchColumn() > 0 ? 'true' : 'false';
?>