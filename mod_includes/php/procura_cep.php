<?php
include('connect.php');

$cep = $_POST['cep'] ?? '';
$up = $_POST['up'] ?? '';

$sqlprocura = "SELECT * FROM end_enderecos
               LEFT JOIN end_bairros ON end_bairros.bai_id = end_enderecos.end_bairro
               LEFT JOIN end_municipios ON end_municipios.mun_id = end_bairros.bai_municipio
               LEFT JOIN end_uf ON end_uf.uf_id = end_municipios.mun_uf
               WHERE end_cep = :cep";

$stmt = $pdo->prepare($sqlprocura);
$stmt->bindParam(':cep', $cep, PDO::PARAM_STR);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($up === 'uf') {
	if ($resultados) {
		foreach ($resultados as $row) {
			echo "<option value='" . htmlspecialchars($row['uf_id']) . "' selected>" . htmlspecialchars($row['uf_sigla']) . "</option>";
		}
	} else {
		echo "<option value=''>UF</option>";
		$stmt = $pdo->query("SELECT * FROM end_uf ORDER BY uf_sigla");
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			echo "<option value='" . htmlspecialchars($row['uf_id']) . "'>" . htmlspecialchars($row['uf_sigla']) . "</option>";
		}
	}
}

if ($up === 'municipio') {
	if ($resultados) {
		foreach ($resultados as $row) {
			echo "<option value='" . htmlspecialchars($row['mun_id']) . "' selected>" . htmlspecialchars($row['mun_nome']) . "</option>";
		}
	} else {
		echo "<option value=''>Municípios</option>";
	}
}

if ($up === 'bairro') {
	if ($resultados) {
		foreach ($resultados as $row) {
			echo htmlspecialchars($row['bai_nome']);
		}
	} else {
		echo "";
	}
}

if ($up === 'endereco') {
	if ($resultados) {
		foreach ($resultados as $row) {
			echo htmlspecialchars($row['end_endereco']);
		}
	} else {
		echo "";
	}
}
?>