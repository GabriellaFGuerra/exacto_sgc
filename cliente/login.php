<?php
session_start();
require_once '../mod_includes/php/connect.php';
?>

<!DOCTYPE html>
<html lang="pt">

<head>
	<title><?php echo isset($titulo) ? htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') : 'Login'; ?></title>
	<meta name="author" content="MogiComp">
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php require_once '../css/style.php'; ?>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
	<?php
	require_once '../mod_includes/php/funcoes-jquery.php';
	require_once '../mod_topo_cliente/topo_login.php';
	?>

	<main class="centro">
		<h1 class="titulo">Bem-vindo ao Sistema de Gerenciamento de Orçamentos</h1>
		<section id="interna">
			<form name="form_login" id="form_login" method="post" autocomplete="off" action="envialogin.php">
				<table align="center" cellspacing="10">
					<tr>
						<td>
							<span class="textopeq">Digite seu usuário e senha para acessar o sistema.</span>
						</td>
					</tr>
					<tr>
						<td align="center">
							<input type="email" name="login" id="login" placeholder="Email" required size="20">
						</td>
					</tr>
					<tr>
						<td align="center">
							<input type="password" name="senha" id="senha" placeholder="Senha" required size="20">
						</td>
					</tr>
					<tr>
						<td align="center" height="30" valign="bottom">
							<button type="submit" id="bt_login" name="B1">Entrar no Sistema</button>
						</td>
					</tr>
				</table>
			</form>
		</section>
	</main>

	<?php require_once '../mod_rodape/rodape.php'; ?>
</body>

</html>