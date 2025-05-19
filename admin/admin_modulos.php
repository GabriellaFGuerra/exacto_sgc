<?php
session_start();
$pagina_link = 'admin_modulos';

// Inclusão dos arquivos essenciais (apenas PHP antes do HTML)
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Constantes e variáveis de controle
$itensPorPagina = 10;
$titulo = 'Administradores - Módulos';
$pagina = $_GET['pagina'] ?? '';
$action = $_GET['action'] ?? '';
$pag = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
$mod_id = isset($_GET['mod_id']) ? (int) $_GET['mod_id'] : 0;
$offset = ($pag - 1) * $itensPorPagina;

// Função para exibir mensagens e redirecionar
function exibirMensagem($mensagem)
{
    $msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    echo "<script>alert('$msg'); window.location.href = 'admin_modulos.php?pagina=admin_modulos';</script>";
    exit;
}

// CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mod_nome = trim($_POST['mod_nome'] ?? '');

    if ($action === 'adicionar') {
        $stmt = $pdo->prepare("INSERT INTO admin_modulos (mod_nome) VALUES (:mod_nome)");
        if ($stmt->execute(['mod_nome' => $mod_nome])) {
            exibirMensagem('Cadastro efetuado com sucesso.');
        } else {
            exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
        }
    }

    if ($action === 'editar' && $mod_id) {
        $stmt = $pdo->prepare("UPDATE admin_modulos SET mod_nome = :mod_nome WHERE mod_id = :mod_id");
        if ($stmt->execute(['mod_nome' => $mod_nome, 'mod_id' => $mod_id])) {
            exibirMensagem('Dados alterados com sucesso.');
        } else {
            exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
        }
    }
}

if ($action === 'excluir' && $mod_id) {
    $stmt = $pdo->prepare("DELETE FROM admin_modulos WHERE mod_id = :mod_id");
    if ($stmt->execute(['mod_id' => $mod_id])) {
        exibirMensagem('Exclusão realizada com sucesso.');
    } else {
        exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?php echo htmlspecialchars($titulo); ?></title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php
    include "../css/style.php";
    require_once '../mod_includes/php/funcoes-jquery.php';
    ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
</head>

<body>
    <?php include "../mod_topo/topo.php"; ?>
    <div class="centro">
        <?php if ($pagina === "admin_modulos" || $pagina == ''): ?>
        <div class="titulo"><?php echo $titulo; ?></div>
        <div id="botoes">
            <input value="Novo Módulo" type="button"
                onclick="window.location.href='admin_modulos.php?pagina=adicionar_admin_modulos';" />
        </div>
        <?php
            $stmt = $pdo->prepare("SELECT * FROM admin_modulos ORDER BY mod_nome ASC LIMIT :offset, :limit");
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
            $stmt->execute();
            $modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalRegistros = $pdo->query("SELECT COUNT(*) FROM admin_modulos")->fetchColumn();
            ?>
        <?php if ($modulos): ?>
        <table align="center" width="100%" border="0" cellspacing="0" cellpadding="10" class="bordatabela">
            <tr>
                <td class="titulo_tabela">Nome</td>
                <td class="titulo_tabela" align="center">Gerenciar</td>
            </tr>
            <?php foreach ($modulos as $modulo): ?>
            <tr>
                <td><?php echo htmlspecialchars($modulo['mod_nome']); ?></td>
                <td align="center">
                    <a href="admin_modulos.php?pagina=editar_admin_modulos&mod_id=<?php echo $modulo['mod_id']; ?>">
                        <img border="0" src="../imagens/icon-editar.png" alt="Editar">
                    </a>
                    <a href="javascript:void(0);" onclick="
                        if(confirm('Deseja excluir <?php echo addslashes(htmlspecialchars($modulo['mod_nome'])); ?>?')){window.location.href='admin_modulos.php?pagina=admin_modulos&action=excluir&mod_id=<?php echo $modulo['mod_id']; ?>';}
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
                        $url = "admin_modulos.php?pagina=admin_modulos&pag=$i";
                        echo "<a href='$url' $active>$i</a> ";
                    }
                    echo "</div>";
                }
                ?>
        <?php else: ?>
        <br><br><br>Não há nenhum módulo cadastrado.
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($pagina === "adicionar_admin_modulos"): ?>
        <div class="titulo">Adicionar Novo Módulo</div>
        <form method="post" action="admin_modulos.php?pagina=adicionar_admin_modulos&action=adicionar">
            <label for="mod_nome">Nome do Módulo:</label><br>
            <input type="text" name="mod_nome" id="mod_nome" required><br><br>
            <input type="submit" value="Salvar">
            <input type="button" value="Cancelar"
                onclick="window.location.href='admin_modulos.php?pagina=admin_modulos';">
        </form>
        <?php endif; ?>

        <?php if ($pagina === "editar_admin_modulos" && $mod_id):
            $stmt = $pdo->prepare("SELECT * FROM admin_modulos WHERE mod_id = :mod_id");
            $stmt->bindValue(':mod_id', $mod_id, PDO::PARAM_INT);
            $stmt->execute();
            $modulo = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$modulo) {
                exibirMensagem('Módulo não encontrado.');
            }
            ?>
        <div class="titulo">Editar Módulo</div>
        <form method="post"
            action="admin_modulos.php?pagina=editar_admin_modulos&action=editar&mod_id=<?php echo $mod_id; ?>">
            <label for="mod_nome">Nome do Módulo:</label><br>
            <input type="text" name="mod_nome" id="mod_nome"
                value="<?php echo htmlspecialchars($modulo['mod_nome']); ?>" required><br><br>
            <input type="submit" value="Salvar">
            <input type="button" value="Cancelar"
                onclick="window.location.href='admin_modulos.php?pagina=admin_modulos';">
        </form>
        <?php endif; ?>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>