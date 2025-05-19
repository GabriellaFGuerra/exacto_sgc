<?php
session_start();
require_once 'connect.php';

// Validação de sessão e variáveis obrigatórias
$pagina_link = isset($pagina_link) ? $pagina_link : (isset($_GET['pagina_link']) ? $_GET['pagina_link'] : '');
$nome = isset($n) ? $n : (isset($_SESSION['usu_nome']) ? $_SESSION['usu_nome'] : '');
$login = isset($login) ? $login : (isset($_SESSION['usu_login']) ? $_SESSION['usu_login'] : '');

// Permissão total para administradores autenticados
if (isset($_SESSION['exactoadm']) && $_SESSION['exactoadm'] === 'S') {
  return;
}

// Verifica se as variáveis essenciais estão presentes
if (
  empty($_SESSION['setor']) ||
  empty($pagina_link) ||
  empty($nome) ||
  empty($login)
) {
  exibirPermissaoNegada();
}

// Consulta de permissão no banco de dados
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
$stmt->bindValue(':nome', $nome, PDO::PARAM_STR);
$stmt->bindValue(':login', $login, PDO::PARAM_STR);
$stmt->execute();

// Se não encontrou permissão, nega o acesso
if ($stmt->rowCount() === 0) {
  exibirPermissaoNegada();
}

/**
 * Exibe mensagem de permissão negada e redireciona para o login.
 */
function exibirPermissaoNegada()
{
  echo "<script>
        alert('Você não tem permissão para acessar esta área. Por favor, faça login.');
        window.location.href='login.php';
    </script>";
  exit;
}
?>