<?php
session_start();
require_once '../mod_includes/php/connect.php';

// Função para obter IP real do usuário
function getUserIp(): string
{
	foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
		if (!empty($_SERVER[$key])) {
			return $_SERVER[$key];
		}
	}
	return 'UNKNOWN';
}

// Função para registrar log de login
function logLogin(PDO $pdo, ?int $userId, string $hash, string $ip, string $obs = null): void
{
	$sql = "INSERT INTO admin_log_login (log_usuario, log_hash, log_ip, log_observacao)
			VALUES (:userId, :hash, :ip, :obs)";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':userId', $userId, $userId ? PDO::PARAM_INT : PDO::PARAM_NULL);
	$stmt->bindValue(':hash', $hash);
	$stmt->bindValue(':ip', $ip);
	$stmt->bindValue(':obs', $obs);
	$stmt->execute();
}

// Recebe e valida dados
$login = trim($_POST['login'] ?? '');
$senha = $_POST['senha'] ?? '';
$ip = getUserIp();

if ($login === '' || $senha === '') {
	$_SESSION['login_erro'] = 'Preencha todos os campos.';
	$_SESSION['exactoadm'] = 'N';
	logLogin($pdo, null, '', $ip, "Campos vazios: $login");
	header('Location: login.php');
	exit;
}

// Busca usuário
$sql = "SELECT u.*, s.set_nome FROM admin_usuarios u
		JOIN admin_setores s ON s.set_id = u.usu_setor
		WHERE u.usu_login = :login LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':login', $login);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($senha, $user['usu_senha'])) {
	$_SESSION['login_erro'] = 'Login ou senha incorreta.<br>Por favor, tente novamente.';
	$_SESSION['exactoadm'] = 'N';
	logLogin($pdo, $user['usu_id'] ?? null, '', $ip, "Falha login: $login");
	header('Location: login.php');
	exit;
}

if ((int) $user['usu_status'] === 0) {
	$_SESSION['login_erro'] = 'Seu usuário está desativado, contate o administrador do sistema.';
	$_SESSION['exactoadm'] = 'N';
	logLogin($pdo, $user['usu_id'], '', $ip, "Usuário desativado: $login");
	header('Location: login.php');
	exit;
}

// Login OK
// Não use session_id() ou dados previsíveis no hash de sessão
$_SESSION['exactoadm'] = 'S';
$_SESSION['usu_id'] = $user['usu_id'];
$_SESSION['usu_nome'] = $user['usu_nome'];
$_SESSION['usu_login'] = $user['usu_login'];
$_SESSION['setor'] = $user['usu_setor'];
$_SESSION['setor_nome'] = $user['set_nome'];

logLogin($pdo, $user['usu_id'], '', $ip, "Login OK");

header('Location: admin.php?login=' . urlencode($login) . '&n=' . urlencode($user['usu_nome']));
exit;