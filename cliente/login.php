<?php
session_start();
include('../mod_includes/php/connect.php');
?>

<!DOCTYPE html>
<html lang="pt">

<head>
	<title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>
	<meta name="author" content="MogiComp">
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
	<?php
	include('../mod_includes/php/funcoes-jquery.php');
	include("../mod_topo_cliente/topo_login.php");
	?>

	<div class='centro'>
		<div class='titulo'>Bem-vindo ao Sistema de Gerenciamento de Orçamentos</div>
		<div id='interna'>
			<form name='form_login' id='form_login' method='post' autocomplete='off' action='envialogin.php'>
				<table align='center' cellspacing='10'>
					<tr>
						<td>
							<span class='textopeq'>Digite seu usuário e senha para acessar o sistema.</span><br>
						</td>
					</tr>
					<tr>
						<td align='center'>
							<input type='email' name='login' id='login' placeholder='Email' required size='20'>
						</td>
					</tr>
					<tr>
						<td align='center'>
							<input type='password' name='senha' id='senha' placeholder='Senha' required size='20'>
						</td>
					</tr>
					<tr>
						<td align='center' height='30' valign='bottom'>
							<input type='submit' id='bt_login' value='Entrar no Sistema' name='B1'>
						</td>
					</tr>
				</table>
			</form>
		</div>
		<div class='titulo'></div>
	</div>

	<?php include('../mod_rodape/rodape.php'); ?>
</body>

</html>