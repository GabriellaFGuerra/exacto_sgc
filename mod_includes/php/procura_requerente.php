<?php
require_once 'connect.php';

$busca = str_replace(['.', '-'], '', $_POST['busca'] ?? '');
$tipoRequerente = $_POST['tipo_requerente'] ?? '';

if ($busca === '') {
	return;
}

switch ($tipoRequerente) {
	case 'Externo':
		$sql = "SELECT * FROM requerente 
				WHERE req_nome LIKE :busca 
				OR REPLACE(REPLACE(req_cpf_cnpj, '.', ''), '-', '') LIKE :busca
				ORDER BY req_nome ASC";
		break;
	case 'Interno':
		$sql = "SELECT * FROM usuarios_internos 
				LEFT JOIN departamentos ON departamentos.dep_id = usuarios_internos.int_departamento
				WHERE int_nome LIKE :busca
				ORDER BY int_nome ASC";
		break;
	default:
		return;
}

$stmt = $pdo->prepare($sql);
$buscaParam = "%$busca%";
$stmt->bindParam(':busca', $buscaParam, PDO::PARAM_STR);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($resultados) {
	foreach ($resultados as $row) {
		$nome = htmlspecialchars($row['req_nome'] ?? $row['int_nome']);
		$info = htmlspecialchars($row['req_cpf_cnpj'] ?? $row['dep_nome']);
		$id = htmlspecialchars($row['req_id'] ?? $row['int_id']);
		echo "<input id='campo' value='&raquo; $nome ($info)' name='campo' onclick='carregaBuscaRequerente(this.value, \"$id\");'><br>";
	}
} else {
	echo "<script>jQuery('#suggestions').hide();</script>";
}
?>