<?php
session_start();
$pagina_link = 'admin_modulos';
include('../mod_includes/php/connect.php');

$page = "Administradores &raquo; <a href='admin_modulos.php?pagina=admin_modulos" . $autenticacao . "'>Módulos</a>";

if ($_GET['action'] === "adicionar") {
	$mod_nome = $_POST['mod_nome'] ?? '';

	$sql = "INSERT INTO admin_modulos (mod_nome) VALUES (:mod_nome)";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':mod_nome', $mod_nome, PDO::PARAM_STR);

	if ($stmt->execute()) {
		echo "<SCRIPT>
                abreMask('<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br>
                <input value=\' Ok \' type=\'button\' class=\'close_janela\'>');
              </SCRIPT>";
	} else {
		echo "<SCRIPT>
                abreMask('<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>
                <input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
              </SCRIPT>";
	}
}

if ($_GET['action'] === 'editar') {
	$mod_id = $_GET['mod_id'] ?? '';
	$mod_nome = $_POST['mod_nome'] ?? '';

	$sql = "UPDATE admin_modulos SET mod_nome = :mod_nome WHERE mod_id = :mod_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':mod_nome', $mod_nome, PDO::PARAM_STR);
	$stmt->bindParam(':mod_id', $mod_id, PDO::PARAM_INT);

	if ($stmt->execute()) {
		echo "<SCRIPT>
                abreMask('<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br>
                <input value=\' Ok \' type=\'button\' class=\'close_janela\'>');
              </SCRIPT>";
	} else {
		echo "<SCRIPT>
                abreMask('<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>
                <input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
              </SCRIPT>";
	}
}

if ($_GET['action'] === 'excluir') {
	$mod_id = $_GET['mod_id'] ?? '';

	$sql = "DELETE FROM admin_modulos WHERE mod_id = :mod_id";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':mod_id', $mod_id, PDO::PARAM_INT);

	if ($stmt->execute()) {
		echo "<SCRIPT>
                abreMask('<img src=../imagens/ok.png> Exclusão realizada com sucesso.<br><br>
                <input value=\' OK \' type=\'button\' class=\'close_janela\'>');
              </SCRIPT>";
	} else {
		echo "<SCRIPT>
                abreMask('<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br>
                <input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
              </SCRIPT>";
	}
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
	<title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script src="../mod_includes/js/funcoes.js"></script>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>

	<?php
	include('../mod_includes/php/funcoes-jquery.php');
	require_once('../mod_includes/php/verificalogin.php');
	include("../mod_topo/topo.php");
	require_once('../mod_includes/php/verificapermissao.php');

	$num_por_pagina = 10;
	$pag = $_GET['pag'] ?? 1;
	$primeiro_registro = ($pag - 1) * $num_por_pagina;

	$sql = "SELECT * FROM admin_modulos ORDER BY mod_nome ASC LIMIT :primeiro_registro, :num_por_pagina";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':primeiro_registro', $primeiro_registro, PDO::PARAM_INT);
	$stmt->bindParam(':num_por_pagina', $num_por_pagina, PDO::PARAM_INT);
	$stmt->execute();
	$rows = $stmt->rowCount();

	if ($_GET['pagina'] === "admin_modulos") {
		echo "<div class='centro'>
            <div class='titulo'> $page  </div>
            <div id='botoes'><input value='Novo Módulo' type='button' onclick=javascript:window.location.href='admin_modulos.php?pagina=adicionar_admin_modulos" . $autenticacao . "'; /></div>";

		if ($rows > 0) {
			echo "<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
                <tr>
                    <td class='titulo_tabela'>Nome</td>
                    <td class='titulo_tabela' align='center'>Gerenciar</td>
                </tr>";

			while ($modulo = $stmt->fetch(PDO::FETCH_ASSOC)) {
				echo "<tr>
                      <td>{$modulo['mod_nome']}</td>
                      <td align='center'>
                          <a href='admin_modulos.php?pagina=editar_admin_modulos&mod_id={$modulo['mod_id']}{$autenticacao}'>
                              <img border='0' src='../imagens/icon-editar.png'>
                          </a>
                          <a onclick=\"abreMask('Deseja excluir {$modulo['mod_nome']}? <input value=\' Sim \' type=\'button\' onclick=window.location.href='admin_modulos.php?pagina=admin_modulos&action=excluir&mod_id={$modulo['mod_id']}{$autenticacao}';>&nbsp;&nbsp;<input value=\' Não \' type=\'button\' class=\'close_janela\'>');\">
                              <img border='0' src='../imagens/icon-excluir.png'>
                          </a>
                      </td>
                  </tr>";
			}

			echo "</table>";
		} else {
			echo "<br><br><br>Não há nenhum módulo cadastrado.";
		}
		echo "</div>";
	}

	include('../mod_rodape/rodape.php');
	?>
</body>

</html>