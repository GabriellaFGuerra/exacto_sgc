<?php
session_start();
$pagina_link = 'admin_setores';
include '../mod_includes/php/connect.php';

$page = "Administradores &raquo; <a href='admin_setores.php?pagina=admin_setores" . ($autenticacao ?? '') . "'>Setores</a>";

function showMessage($icon, $message, $button = null)
{
	$buttonHtml = $button ?? '<input value=" Ok " type="button" class="close_janela">';
	echo "<script>
		abreMask('<img src=../imagens/{$icon}.png> {$message}<br><br>{$buttonHtml}');
	</script>";
}

function addSetor($pdo)
{
	$set_nome = $_POST['set_nome'] ?? '';
	$sql = "INSERT INTO admin_setores (set_nome) VALUES (:set_nome)";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':set_nome', $set_nome, PDO::PARAM_STR);

	if ($stmt->execute()) {
		showMessage('ok', 'Cadastro efetuado com sucesso.');
	} else {
		showMessage('x', 'Erro ao efetuar cadastro, por favor tente novamente.', '<input value=" Ok " type="button" onclick="window.history.back();">');
	}
}

function editSetor($pdo)
{
	$set_id = $_GET['set_id'] ?? '';
	$set_nome = $_POST['set_nome'] ?? '';
	$sql = "UPDATE admin_setores SET set_nome = :set_nome WHERE set_id = :set_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':set_nome', $set_nome, PDO::PARAM_STR);
	$stmt->bindParam(':set_id', $set_id, PDO::PARAM_INT);

	if ($stmt->execute()) {
		showMessage('ok', 'Dados alterados com sucesso.');
	} else {
		showMessage('x', 'Erro ao alterar dados, por favor tente novamente.', '<input value=" Ok " type="button" onclick="window.history.back();">');
	}
}

function deleteSetor($pdo)
{
	$set_id = $_GET['set_id'] ?? '';
	$sql = "DELETE FROM admin_setores WHERE set_id = :set_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':set_id', $set_id, PDO::PARAM_INT);

	if ($stmt->execute()) {
		showMessage('ok', 'Exclusão realizada com sucesso.', '<input value=" OK " type="button" class="close_janela">');
	} else {
		showMessage('x', 'Este item não pode ser excluído pois está relacionado com alguma tabela.', '<input value=" Ok " type="button" onclick="window.history.back();">');
	}
}

$action = $_GET['action'] ?? '';
switch ($action) {
	case 'adicionar':
		addSetor($pdo);
		break;
	case 'editar':
		editSetor($pdo);
		break;
	case 'excluir':
		deleteSetor($pdo);
		break;
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <title><?php echo htmlspecialchars($titulo ?? '', ENT_QUOTES, 'UTF-8'); ?></title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/funcoes.js"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
    <?php
	include '../mod_includes/php/funcoes-jquery.php';
	require_once '../mod_includes/php/verificalogin.php';
	include '../mod_topo/topo.php';
	require_once '../mod_includes/php/verificapermissao.php';

	$num_por_pagina = 10;
	$pag = $_GET['pag'] ?? 1;
	$primeiro_registro = ($pag - 1) * $num_por_pagina;

	$sql = "SELECT * FROM admin_setores ORDER BY set_nome ASC LIMIT :primeiro_registro, :num_por_pagina";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':primeiro_registro', $primeiro_registro, PDO::PARAM_INT);
	$stmt->bindParam(':num_por_pagina', $num_por_pagina, PDO::PARAM_INT);
	$stmt->execute();
	$rows = $stmt->rowCount();

	function renderSetoresTable($stmt, $autenticacao)
	{
		echo "<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
		<tr>
			<td class='titulo_tabela'>Nome</td>
			<td class='titulo_tabela' align='center'>Gerenciar</td>
		</tr>";
		while ($setor = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$editUrl = "admin_setores.php?pagina=editar_admin_setores&set_id={$setor['set_id']}{$autenticacao}";
			$deleteUrl = "admin_setores.php?pagina=admin_setores&action=excluir&set_id={$setor['set_id']}{$autenticacao}";
			$deleteMsg = "Deseja excluir {$setor['set_nome']}? <input value=' Sim ' type='button' onclick=window.location.href='{$deleteUrl}';>&nbsp;&nbsp;<input value=' Não ' type='button' class='close_janela'>";
			echo "<tr>
			<td>{$setor['set_nome']}</td>
			<td align='center'>
				<a href='{$editUrl}'><img border='0' src='../imagens/icon-editar.png'></a>
				<a onclick=\"abreMask('{$deleteMsg}');\"><img border='0' src='../imagens/icon-excluir.png'></a>
			</td>
		</tr>";
		}
		echo "</table>";
	}

	if (($_GET['pagina'] ?? '') === "admin_setores") {
		echo "<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Setor' type='button' onclick=\"window.location.href='admin_setores.php?pagina=adicionar_admin_setores" . ($autenticacao ?? '') . "';\" /></div>";

		if ($rows > 0) {
			renderSetoresTable($stmt, $autenticacao ?? '');
		} else {
			echo "<br><br><br>Não há nenhum setor cadastrado.";
		}
		echo "</div>";
	}

	include '../mod_rodape/rodape.php';
	?>
</body>

</html>