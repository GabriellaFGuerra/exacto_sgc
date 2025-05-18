<?php
session_start();
require_once 'connect.php';

$busca = $_POST['busca'] ?? '';

if ($busca) {
	$sql = "
		SELECT * FROM orcamento_gerenciar
		LEFT JOIN cadastro_tipos_servicos 
			ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
		WHERE orc_cliente = :busca
		ORDER BY orc_id DESC
	";

	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':busca', $busca, PDO::PARAM_INT);
	$stmt->execute();
	$orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($orcamentos) {
		echo "<option value=''>Selecione o orçamento caso tenha relação</option>";
		foreach ($orcamentos as $orcamento) {
			$id = htmlspecialchars($orcamento['orc_id']);
			$nome = htmlspecialchars($orcamento['tps_nome']);
			echo "<option value='$id'>$id ($nome)</option>";
		}
	} else {
		echo "<option value=''>Nenhum orçamento cadastrado para este cliente.</option>";
	}
}
?>