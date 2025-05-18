<?php
session_start();
require_once '../mod_includes/php/connect.php';

/**
 * Obtém o endereço IP do usuário.
 *
 * @return string
 */
function obterIpUsuario(): string
{
	return $_SERVER['HTTP_CLIENT_IP']
		?? $_SERVER['HTTP_X_FORWARDED_FOR']
		?? $_SERVER['REMOTE_ADDR'];
}

/**
 * Exibe uma mensagem de erro e encerra a execução.
 *
 * @param string $mensagem
 */
function exibirErro(string $mensagem): void
{
	echo "<script>
			abreMask('<img src=../imagens/x.png> $mensagem<br><br>
			<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		  </script>";
	exit;
}

$ipUsuario = obterIpUsuario();
$login = $_POST['login'] ?? '';
$senha = $_POST['senha'] ?? '';

// Consulta usuário pelo login
$sqlUsuario = '
	SELECT * FROM admin_usuarios
	INNER JOIN admin_setores ON admin_setores.set_id = admin_usuarios.usu_setor
	WHERE usu_login = :login
';
$stmtUsuario = $pdo->prepare($sqlUsuario);
$stmtUsuario->bindParam(':login', $login);
$stmtUsuario->execute();
$usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
	if (!password_verify($senha, $usuario['usu_senha'])) {
		exibirErro('Login ou senha incorreta.<br>Por favor, tente novamente.');
	}

	if ($usuario['usu_status'] == 0) {
		exibirErro('Seu usuário está desativado, contate o administrador do sistema.');
	}

	$_SESSION['exactoadm'] = hash('sha256', $login . $usuario['usu_nome']);
	$_SESSION['setor'] = $usuario['usu_setor'];
	$_SESSION['setor_nome'] = $usuario['set_nome'];
	$_SESSION['usuario_id'] = $usuario['usu_id'];

	// Registra login bem-sucedido
	$sqlLog = '
		INSERT INTO admin_log_login (log_usuario, log_hash, log_ip)
		VALUES (:usuario_id, :hash, :ip)
	';
	$stmtLog = $pdo->prepare($sqlLog);
	$stmtLog->bindParam(':usuario_id', $usuario['usu_id']);
	$stmtLog->bindParam(':hash', $_SESSION['exactoadm']);
	$stmtLog->bindParam(':ip', $ipUsuario);
	$stmtLog->execute();

	header('Location: admin.php?login=' . urlencode($login) . '&n=' . urlencode($usuario['usu_nome']));
	exit;
}

// Login falhou
$_SESSION['exactoadm'] = 'N';

// Registra tentativa de login falha
$sqlLogFalha = '
	INSERT INTO admin_log_login (log_ip, log_observacao)
	VALUES (:ip, :observacao)
';
$stmtLogFalha = $pdo->prepare($sqlLogFalha);
$stmtLogFalha->bindParam(':ip', $ipUsuario);
$observacao = "Falha login: $login";
$stmtLogFalha->bindParam(':observacao', $observacao);
$stmtLogFalha->execute();

exibirErro('Login ou senha incorreta.<br>Por favor, tente novamente.');
