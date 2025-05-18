<?php
require_once 'connect.php';

$cpf = $_POST['cpf'] ?? '';
$reqId = (int) ($_POST['req_id'] ?? 0);

$sql = 'SELECT COUNT(*) FROM requerente WHERE req_cpf_cnpj = :cpf';
$params = [':cpf' => $cpf];

if ($reqId !== 0) {
	$sql .= ' AND req_id <> :req_id';
	$params[':req_id'] = $reqId;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo $stmt->fetchColumn() > 0 ? 'true' : 'false';
?>