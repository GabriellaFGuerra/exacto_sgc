<?php
session_start();
include('../mod_includes/php/connect.php');

// Função para obter o IP do usuário
function getIp()
{
	return $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

$ip = getIp();

// Capturar e limpar entrada do usuário
$login = $_POST['login'] ?? '';
$senha = $_POST['senha'] ?? '';

$sql = "SELECT * FROM cadastro_clientes WHERE cli_email = :login AND cli_senha = MD5(:senha)";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':login', $login, PDO::PARAM_STR);
$stmt->bindParam(':senha', $senha, PDO::PARAM_STR);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
	if ($usuario['cli_status'] == 0) {
		echo "<SCRIPT>
                abreMask('<img src=../imagens/x.png> Seu usuário está desativado, por favor contate o administrador do sistema.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
              </SCRIPT>";
	} else {
		$_SESSION['cliente'] = $login . md5($usuario['cli_nome_razao']);
		$_SESSION['cliente_id'] = $usuario['cli_id'];

		$sql_log = "INSERT INTO cliente_log_login (log_usuario, log_hash, log_ip) VALUES (:cli_id, :log_hash, :ip)";
		$stmt_log = $pdo->prepare($sql_log);
		$stmt_log->bindParam(':cli_id', $usuario['cli_id'], PDO::PARAM_INT);
		$stmt_log->bindParam(':log_hash', $_SESSION['cliente'], PDO::PARAM_STR);
		$stmt_log->bindParam(':ip', $ip, PDO::PARAM_STR);
		$stmt_log->execute();

		echo "<script>window.location.href = 'admin.php?login=" . urlencode($login) . "&n=" . urlencode($usuario['cli_nome_razao']) . "';</script>";
	}
} else {
	$_SESSION['cliente'] = 'N';
	$sql_log = "INSERT INTO cliente_log_login (log_ip, log_observacao) VALUES (:ip, :log_observacao)";
	$stmt_log = $pdo->prepare($sql_log);
	$stmt_log->bindParam(':ip', $ip, PDO::PARAM_STR);
	$log_observacao = "Falha login: $login | $senha";
	$stmt_log->bindParam(':log_observacao', $log_observacao, PDO::PARAM_STR);
	$stmt_log->execute();

	echo "<SCRIPT>
            abreMask('<img src=../imagens/x.png> Login ou senha incorreta.<br>Por favor tente novamente.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
          </SCRIPT>";
}
?>