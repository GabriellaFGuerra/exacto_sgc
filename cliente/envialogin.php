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

$ipUsuario = obterIpUsuario();

// Captura e sanitiza a entrada do usuário
$login = trim($_POST['login'] ?? '');
$senha = trim($_POST['senha'] ?? '');

// Consulta usuário
$sql = 'SELECT * FROM cadastro_clientes WHERE cli_email = :login AND cli_senha = MD5(:senha)';
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':login', $login, PDO::PARAM_STR);
$stmt->bindParam(':senha', $senha, PDO::PARAM_STR);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

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

if ($usuario) {
	if ($usuario['cli_status'] == 0) {
		echo "<script>
			abreMask('<img src=../imagens/x.png> Seu usuário está desativado, por favor contate o administrador do sistema.<br><br><input value=\"Ok\" type=\"button\" onclick=\"window.history.back();\">');
		</script>";
		exit;
	}

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

echo "<script>
	abreMask('<img src=../imagens/x.png> Login ou senha incorreta.<br>Por favor tente novamente.<br><br><input value=\"Ok\" type=\"button\" onclick=\"window.history.back();\">');
</script>";