<?php
include('connect.php');

$int_id = $_POST['int_id'] ?? '';
$departamento = $_POST['departamento'] ?? '';

if (!empty($departamento)) {
	// Consulta segura com `Prepared Statements`
	$sqlprocura = "SELECT * FROM usuarios_internos WHERE int_departamento = :departamento";

	if (!empty($int_id)) {
		$sqlprocura .= " AND int_id = :int_id";
	}

	$sqlprocura .= " ORDER BY int_nome ASC";

	$stmt = $pdo->prepare($sqlprocura);
	$stmt->bindParam(':departamento', $departamento, PDO::PARAM_INT);

	if (!empty($int_id)) {
		$stmt->bindParam(':int_id', $int_id, PDO::PARAM_INT);
	}

	$stmt->execute();
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($resultados) {
		echo "<option value=''>Selecione o Responsável</option>";
		foreach ($resultados as $row) {
			echo "<option value='" . htmlspecialchars($row['int_id']) . "'>" . htmlspecialchars($row['int_nome']) . "</option>";
		}
	} else {
		echo "<option value=''>Nome do Responsável</option>";
	}
}
?>