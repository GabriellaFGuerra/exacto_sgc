<?php
session_start();
$pagina_link = 'admin_modulos';
include '../mod_includes/php/connect.php';

$page = "Administradores &raquo; <a href='admin_modulos.php?pagina=admin_modulos" . $autenticacao . "'>Módulos</a>";

function showMessage($icon, $message, $closeType = 'close_janela', $back = false)
{
	$button = $back
		? "<input value=\" Ok \" type=\"button\" onclick=\"javascript:window.history.back();\">"
		: "<input value=\" Ok \" type=\"button\" class=\"$closeType\">";
	echo "<script>
			abreMask('<img src=../imagens/$icon.png> $message<br><br>$button');
		  </script>";
}

function addModulo($pdo)
{
	$mod_nome = $_POST['mod_nome'] ?? '';
	$sql = "INSERT INTO admin_modulos (mod_nome) VALUES (:mod_nome)";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':mod_nome', $mod_nome, PDO::PARAM_STR);

	if ($stmt->execute()) {
		showMessage('ok', 'Cadastro efetuado com sucesso.');
	} else {
		showMessage('x', 'Erro ao efetuar cadastro, por favor tente novamente.', 'close_janela', true);
	}
}

function editModulo($pdo)
{
	$mod_id = $_GET['mod_id'] ?? '';
	$mod_nome = $_POST['mod_nome'] ?? '';
	$sql = "UPDATE admin_modulos SET mod_nome = :mod_nome WHERE mod_id = :mod_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':mod_nome', $mod_nome, PDO::PARAM_STR);
	$stmt->bindParam(':mod_id', $mod_id, PDO::PARAM_INT);

	if ($stmt->execute()) {
		showMessage('ok', 'Dados alterados com sucesso.');
	} else {
		showMessage('x', 'Erro ao alterar dados, por favor tente novamente.', 'close_janela', true);
	}
}

function deleteModulo($pdo)
{
	$mod_id = $_GET['mod_id'] ?? '';
	$sql = "DELETE FROM admin_modulos WHERE mod_id = :mod_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':mod_id', $mod_id, PDO::PARAM_INT);

	if ($stmt->execute()) {
		showMessage('ok', 'Exclusão realizada com sucesso.');
	} else {
		showMessage('x', 'Este item não pode ser excluído pois está relacionado com alguma tabela.', 'close_janela', true);
	}
}

$action = $_GET['action'] ?? '';
switch ($action) {
	case 'adicionar':
		addModulo($pdo);
		break;
	case 'editar':
		editModulo($pdo);
		break;
	case 'excluir':
		deleteModulo($pdo);
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

	function getModulos($pdo, $primeiro_registro, $num_por_pagina)
	{
		$sql = "SELECT * FROM admin_modulos ORDER BY mod_nome ASC LIMIT :primeiro_registro, :num_por_pagina";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':primeiro_registro', $primeiro_registro, PDO::PARAM_INT);
		$stmt->bindParam(':num_por_pagina', $num_por_pagina, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt;
	}

	function renderModulosTable($stmt, $autenticacao)
	{
		if ($stmt->rowCount() > 0) {
			echo "<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Nome</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>";
			while ($modulo = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$mod_id = $modulo['mod_id'];
				$mod_nome = htmlspecialchars($modulo['mod_nome'], ENT_QUOTES, 'UTF-8');
				echo "<tr>
				  <td>{$mod_nome}</td>
				  <td align='center'>
					  <a href='admin_modulos.php?pagina=editar_admin_modulos&mod_id={$mod_id}{$autenticacao}'>
						  <img border='0' src='../imagens/icon-editar.png'>
					  </a>
					  <a onclick=\"abreMask('Deseja excluir {$mod_nome}? <input value=\\' Sim \\' type=\\'button\\' onclick=window.location.href=\\'admin_modulos.php?pagina=admin_modulos&action=excluir&mod_id={$mod_id}{$autenticacao}\\';>&nbsp;&nbsp;<input value=\\' Não \\' type=\\'button\\' class=\\'close_janela\\'>');\">
						  <img border='0' src='../imagens/icon-excluir.png'>
					  </a>
				  </td>
			  </tr>";
			}
			echo "</table>";
		} else {
			echo "<br><br><br>Não há nenhum módulo cadastrado.";
		}
	}

	if (isset($_GET['pagina']) && $_GET['pagina'] === 'admin_modulos') {
		echo "<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Módulo' type='button' onclick=\"javascript:window.location.href='admin_modulos.php?pagina=adicionar_admin_modulos" . $autenticacao . "';\" /></div>";
		$stmt = getModulos($pdo, $primeiro_registro, $num_por_pagina);
		renderModulosTable($stmt, $autenticacao);
		echo "</div>";
	}

	include '../mod_rodape/rodape.php';
	?>
</body>

</html>