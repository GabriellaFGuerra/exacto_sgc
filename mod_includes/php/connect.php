<?php
include("ctracker.php");
error_reporting(1);
date_default_timezone_set('America/Sao_Paulo');

// Configuração segura do banco de dados
$dsn = "mysql:host=localhost;dbname=sistemae_sistema;charset=utf8mb4";
$user = "sistemae_admin";
$senha = "infomogi123";

try {
	$pdo = new PDO($dsn, $user, $senha, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	]);
} catch (PDOException $e) {
	die("Erro ao conectar ao banco: " . $e->getMessage());
}

// Configuração de caracteres
$pdo->query("SET NAMES 'utf8mb4'");
$pdo->query("SET character_set_connection=utf8mb4");
$pdo->query("SET character_set_client=utf8mb4");
$pdo->query("SET character_set_results=utf8mb4");

include("parametros.php");
?>
|