<?php
require_once 'connect.php';

$int_id = $_POST['int_id'] ?? '';
$departamento = $_POST['departamento'] ?? '';

if ($departamento) {
	$sql = 'SELECT * FROM usuarios_internos WHERE int_departamento = :departamento';
	if ($int_id) {
		$sql .= ' AND int_id = :int_id';
	}
	$sql .= ' ORDER BY int_nome ASC';

	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':departamento', $departamento, PDO::PARAM_INT);
	if ($int_id) {
		$stmt->bindValue(':int_id', $int_id, PDO::PARAM_INT);
	}
	$stmt->execute();
	$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($usuarios) {
		echo "<option value=''>Selecione o Responsável</option>";
		foreach ($usuarios as $usuario) {
			$id = htmlspecialchars($usuario['int_id']);
			$nome = htmlspecialchars($usuario['int_nome']);
			echo "<option value='$id'>$nome</option>";
		}
	} else {
		echo "<option value=''>Nome do Responsável</option>";
	}
}
?>