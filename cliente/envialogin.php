<?php
session_start();
require_once '../mod_includes/php/connect.php';

// Função para obter o IP do usuário
function obterIpUsuario(): string
{
	return $_SERVER['HTTP_CLIENT_IP']
		?? $_SERVER['HTTP_X_FORWARDED_FOR']
		?? $_SERVER['REMOTE_ADDR']
		?? 'UNKNOWN';
}

function registrarLogLogin($pdo, $cliId, $hash, $ip, $observacao = null)
{
	if ($cliId && $hash) {
		$sql = 'INSERT INTO cliente_log_login (log_usuario, log_hash, log_ip) VALUES (:cli_id, :log_hash, :ip)';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':cli_id', $cliId, PDO::PARAM_INT);
		$stmt->bindParam(':log_hash', $hash, PDO::PARAM_STR);
		$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
		$stmt->execute();
	} else {
		$sql = 'INSERT INTO cliente_log_login (log_ip, log_observacao) VALUES (:ip, :log_observacao)';
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':ip', $ip, PDO::PARAM_STR);
		$stmt->bindParam(':log_observacao', $observacao, PDO::PARAM_STR);
		$stmt->execute();
	}
}

function mostrarAlerta($mensagem)
{
	echo "<script>
        alert('$mensagem');
        window.history.back();
    </script>";
	exit;
}

$ipUsuario = obterIpUsuario();
$login = trim($_POST['login'] ?? '');
$senha = trim($_POST['senha'] ?? '');

// Busca usuário pelo e-mail
$sql = 'SELECT * FROM cadastro_clientes WHERE cli_email = :login LIMIT 1';
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':login', $login, PDO::PARAM_STR);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
	$senhaHash = $usuario['cli_senha'];

	// Primeiro tenta com password_verify (novo hash)
	if (password_verify($senha, $senhaHash)) {
		// Se o hash precisar ser atualizado (ex: algoritmo novo)
		if (password_needs_rehash($senhaHash, PASSWORD_DEFAULT)) {
			$novoHash = password_hash($senha, PASSWORD_DEFAULT);
			$stmtUpdate = $pdo->prepare('UPDATE cadastro_clientes SET cli_senha = :nova WHERE cli_id = :id');
			$stmtUpdate->bindParam(':nova', $novoHash, PDO::PARAM_STR);
			$stmtUpdate->bindParam(':id', $usuario['cli_id'], PDO::PARAM_INT);
			$stmtUpdate->execute();
		}
	}
	// Se não, tenta com o hash antigo (MD5)
	elseif ($senhaHash === md5($senha)) {
		// Atualiza para o novo hash seguro
		$novoHash = password_hash($senha, PASSWORD_DEFAULT);
		$stmtUpdate = $pdo->prepare('UPDATE cadastro_clientes SET cli_senha = :nova WHERE cli_id = :id');
		$stmtUpdate->bindParam(':nova', $novoHash, PDO::PARAM_STR);
		$stmtUpdate->bindParam(':id', $usuario['cli_id'], PDO::PARAM_INT);
		$stmtUpdate->execute();
	}
	// Senha incorreta
	else {
		$_SESSION['cliente'] = 'N';
		$observacao = "Falha login: $login";
		registrarLogLogin($pdo, null, null, $ipUsuario, $observacao);
		mostrarAlerta('Login ou senha incorreta. Por favor tente novamente.');
	}

	// Usuário desativado
	if ($usuario['cli_status'] == 0) {
		mostrarAlerta('Seu usuário está desativado, por favor contate o administrador do sistema.');
	}

	// Login OK
	$_SESSION['cliente'] = $login . md5($usuario['cli_nome_razao']);
	$_SESSION['cliente_id'] = $usuario['cli_id'];
	registrarLogLogin($pdo, $usuario['cli_id'], $_SESSION['cliente'], $ipUsuario);

	echo "<script>
        window.location.href = 'admin.php?login=" . urlencode($login) . "&n=" . urlencode($usuario['cli_nome_razao']) . "';
    </script>";
	exit;
}

// Login inválido
$_SESSION['cliente'] = 'N';
$observacao = "Falha login: $login";
registrarLogLogin($pdo, null, null, $ipUsuario, $observacao);
mostrarAlerta('Login ou senha incorreta. Por favor tente novamente.');