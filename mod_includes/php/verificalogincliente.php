<?php
session_start();
include('connect.php');

$sqlverifica = "SELECT * FROM cadastro_clientes
                LEFT JOIN cliente_log_login h1 ON h1.log_usuario = cadastro_clientes.cli_id
                WHERE h1.log_id = (SELECT MAX(h2.log_id) FROM cliente_log_login h2 WHERE h2.log_usuario = h1.log_usuario)
                AND cli_nome_razao = :nome AND cli_email = :login AND cli_status = 1";

$stmt = $pdo->prepare($sqlverifica);
$stmt->bindParam(':nome', $n, PDO::PARAM_STR);
$stmt->bindParam(':login', $login, PDO::PARAM_STR);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
	if ($_SESSION['cliente'] !== $usuario['log_hash']) {
		session_destroy();
		echo "<SCRIPT>
                abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>
                <input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>');
              </SCRIPT>";
		exit;
	}
} else {
	session_destroy();
	echo "<SCRIPT>
            abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>
            <input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>');
          </SCRIPT>";
	exit;
}

if (!isset($_SESSION['cliente'])) {
	session_destroy();
	echo "<SCRIPT>
            abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>
            <input value=\' Ok \' type=\'button\' onclick=javascript:window.location.href=\'login.php\';>');
          </SCRIPT>";
	exit;
}
?>