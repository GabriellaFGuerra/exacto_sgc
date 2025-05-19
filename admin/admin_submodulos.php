<?php
session_start();
$pagina_link = 'admin_submodulos';

// Includes essenciais (apenas PHP antes do HTML)
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Variáveis de controle
$titulo = 'Administradores - Submódulos';
$pagina = $_GET['pagina'] ?? '';
$action = $_GET['action'] ?? '';
$pag = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
$sub_id = isset($_GET['sub_id']) ? (int) $_GET['sub_id'] : 0;
$itensPorPagina = 10;
$offset = ($pag - 1) * $itensPorPagina;

// Função para exibir mensagens e redirecionar
function exibirMensagem($mensagem)
{
    $msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    echo "<script>alert('$msg'); window.location.href = 'admin_submodulos.php?pagina=admin_submodulos';</script>";
    exit;
}

// CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub_nome = trim($_POST['sub_nome'] ?? '');
    $sub_modulo = (int) ($_POST['sub_modulo'] ?? 0);

    if ($action === 'adicionar') {
        $stmt = $pdo->prepare("INSERT INTO admin_submodulos (sub_nome, sub_modulo) VALUES (:sub_nome, :sub_modulo)");
        if ($stmt->execute(['sub_nome' => $sub_nome, 'sub_modulo' => $sub_modulo])) {
            exibirMensagem('Cadastro efetuado com sucesso.');
        } else {
            exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
        }
    }

    if ($action === 'editar' && $sub_id) {
        $stmt = $pdo->prepare("UPDATE admin_submodulos SET sub_nome = :sub_nome, sub_modulo = :sub_modulo WHERE sub_id = :sub_id");
        if ($stmt->execute(['sub_nome' => $sub_nome, 'sub_modulo' => $sub_modulo, 'sub_id' => $sub_id])) {
            exibirMensagem('Dados alterados com sucesso.');
        } else {
            exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
        }
    }
}

if ($action === 'excluir' && $sub_id) {
    $stmt = $pdo->prepare("DELETE FROM admin_submodulos WHERE sub_id = :sub_id");
    if ($stmt->execute(['sub_id' => $sub_id])) {
        exibirMensagem('Exclusão realizada com sucesso.');
    } else {
        exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
    }
    exit;
}

// Função para buscar módulos para o select
function buscarModulos($pdo)
{
    $stmt = $pdo->query("SELECT mod_id, mod_nome FROM admin_modulos ORDER BY mod_nome ASC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php
    include '../css/style.php';
    require_once '../mod_includes/php/funcoes-jquery.php';
    ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
</head>

<body>
    <?php include '../mod_topo/topo.php'; ?>
    <div class="centro">
        <?php if ($pagina === "admin_submodulos" || $pagina == ''): ?>
        <div class="titulo"><?php echo $titulo; ?></div>
        <div id="botoes">
            <input value="Novo Submódulo" type="button"
                onclick="window.location.href='admin_submodulos.php?pagina=adicionar_admin_submodulos';" />
        </div>
        <?php
            $stmt = $pdo->prepare("SELECT s.*, m.mod_nome FROM admin_submodulos s INNER JOIN admin_modulos m ON m.mod_id = s.sub_modulo ORDER BY s.sub_nome ASC LIMIT :offset, :limit");
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
            $stmt->execute();
            $submodulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalRegistros = $pdo->query("SELECT COUNT(*) FROM admin_submodulos")->fetchColumn();
            ?>
        <?php if ($submodulos): ?>
        <table align="center" width="100%" border="0" cellspacing="0" cellpadding="10" class="bordatabela">
            <tr>
                <td class="titulo_tabela">Nome</td>
                <td class="titulo_tabela">Módulo</td>
                <td class="titulo_tabela" align="center">Gerenciar</td>
            </tr>
            <?php foreach ($submodulos as $sub): ?>
            <tr>
                <td><?php echo htmlspecialchars($sub['sub_nome']); ?></td>
                <td><?php echo htmlspecialchars($sub['mod_nome']); ?></td>
                <td align="center">
                    <a href="admin_submodulos.php?pagina=editar_admin_submodulos&sub_id=<?php echo $sub['sub_id']; ?>">
                        <img border="0" src="../imagens/icon-editar.png" alt="Editar">
                    </a>
                    <a href="javascript:void(0);" onclick="
                        if(confirm('Deseja excluir <?php echo addslashes(htmlspecialchars($sub['sub_nome'])); ?>?')){window.location.href='admin_submodulos.php?pagina=admin_submodulos&action=excluir&sub_id=<?php echo $sub['sub_id']; ?>';}
                    ">
                        <img border="0" src="../imagens/icon-excluir.png" alt="Excluir">
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
                $totalPaginas = ceil($totalRegistros / $itensPorPagina);
                if ($totalPaginas > 1) {
                    echo "<div class='paginacao'>";
                    for ($i = 1; $i <= $totalPaginas; $i++) {
                        $active = $i == $pag ? "style='font-weight:bold;'" : "";
                        $url = "admin_submodulos.php?pagina=admin_submodulos&pag=$i";
                        echo "<a href='$url' $active>$i</a> ";
                    }
                    echo "</div>";
                }
                ?>
        <?php else: ?>
        <br><br><br>Não há nenhum submódulo cadastrado.
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($pagina === "adicionar_admin_submodulos"):
            $modulos = buscarModulos($pdo);
            ?>
        <div class="titulo">Adicionar Novo Submódulo</div>
        <form method="post" action="admin_submodulos.php?pagina=adicionar_admin_submodulos&action=adicionar">
            <label for="sub_nome">Nome do Submódulo:</label><br>
            <input type="text" name="sub_nome" id="sub_nome" required><br><br>
            <label for="sub_modulo">Módulo:</label><br>
            <select name="sub_modulo" id="sub_modulo" required>
                <option value="">Selecione</option>
                <?php foreach ($modulos as $mod): ?>
                <option value="<?php echo $mod['mod_id']; ?>">
                    <?php echo htmlspecialchars($mod['mod_nome']); ?>
                </option>
                <?php endforeach; ?>
            </select><br><br>
            <input type="submit" value="Salvar">
            <input type="button" value="Cancelar"
                onclick="window.location.href='admin_submodulos.php?pagina=admin_submodulos';">
        </form>
        <?php endif; ?>

        <?php if ($pagina === "editar_admin_submodulos" && $sub_id):
            $modulos = buscarModulos($pdo);
            $stmt = $pdo->prepare("SELECT * FROM admin_submodulos WHERE sub_id = :sub_id");
            $stmt->bindValue(':sub_id', $sub_id, PDO::PARAM_INT);
            $stmt->execute();
            $sub = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$sub) {
                exibirMensagem('Submódulo não encontrado.');
            }
            ?>
        <div class="titulo">Editar Submódulo</div>
        <form method="post"
            action="admin_submodulos.php?pagina=editar_admin_submodulos&action=editar&sub_id=<?php echo $sub_id; ?>">
            <label for="sub_nome">Nome do Submódulo:</label><br>
            <input type="text" name="sub_nome" id="sub_nome" value="<?php echo htmlspecialchars($sub['sub_nome']); ?>"
                required><br><br>
            <label for="sub_modulo">Módulo:</label><br>
            <select name="sub_modulo" id="sub_modulo" required>
                <option value="">Selecione</option>
                <?php foreach ($modulos as $mod): ?>
                <option value="<?php echo $mod['mod_id']; ?>" <?php if ($sub['sub_modulo'] == $mod['mod_id'])
                               echo 'selected'; ?>>
                    <?php echo htmlspecialchars($mod['mod_nome']); ?>
                </option>
                <?php endforeach; ?>
            </select><br><br>
            <input type="submit" value="Salvar">
            <input type="button" value="Cancelar"
                onclick="window.location.href='admin_submodulos.php?pagina=admin_submodulos';">
        </form>
        <?php endif; ?>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>