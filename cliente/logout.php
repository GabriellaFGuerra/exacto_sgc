<?php
session_start();
include('../mod_includes/php/connect.php');

if ($_GET['pagina'] === 'logout') {
	session_unset(); // Remove todas as variáveis de sessão
	session_destroy(); // Destrói a sessão
	header("Location: login.php"); // Redireciona para a página de login
	exit();
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
	<title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>
	<meta name="author" content="MogiComp">
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script src="../libs/jquery-1.8.3.min.js"></script>
</head>

<body>
</body>

</html>