<?php
include('connect.php');

$busca = str_replace([".", "-"], "", $_POST['busca'] ?? '');
$tipo_requerente = $_POST['tipo_requerente'] ?? '';

if (!empty($busca)) {
	if ($tipo_requerente === "Externo") {
		$sqlprocura = "SELECT * FROM requerente 
                       WHERE req_nome LIKE :busca 
                       OR REPLACE(REPLACE(req_cpf_cnpj, '.', ''), '-', '') LIKE :busca
                       ORDER BY req_nome ASC";
	} elseif ($tipo_requerente === "Interno") {
		$sqlprocura = "SELECT * FROM usuarios_internos 
                       LEFT JOIN departamentos ON departamentos.dep_id = usuarios_internos.int_departamento
                       WHERE int_nome LIKE :busca
                       ORDER BY int_nome ASC";
	}

	if (isset($sqlprocura)) {
		$stmt = $pdo->prepare($sqlprocura);
		$busca_param = '%' . $busca . '%';
		$stmt->bindParam(':busca', $busca_param, PDO::PARAM_STR);
		$stmt->execute();
		$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if ($resultados) {
			foreach ($resultados as $row) {
				echo "<input id='campo' value='&raquo; " . htmlspecialchars($row['req_nome'] ?? $row['int_nome']) . " (" . htmlspecialchars($row['req_cpf_cnpj'] ?? $row['dep_nome']) . ")' 
                      name='campo' onclick='carregaBuscaRequerente(this.value,\"" . htmlspecialchars($row['req_id'] ?? $row['int_id']) . "\");'><br>";
			}
		} else {
			echo "<script> jQuery('#suggestions').hide();</script>";
		}
	}
}
?>