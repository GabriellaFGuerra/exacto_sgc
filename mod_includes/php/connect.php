<?php
use Dotenv\Dotenv;

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

include 'ctracker.php';

error_reporting(1);
date_default_timezone_set('America/Sao_Paulo');

// Use o DSN diretamente do .env
$dsn = $_ENV['DB_DSN'];
$user = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
	$pdo = new PDO($dsn, $user, $password, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	]);
} catch (PDOException $e) {
	die('Erro ao conectar ao banco: ' . $e->getMessage());
}

include 'parametros.php';
?>