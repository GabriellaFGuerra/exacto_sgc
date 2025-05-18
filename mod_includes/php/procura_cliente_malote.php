<?php
session_start();
require_once 'connect.php';

$busca = $_POST['busca'] ?? '';

if ($busca === '') {
	return;
}

$sql = "
	SELECT *
	FROM cadastro_clientes
	INNER JOIN cadastro_usuarios_clientes 
		ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
	WHERE ucl_usuario = :usuario_id
	  AND (
		  cli_nome_razao LIKE :busca 
		  OR REPLACE(REPLACE(cli_cnpj, '.', ''), '-', '') LIKE :busca
	  )
	ORDER BY cli_nome_razao ASC
";

$stmt = $pdo->prepare($sql);
$buscaParam = '%' . $busca . '%';
$stmt->bindValue(':usuario_id', $_SESSION['usuario_id'], PDO::PARAM_INT);
$stmt->bindValue(':busca', $buscaParam, PDO::PARAM_STR);
$stmt->execute();

$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($resultados) {
	foreach ($resultados as $row) {
		$nomeRazao = htmlspecialchars($row['cli_nome_razao']);
		$cnpj = htmlspecialchars($row['cli_cnpj']);
		$id = htmlspecialchars($row['cli_id']);
		echo "<input id='campo' value='&raquo; {$nomeRazao} ({$cnpj})' name='campo' onclick='carregaClienteMal(this.value,\"{$id}\");'><br>";
	}
} else {
	echo "<script>jQuery('#suggestions').hide();</script>";
}
?>