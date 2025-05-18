<?php
session_start();
require_once '../mod_includes/php/connect.php';

// Remove variáveis de sessão relacionadas ao login do administrador
unset($_SESSION['exactoadm'], $_SESSION['setor'], $_SESSION['setor_nome'], $_SESSION['usuario_id']);

// Fecha a sessão
session_write_close();

// Redireciona para a página de login
header('Location: login.php');
exit;
?>