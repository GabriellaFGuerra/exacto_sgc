<?php
include('connect.php');

$uf = $_POST['uf'] ?? '';

$sqlprocura = "SELECT * FROM end_municipios WHERE mun_uf = :uf";
$stmt = $pdo->prepare($sqlprocura);
$stmt->bindParam(':uf', $uf, PDO::PARAM_STR);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($resultados) {
	foreach ($resultados as $row) {
		echo "<option value='" . htmlspecialchars($row['mun_id']) . "'>" . htmlspecialchars($row['mun_nome']) . "</option>";
	}
} else {
	echo "<option value=''>Selecione UF</option>";
}
?>