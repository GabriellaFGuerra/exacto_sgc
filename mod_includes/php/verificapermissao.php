<?php
session_start();
require_once 'connect.php';

// Validação de sessão
if (
  empty($_SESSION['setor']) ||
  empty($pagina_link) ||
  empty($n) ||
  empty($login)
) {
  session_destroy();
  exibirPermissaoNegada();
  exit;
}

$sql = "
  SELECT 1
  FROM admin_setores_permissoes
  INNER JOIN admin_submodulos ON admin_submodulos.sub_id = admin_setores_permissoes.sep_submodulo
  INNER JOIN admin_modulos ON admin_modulos.mod_id = admin_submodulos.sub_modulo
  INNER JOIN admin_setores ON admin_setores.set_id = admin_setores_permissoes.sep_setor
  INNER JOIN admin_usuarios ON admin_usuarios.usu_setor = admin_setores.set_id
  WHERE sep_setor = :setor
    AND sub_link = :pagina_link
    AND usu_nome = :nome
    AND usu_login = :login
    AND usu_status = 1
  LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':setor', $_SESSION['setor'], PDO::PARAM_INT);
$stmt->bindValue(':pagina_link', $pagina_link, PDO::PARAM_STR);
$stmt->bindValue(':nome', $n, PDO::PARAM_STR);
$stmt->bindValue(':login', $login, PDO::PARAM_STR);
$stmt->execute();

if ($stmt->rowCount() === 0) {
  session_destroy();
  exibirPermissaoNegada();
  exit;
}

if (
  empty($_SESSION['exactoadm']) ||
  $_SESSION['exactoadm'] !== $login . md5($n)
) {
  session_destroy();
  exibirPermissaoNegada();
  exit;
}

function exibirPermissaoNegada()
{
  echo "<script>
    abreMask('<img src=../imagens/x.png> Você não tem permissão para acessar esta área.<br>Por favor faça Login.<br><br>
    <input value=\" Ok \" type=\"button\" onclick=\"window.location.href=\'login.php\'\">');
  </script>";
}
?>