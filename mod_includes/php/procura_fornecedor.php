<?php
require_once 'connect.php';

$busca = $_POST['busca'] ?? '';

if ($busca) {
	$sql = "
		SELECT DISTINCT f.for_id, f.for_nome_razao
		FROM cadastro_fornecedores_servicos fs
		INNER JOIN cadastro_fornecedores f ON f.for_id = fs.fse_fornecedor
		INNER JOIN cadastro_tipos_servicos ts ON ts.tps_id = fs.fse_servico
		WHERE fs.fse_servico = :busca
		ORDER BY f.for_nome_razao ASC
	";

	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':busca', $busca, PDO::PARAM_INT);
	$stmt->execute();
	$fornecedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($fornecedores) {
		echo "<option value=''>Fornecedor</option>";
		foreach ($fornecedores as $fornecedor) {
			$id = htmlspecialchars($fornecedor['for_id']);
			$nome = htmlspecialchars($fornecedor['for_nome_razao']);
			echo "<option value='$id'>$nome</option>";
		}
	} else {
		echo "<option value=''>Nenhum fornecedor cadastrado para este tipo de serviço.</option>";
	}
}
?>