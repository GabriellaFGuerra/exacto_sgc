<?php
session_start();
$pagina_link = 'admin_usuarios';
include('../mod_includes/php/connect.php');
?>
<!DOCTYPE html
	PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php echo $titulo; ?></title>
	<meta name="author" content="MogiComp">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
	<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
	<!-- TOOLBAR -->
	<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
	<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
	<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
	<!-- TOOLBAR -->
</head>

<body>
	<?php
	include('../mod_includes/php/funcoes-jquery.php');
	require_once('../mod_includes/php/verificalogin.php');
	include("../mod_topo/topo.php");
	require_once('../mod_includes/php/verificapermissao.php');

	$page = "Administradores &raquo; <a href='admin_usuarios.php?pagina=admin_usuarios" . $autenticacao . "'>Usuários</a>";

	// Adicionar usuário
	if ($_GET['action'] === "adicionar") {
		$usu_setor = $_POST['usu_setor'] ?? '';
		$usu_nome = $_POST['usu_nome'] ?? '';
		$usu_email = $_POST['usu_email'] ?? '';
		$usu_login = $_POST['usu_login'] ?? '';
		$usu_senha = password_hash($_POST['usu_senha'] ?? '', PASSWORD_DEFAULT);
		$usu_status = $_POST['usu_status'] ?? '';
		$usu_notificacao = $_POST['usu_notificacao'] ?? '';

		$sql = "INSERT INTO admin_usuarios (usu_setor, usu_nome, usu_email, usu_login, usu_senha, usu_status, usu_notificacao) 
            VALUES (:usu_setor, :usu_nome, :usu_email, :usu_login, :usu_senha, :usu_status, :usu_notificacao)";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':usu_setor', $usu_setor);
		$stmt->bindParam(':usu_nome', $usu_nome);
		$stmt->bindParam(':usu_email', $usu_email);
		$stmt->bindParam(':usu_login', $usu_login);
		$stmt->bindParam(':usu_senha', $usu_senha);
		$stmt->bindParam(':usu_status', $usu_status);
		$stmt->bindParam(':usu_notificacao', $usu_notificacao);

		if ($stmt->execute()) {
			echo "<script>
                abreMask('<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br>
                <input value=\' Ok \' type=\'button\' class=\'close_janela\'>');
              </script>";
		} else {
			echo "<script>
                abreMask('<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>
                <input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
              </script>";
		}
	}

	// Editar usuário
	if ($_GET['action'] === 'editar') {
		$usu_id = $_GET['usu_id'] ?? '';
		$usu_setor = $_POST['usu_setor'] ?? '';
		$usu_nome = $_POST['usu_nome'] ?? '';
		$usu_email = $_POST['usu_email'] ?? '';
		$usu_login = $_POST['usu_login'] ?? '';
		$usu_status = $_POST['usu_status'] ?? '';
		$usu_notificacao = $_POST['usu_notificacao'] ?? '';

		// Buscar a senha existente
		$sql = "SELECT usu_senha FROM admin_usuarios WHERE usu_id = :usu_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':usu_id', $usu_id);
		$stmt->execute();
		$usuario = $stmt->fetch();

		$usu_senha = !empty($_POST['usu_senha']) ? password_hash($_POST['usu_senha'], PASSWORD_DEFAULT) : $usuario['usu_senha'];

		$sql = "UPDATE admin_usuarios SET 
                usu_setor = :usu_setor,
                usu_nome = :usu_nome,
                usu_email = :usu_email,
                usu_login = :usu_login,
                usu_senha = :usu_senha,
                usu_status = :usu_status,
                usu_notificacao = :usu_notificacao
            WHERE usu_id = :usu_id";

		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':usu_setor', $usu_setor);
		$stmt->bindParam(':usu_nome', $usu_nome);
		$stmt->bindParam(':usu_email', $usu_email);
		$stmt->bindParam(':usu_login', $usu_login);
		$stmt->bindParam(':usu_senha', $usu_senha);
		$stmt->bindParam(':usu_status', $usu_status);
		$stmt->bindParam(':usu_notificacao', $usu_notificacao);
		$stmt->bindParam(':usu_id', $usu_id);

		if ($stmt->execute()) {
			echo "<script>
                abreMask('<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br>
                <input value=\' Ok \' type=\'button\' class=\'close_janela\'>');
              </script>";
		} else {
			echo "<script>
                abreMask('<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>
                <input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
              </script>";
		}
	}

	// Excluir usuário
	if ($_GET['action'] === 'excluir') {
		$usu_id = $_GET['usu_id'] ?? '';

		$sql = "DELETE FROM admin_usuarios WHERE usu_id = :usu_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':usu_id', $usu_id);

		if ($stmt->execute()) {
			echo "<script>
                abreMask('<img src=../imagens/ok.png> Exclusão realizada com sucesso.<br><br>
                <input value=\' OK \' type=\'button\' class=\'close_janela\'>');
              </script>";
		} else {
			echo "<script>
                abreMask('<img src=../imagens/x.png> Este usuário não pode ser excluído pois está relacionado com algum cliente.<br><br>
                <input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
              </script>";
		}
	}

	// Ativar usuário
	if ($_GET['action'] === 'ativar') {
		$usu_id = $_GET['usu_id'] ?? '';

		$sql = "UPDATE admin_usuarios SET usu_status = 1 WHERE usu_id = :usu_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			echo "<script>
                abreMask('<img src=../imagens/ok.png> Ativação realizada com sucesso.<br><br>
                <input value=\' OK \' type=\'button\' class=\'close_janela\'>');
              </script>";
		} else {
			echo "<script>
                abreMask('<img src=../imagens/x.png> Erro ao ativar usuário, tente novamente.<br><br>
                <input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
              </script>";
		}
	}

	// Desativar usuário
	if ($_GET['action'] === 'desativar') {
		$usu_id = $_GET['usu_id'] ?? '';

		$sql = "UPDATE admin_usuarios SET usu_status = 0 WHERE usu_id = :usu_id";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);

		if ($stmt->execute()) {
			echo "<script>
                abreMask('<img src=../imagens/ok.png> Desativação realizada com sucesso.<br><br>
                <input value=\' OK \' type=\'button\' class=\'close_janela\'>');
              </script>";
		} else {
			echo "<script>
                abreMask('<img src=../imagens/x.png> Erro ao desativar usuário, tente novamente.<br><br>
                <input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
              </script>";
		}
	}

	// Paginação
	$num_por_pagina = 10;
	$pag = $_GET['pag'] ?? 1;
	$primeiro_registro = ($pag - 1) * $num_por_pagina;

	$sql = "SELECT * FROM admin_usuarios 
        LEFT JOIN admin_setores ON admin_setores.set_id = admin_usuarios.usu_setor
        ORDER BY usu_nome ASC
        LIMIT :primeiro_registro, :num_por_pagina";

	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':primeiro_registro', $primeiro_registro, PDO::PARAM_INT);
	$stmt->bindParam(':num_por_pagina', $num_por_pagina, PDO::PARAM_INT);
	$stmt->execute();
	$rows = $stmt->rowCount();

	// Total de usuários
	if ($pagina === "admin_usuarios") {
		echo "<div class='centro'>
            <div class='titulo'> $page </div>
            <div id='botoes'><input value='Novo Usuário' type='button' onclick=javascript:window.location.href='admin_usuarios.php?pagina=adicionar_admin_usuarios" . $autenticacao . "'; /></div>";

		if ($rows > 0) {
			echo "<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
                <tr>
                    <td class='titulo_tabela'>Nome</td>
                    <td class='titulo_tabela'>Email</td>
                    <td class='titulo_tabela'>Setor</td>
                    <td class='titulo_tabela'>Login</td>
                    <td class='titulo_tabela' align='center'>Status</td>
                    <td class='titulo_tabela' align='center'>Recebe notificação?</td>
                    <td class='titulo_tabela' align='center'>Gerenciar</td>
                </tr>";

			$c = 0;
			while ($usuario = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$usu_id = $usuario['usu_id'];
				$set_nome = $usuario['set_nome'];
				$usu_nome = $usuario['usu_nome'];
				$usu_email = $usuario['usu_email'];
				$usu_login = $usuario['usu_login'];
				$usu_status = $usuario['usu_status'];
				$usu_notificacao = $usuario['usu_notificacao'];

				$c1 = ($c++ % 2 === 0) ? "linhaimpar" : "linhapar";

				echo "<script type='text/javascript'>
                    jQuery(document).ready(function($) {
                        $('.toolbar-icons a').on('click', function(event) {
                            $(this).click();
                        });
                        $('#normal-button-$usu_id').toolbar({content: '#user-options-$usu_id', position: 'top', hideOnClick: true});
                    });
                  </script>
                  <div id='user-options-$usu_id' class='toolbar-icons' style='display: none;'>";

				echo ($usu_status == 1) ?
					"<a href='admin_usuarios.php?pagina=admin_usuarios&action=desativar&usu_id=$usu_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>" :
					"<a href='admin_usuarios.php?pagina=admin_usuarios&action=ativar&usu_id=$usu_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";

				echo "<a href='admin_usuarios.php?pagina=editar_admin_usuarios&usu_id=$usu_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
                  <a onclick=\"
                      abreMask(
                          'Deseja realmente excluir o usuário <b>$usu_nome</b>?<br><br>'+
                          '<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'admin_usuarios.php?pagina=admin_usuarios&action=excluir&usu_id=$usu_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
                          '<input value=\' Não \' type=\'button\' class=\'close_janela\'>');
                      \">
                      <img border='0' src='../imagens/icon-excluir.png'></i>
                  </a>
                  </div>";

				echo "<tr class='$c1'>
                    <td>$usu_nome</td>
                    <td>$usu_email</td>
                    <td>$set_nome</td>
                    <td>$usu_login</td>
                    <td align=center>" . ($usu_status == 1 ? "<img border='0' src='../imagens/icon-ativo.png' width='15' height='15'>" : "<img border='0' src='../imagens/icon-inativo.png' width='15' height='15'>") . "</td>
                    <td align=center>" . ($usu_notificacao == 1 ? "<img border='0' src='../imagens/ok.png' width='15' height='15'>" : "<img border='0' src='../imagens/x.png' width='15' height='15'>") . "</td>
                    <td align=center><div id='normal-button-$usu_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
                  </tr>";
			}

			echo "</table>";
			$variavel = "&pagina=admin_usuarios" . $autenticacao . "";
			include("../mod_includes/php/paginacao.php");
		} else {
			echo "<br><br><br>Não há nenhum usuário cadastrado.";
		}
		echo "<div class='titulo'>  </div></div>";
	}
	// Adicionar usuário
	if ($pagina === 'adicionar_admin_usuarios') {
		echo "<form name='form_admin_usuarios' id='form_admin_usuarios' enctype='multipart/form-data' method='post' action='admin_usuarios.php?pagina=admin_usuarios&action=adicionar$autenticacao'>
    <div class='centro'>
        <div class='titulo'> $page &raquo; Adicionar </div>
        <table align='center' cellspacing='0' width='100%'>
            <tr>
                <td align='center'>
                    <select name='usu_setor' id='usu_setor'>
                        <option value=''>Setor</option>";

		$sql = "SELECT * FROM admin_setores ORDER BY set_nome";
		$stmt = $pdo->prepare($sql);
		$stmt->execute();
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			echo "<option value='" . htmlspecialchars($row['set_id']) . "'>" . htmlspecialchars($row['set_nome']) . "</option>";
		}

		echo "
                    </select>
                    <p>
                    <div id='usu_setor_erro' class='left'>&nbsp;</div>
                    <p>
                    <input name='usu_nome' id='usu_nome' placeholder='Nome do Usuário'>
                    <p>
                    <input name='usu_email' id='usu_email' placeholder='Email'>
                    <p>
                    <div id='usu_nome_erro' class='left'>&nbsp;</div>
                    <p>
                    <input type='text' name='usu_login' id='usu_login' placeholder='Login'>
                    <input type='password' name='usu_senha' id='usu_senha' placeholder='Senha'>
                    <p>
                    <input type='radio' name='usu_status' value='1' checked> Ativo &nbsp;&nbsp;&nbsp;
                    <input type='radio' name='usu_status' value='0'> Inativo<br>
                    <p>
                    Recebe notificação via email de novos chamados realizados?<br>
                    <input type='radio' name='usu_notificacao' value='1' checked> Sim &nbsp;&nbsp;&nbsp;
                    <input type='radio' name='usu_notificacao' value='0'> Não<br>
                    <p>

                    <div class='formtitulo'>Clientes que este usuário poderá visualizar</div>
                    <input type='checkbox' class='todos' onclick='marcardesmarcar();' /> Marcar/desmarcar todos
                    <p><br>
                    <table width='100%' align='center'>
                    <tr>";

		$sql_submodulos = "SELECT * FROM cadastro_clientes ORDER BY cli_nome_razao ASC";
		$stmt = $pdo->prepare($sql_submodulos);
		$stmt->execute();
		$rows_submodulos = $stmt->rowCount();

		if ($rows_submodulos > 0) {
			$i = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$i++;
				$coluna = ($i % 3 == 0) ? "</td></tr><tr>" : "</td>";
				echo "<td align='left' width='25%'>
                      <input type='checkbox' class='marcar' name='item_check_" . htmlspecialchars($row['cli_id']) . "' id='item_check_" . htmlspecialchars($row['cli_id']) . "' value='" . htmlspecialchars($row['cli_id']) . "' > " . htmlspecialchars($row['cli_nome_razao']) . "
                  $coluna";
			}
		} else {
			echo "<tr><td>Não há clientes cadastrados.</td></tr>";
		}

		echo "</table>
                    <center>
                    <div id='erro' align='center'>&nbsp;</div>
                    <input type='submit' id='bt_admin_usuarios' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
                    <input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_usuarios.php?pagina=admin_usuarios$autenticacao'; value='Cancelar'/></center>
                </td>
            </tr>
        </table>
        <div class='titulo'> </div>
    </div>
    </form>";
	}

	// Editar usuário
	if ($pagina === 'editar_admin_usuarios') {
		$usu_id = $_GET['usu_id'] ?? '';

		$sqledit = "SELECT * FROM admin_usuarios
                LEFT JOIN admin_setores ON admin_setores.set_id = admin_usuarios.usu_setor
                WHERE usu_id = :usu_id";
		$stmt = $pdo->prepare($sqledit);
		$stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
		$stmt->execute();
		$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($usuario) {
			echo "<form name='form_admin_usuarios' id='form_admin_usuarios' enctype='multipart/form-data' method='post' action='admin_usuarios.php?pagina=admin_usuarios&action=editar&usu_id=$usu_id$autenticacao'>
        <div class='centro'>
            <div class='titulo'> $page &raquo; Editar: " . htmlspecialchars($usuario['usu_nome']) . " </div>
            <table align='center' cellspacing='0' width='100%'>
                <tr>
                    <td align='center'>
                        <input type='hidden' name='usu_id' id='usu_id' value='" . htmlspecialchars($usuario['usu_id']) . "'>
                        <select name='usu_setor' id='usu_setor'>
                            <option value='" . htmlspecialchars($usuario['usu_setor']) . "'>" . htmlspecialchars($usuario['set_nome']) . "</option>";

			$sql = "SELECT * FROM admin_setores ORDER BY set_nome";
			$stmt = $pdo->prepare($sql);
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				echo "<option value='" . htmlspecialchars($row['set_id']) . "'>" . htmlspecialchars($row['set_nome']) . "</option>";
			}

			echo "</select>
                        <p>
                        <input name='usu_nome' id='usu_nome' value='" . htmlspecialchars($usuario['usu_nome']) . "' placeholder='Nome do Usuário'>
                        <p>
                        <input name='usu_email' id='usu_email' value='" . htmlspecialchars($usuario['usu_email']) . "' placeholder='Email'>
                        <p>
                        <input type='text' name='usu_login' id='usu_login' value='" . htmlspecialchars($usuario['usu_login']) . "' placeholder='Login'>
                        <input type='password' name='usu_senha' id='usu_senha' placeholder='Senha'>
                        <p>
                        <input type='radio' name='usu_status' value='1' " . ($usuario['usu_status'] == 1 ? "checked" : "") . "> Ativo &nbsp;&nbsp;&nbsp;
                        <input type='radio' name='usu_status' value='0' " . ($usuario['usu_status'] == 0 ? "checked" : "") . "> Inativo
                        <p>
                        Recebe notificação via email de novos chamados realizados?<br>
                        <input type='radio' name='usu_notificacao' value='1' " . ($usuario['usu_notificacao'] == 1 ? "checked" : "") . "> Sim &nbsp;&nbsp;&nbsp;
                        <input type='radio' name='usu_notificacao' value='0' " . ($usuario['usu_notificacao'] == 0 ? "checked" : "") . "> Não
                        <p>
                        <div class='formtitulo'>Clientes que este usuário poderá visualizar</div>
                        <input type='checkbox' class='todos' onclick='marcardesmarcar();' /> Marcar/desmarcar todos
                        <p><br>
                        <table width='100%'>
                        <tr>";

			$sql_clientes = "SELECT * FROM cadastro_clientes WHERE cli_status = 1 AND cli_deletado = 1 ORDER BY cli_nome_razao";
			$stmt = $pdo->prepare($sql_clientes);
			$stmt->execute();
			$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

			if ($clientes) {
				$i = 0;
				foreach ($clientes as $cliente) {
					$i++;
					$coluna = ($i % 3 == 0) ? "</td></tr><tr>" : "</td>";

					$sql_itens_cad = "SELECT * FROM cadastro_usuarios_clientes WHERE ucl_usuario = :usu_id AND ucl_cliente = :cli_id";
					$stmt = $pdo->prepare($sql_itens_cad);
					$stmt->bindParam(':usu_id', $usu_id, PDO::PARAM_INT);
					$stmt->bindParam(':cli_id', $cliente['cli_id'], PDO::PARAM_INT);
					$stmt->execute();
					$tem_acesso = $stmt->rowCount() > 0;

					echo "<td align='left' width='25%'>
                          <input type='checkbox' class='marcar' name='item_check_" . htmlspecialchars($cliente['cli_id']) . "' id='item_check_" . htmlspecialchars($cliente['cli_id']) . "' value='" . htmlspecialchars($cliente['cli_id']) . "' " . ($tem_acesso ? "checked" : "") . "> " . htmlspecialchars($cliente['cli_nome_razao']) . "
                      $coluna";
				}
			} else {
				echo "<tr><td>Não há clientes.</td></tr>";
			}

			echo "</table>
                        <center>
                        <div id='erro' align='center'>&nbsp;</div>
                        <input type='submit' id='bt_admin_usuarios' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
                        <input type='button' id='botao_cancelar' onclick=javascript:window.location.href='admin_usuarios.php?pagina=admin_usuarios$autenticacao'; value='Cancelar'/></center>
                    </td>
                </tr>
            </table>
            <div class='titulo'>   </div>
        </div>
        </form>";
		}
	}

	include('../mod_rodape/rodape.php');
	?>
</body>

</html>