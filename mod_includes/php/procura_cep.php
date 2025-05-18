<?php
require_once 'connect.php';

$cep = $_POST['cep'] ?? '';
$up = $_POST['up'] ?? '';

$sql = "
	SELECT * FROM end_enderecos
	LEFT JOIN end_bairros ON end_bairros.bai_id = end_enderecos.end_bairro
	LEFT JOIN end_municipios ON end_municipios.mun_id = end_bairros.bai_municipio
	LEFT JOIN end_uf ON end_uf.uf_id = end_municipios.mun_uf
	WHERE end_cep = :cep
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cep', $cep, PDO::PARAM_STR);
$stmt->execute();
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

switch ($up) {
	case 'uf':
		if ($results) {
			$row = $results[0];
			echo "<option value='" . htmlspecialchars($row['uf_id']) . "' selected>" . htmlspecialchars($row['uf_sigla']) . "</option>";
		} else {
			echo "<option value=''>UF</option>";
			$stmt = $pdo->query("SELECT * FROM end_uf ORDER BY uf_sigla");
			foreach ($stmt as $row) {
				echo "<option value='" . htmlspecialchars($row['uf_id']) . "'>" . htmlspecialchars($row['uf_sigla']) . "</option>";
			}
		}
		break;

	case 'municipio':
		if ($results) {
			$row = $results[0];
			echo "<option value='" . htmlspecialchars($row['mun_id']) . "' selected>" . htmlspecialchars($row['mun_nome']) . "</option>";
		} else {
			echo "<option value=''>Municípios</option>";
		}
		break;

	case 'bairro':
		if ($results) {
			echo htmlspecialchars($results[0]['bai_nome']);
		}
		break;

	case 'endereco':
		if ($results) {
			echo htmlspecialchars($results[0]['end_endereco']);
		}
		break;
}
?>