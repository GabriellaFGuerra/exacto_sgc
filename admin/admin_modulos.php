<?php
session_start();

require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';
include '../mod_includes/php/funcoes-jquery.php';
include "../mod_topo/topo.php";

const ITEMS_PER_PAGE = 10;

$pageTitle = 'Administradores &raquo; <a href="admin_modulos.php?pagina=admin_modulos' . htmlspecialchars($autenticacao ?? '', ENT_QUOTES, 'UTF-8') . '">Módulos</a>';

function showMessage(string $icon, string $message, string $button = "<input value=' Ok ' type='button' class='close_janela'>"): void
{
    echo "<script>
        abreMask('<img src=../imagens/{$icon}.png> {$message}<br><br>{$button}');
    </script>";
}

function getPost(string $key): string
{
    return trim($_POST[$key] ?? '');
}

function getInt(string $key): ?int
{
    return isset($_GET[$key]) ? (int) $_GET[$key] : null;
}

function handleAddModule(PDO $pdo, string $mod_nome): void
{
    $stmt = $pdo->prepare("INSERT INTO admin_modulos (mod_nome) VALUES (:mod_nome)");
    $stmt->bindParam(':mod_nome', $mod_nome, PDO::PARAM_STR);

    if ($stmt->execute()) {
        showMessage('ok', 'Cadastro efetuado com sucesso.');
    } else {
        showMessage('x', 'Erro ao efetuar cadastro, por favor tente novamente.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
    }
}

function handleEditModule(PDO $pdo, int $mod_id, string $mod_nome): void
{
    $stmt = $pdo->prepare("UPDATE admin_modulos SET mod_nome = :mod_nome WHERE mod_id = :mod_id");
    $stmt->bindParam(':mod_nome', $mod_nome, PDO::PARAM_STR);
    $stmt->bindParam(':mod_id', $mod_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        showMessage('ok', 'Dados alterados com sucesso.');
    } else {
        showMessage('x', 'Erro ao alterar dados, por favor tente novamente.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
    }
}

function handleDeleteModule(PDO $pdo, int $mod_id): void
{
    $stmt = $pdo->prepare("DELETE FROM admin_modulos WHERE mod_id = :mod_id");
    $stmt->bindParam(':mod_id', $mod_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        showMessage('ok', 'Exclusão realizada com sucesso.', "<input value=' OK ' type='button' class='close_janela'>");
    } else {
        showMessage('x', 'Este item não pode ser excluído pois está relacionado com alguma tabela.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
    }
}

function getPaginationParams(): array
{
    $page = isset($_GET['pag']) && is_numeric($_GET['pag']) ? (int) $_GET['pag'] : 1;
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    return [$offset, ITEMS_PER_PAGE];
}

function fetchModules(PDO $pdo, int $offset, int $limit): PDOStatement
{
    $stmt = $pdo->prepare("SELECT * FROM admin_modulos ORDER BY mod_nome ASC LIMIT :offset, :limit");
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt;
}

// Main logic
$action = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$mod_id = getInt('mod_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mod_nome = getPost('mod_nome');

    if ($action === "adicionar") {
        handleAddModule($pdo, $mod_nome);
    }

    if ($action === 'editar' && $mod_id) {
        handleEditModule($pdo, $mod_id, $mod_nome);
    }
}

if ($action === 'excluir' && $mod_id) {
    handleDeleteModule($pdo, $mod_id);
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <title>
        <?php echo htmlspecialchars($titulo ?? 'Módulos', ENT_QUOTES, 'UTF-8'); ?>
    </title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include "../css/style.php"; ?>
    <script src="../mod_includes/js/funcoes.js"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
    <?php if ($pagina === "admin_modulos"): ?>
    <div class="centro">
        <div class="titulo"><?php echo $pageTitle; ?></div>
        <div id="botoes">
            <input value="Novo Módulo" type="button"
                onclick="window.location.href='admin_modulos.php?pagina=adicionar_admin_modulos<?php echo htmlspecialchars($autenticacao ?? '', ENT_QUOTES, 'UTF-8'); ?>';" />
        </div>
        <?php
            list($offset, $limit) = getPaginationParams();
            $stmt = fetchModules($pdo, $offset, $limit);
            $rows = $stmt->rowCount();
            ?>
        <?php if ($rows > 0): ?>
        <table align="center" width="100%" border="0" cellspacing="0" cellpadding="10" class="bordatabela">
            <tr>
                <td class="titulo_tabela">Nome</td>
                <td class="titulo_tabela" align="center">Gerenciar</td>
            </tr>
            <?php while ($modulo = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($modulo['mod_nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td align="center">
                    <a
                        href="admin_modulos.php?pagina=editar_admin_modulos&mod_id=<?php echo $modulo['mod_id'] . htmlspecialchars($autenticacao ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <img border="0" src="../imagens/icon-editar.png" alt="Editar">
                    </a>
                    <a href="javascript:void(0);"
                        onclick="abreMask('Deseja excluir <?php echo htmlspecialchars(addslashes($modulo['mod_nome']), ENT_QUOTES, 'UTF-8'); ?>? <input value=\' Sim \' type=\'button\' onclick=window.location.href=\'admin_modulos.php?pagina=admin_modulos&action=excluir&mod_id=<?php echo $modulo['mod_id'] . htmlspecialchars($autenticacao ?? '', ENT_QUOTES, 'UTF-8'); ?>\';>&nbsp;&nbsp;<input value=\' Não \' type=\'button\' class=\'close_janela\'>');">
                        <img border="0" src="../imagens/icon-excluir.png" alt="Excluir">
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?>
        <br><br><br>Não há nenhum módulo cadastrado.
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>