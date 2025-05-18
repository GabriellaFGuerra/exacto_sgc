<?php
session_start();
include '../mod_includes/php/connect.php';

function getIp(): string
{
	return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];
}

$ip = getIp();
$login = $_POST['login'] ?? '';
$senha = $_POST['senha'] ?? '';

// Consulta segura com PDO
$sql = 'SELECT * FROM admin_usuarios
		INNER JOIN admin_setores ON admin_setores.set_id = admin_usuarios.usu_setor
		WHERE usu_login = :login';
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':login', $login);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
	if (!password_verify($senha, $usuario['usu_senha'])) {
		echo "<script>
				abreMask('<img src=../imagens/x.png> Login ou senha incorreta.<br>Por favor, tente novamente.<br><br>
				<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
			  </script>";
		exit;
	}

	if ($usuario['usu_status'] == 0) {
		echo "<script>
				abreMask('<img src=../imagens/x.png> Seu usuário está desativado, contate o administrador do sistema.<br><br>
				<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
			  </script>";
		exit;
	}

	$_SESSION['exactoadm'] = hash('sha256', $login . $usuario['usu_nome']);
	$_SESSION['setor'] = $usuario['usu_setor'];
	$_SESSION['setor_nome'] = $usuario['set_nome'];
	$_SESSION['usuario_id'] = $usuario['usu_id'];

	// Registro de login seguro
	$sql_log = 'INSERT INTO admin_log_login (log_usuario, log_hash, log_ip) VALUES (:usu_id, :hash, :ip)';
	$stmt_log = $pdo->prepare($sql_log);
	$stmt_log->bindParam(':usu_id', $usuario['usu_id']);
	$stmt_log->bindParam(':hash', $_SESSION['exactoadm']);
	$stmt_log->bindParam(':ip', $ip);
	$stmt_log->execute();

	header('Location: admin.php?login=' . urlencode($login) . '&n=' . urlencode($usuario['usu_nome']));
	exit;
}

$_SESSION['exactoadm'] = 'N';

// Registro de tentativa de login falha
$sql_log = 'INSERT INTO admin_log_login (log_ip, log_observacao) VALUES (:ip, :observacao)';
$stmt_log = $pdo->prepare($sql_log);
$stmt_log->bindParam(':ip', $ip);
$stmt_log->bindValue(':observacao', "Falha login: $login");
$stmt_log->execute();

echo "<script>
		abreMask('<img src=../imagens/x.png> Login ou senha incorreta.<br>Por favor, tente novamente.<br><br>
		<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
	  </script>";
