<?php
session_start();
require_once 'connect.php';

// Verifica se a sessão existe antes de consultar o banco
if (!isset($_SESSION['cliente'])) {
  session_destroy();
  exibirMensagemLogin();
  exit;
}

// Parâmetros esperados
$nome = $n ?? '';
$email = $login ?? '';

$sql = "
  SELECT *
  FROM cadastro_clientes
  LEFT JOIN cliente_log_login h1 ON h1.log_usuario = cadastro_clientes.cli_id
  WHERE h1.log_id = (
    SELECT MAX(h2.log_id)
    FROM cliente_log_login h2
    WHERE h2.log_usuario = h1.log_usuario
  )
  AND cli_nome_razao = :nome
  AND cli_email = :email
  AND cli_status = 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario || $_SESSION['cliente'] !== $usuario['log_hash']) {
  session_destroy();
  exibirMensagemLogin();
  exit;
}

function exibirMensagemLogin()
{
  echo "<script>
    abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>
    <input value=\" Ok \" type=\"button\" onclick=\"window.location.href=\'login.php\'\">');
  </script>";
}
?>