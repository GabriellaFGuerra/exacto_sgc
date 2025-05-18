<?php
require_once 'connect.php';

$uf = $_POST['uf'] ?? '';

$stmt = $pdo->prepare('SELECT mun_id, mun_nome FROM end_municipios WHERE mun_uf = :uf');
$stmt->bindValue(':uf', $uf, PDO::PARAM_STR);
$stmt->execute();
$municipios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($municipios) {
	foreach ($municipios as $municipio) {
		echo "<option value='" . htmlspecialchars($municipio['mun_id']) . "'>" . htmlspecialchars($municipio['mun_nome']) . "</option>";
	}
} else {
	echo "<option value=''>Selecione UF</option>";
}
?>