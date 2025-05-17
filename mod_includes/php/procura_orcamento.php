<?php
session_start();
include('connect.php');

$busca = $_POST['busca'] ?? '';

if (!empty($busca)) {
	// Consulta segura com `Prepared Statements`
	$sqlprocura = "SELECT * FROM orcamento_gerenciar
                   LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
                   WHERE orc_cliente = :busca
                   ORDER BY orc_id DESC";

	$stmt = $pdo->prepare($sqlprocura);
	$stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
	$stmt->execute();
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($resultados) {
		echo "<option value=''>Selecione o orçamento caso tenha relação</option>";
		foreach ($resultados as $row) {
			echo "<option value='" . htmlspecialchars($row['orc_id']) . "'>" . htmlspecialchars($row['orc_id']) . " (" . htmlspecialchars($row['tps_nome']) . ")</option>";
		}
	} else {
		echo "<option value=''>Nenhum orçamento cadastrado para este cliente.</option>";
	}
}
?>