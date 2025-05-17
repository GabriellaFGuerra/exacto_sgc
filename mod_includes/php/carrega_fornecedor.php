<?php
include('connect.php');

$tipo_servico = $_POST['tipo_servico'] ?? '';

if (!empty($tipo_servico)) {
	// Consulta segura com Prepared Statements
	$sqlprocura = "SELECT cadastro_fornecedores.for_id, cadastro_fornecedores.for_nome_razao 
                   FROM cadastro_fornecedores_servicos 
                   INNER JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = cadastro_fornecedores_servicos.fse_fornecedor
                   INNER JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = cadastro_fornecedores_servicos.fse_servico
                   WHERE fse_servico = :tipo_servico 
                   GROUP BY for_id
                   ORDER BY for_nome_razao ASC";

	$stmt = $pdo->prepare($sqlprocura);
	$stmt->bindParam(':tipo_servico', $tipo_servico, PDO::PARAM_INT);
	$stmt->execute();
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($resultados) {
		echo "<option value=''>Fornecedor</option>";
		foreach ($resultados as $row) {
			echo "<option value='" . htmlspecialchars($row['for_id']) . "'>" . htmlspecialchars($row['for_nome_razao']) . "</option>";
		}
	} else {
		echo "<option value=''>Nenhum fornecedor cadastrado para este tipo de serviço.</option>";
	}
} else {
	echo "<option value=''>Selecione um tipo de serviço.</option>";
}
?>