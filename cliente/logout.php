<?php
session_start();
require_once '../mod_includes/php/connect.php';

// Verifica se o parâmetro 'pagina' é igual a 'logout'
if ($_GET['pagina'] === 'logout') {
	session_unset(); // Remove todas as variáveis de sessão
	session_destroy(); // Destroi a sessão
	header('Location: login.php'); // Redireciona para a página de login
	exit;
}