<?php
session_start();
include('connect.php');

$busca = $_POST['busca'] ?? '';

if (!empty($busca)) {
	// Consulta segura com `Prepared Statements`
	$sqlprocura = "SELECT * FROM cadastro_clientes
                   INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
                   WHERE ucl_usuario = :usuario_id
                   AND (cli_nome_razao LIKE :busca OR REPLACE(REPLACE(cli_cnpj, '.', ''), '-', '') LIKE :busca)
                   ORDER BY cli_nome_razao ASC";

	$stmt = $pdo->prepare($sqlprocura);
	$busca_param = '%' . $busca . '%';
	$stmt->bindParam(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
	$stmt->bindParam(':busca', $busca_param, PDO::PARAM_STR);
	$stmt->execute();
	$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($resultados) {
		foreach ($resultados as $row) {
			echo "<input id='campo' value='&raquo; " . htmlspecialchars($row['cli_nome_razao']) . " (" . htmlspecialchars($row['cli_cnpj']) . ")' 
                  name='campo' onclick='carregaClienteInf(this.value,\"" . htmlspecialchars($row['cli_id']) . "\");'><br>";
		}
	} else {
		echo "<script> jQuery('#suggestions').hide();</script>";
	}
}
?>