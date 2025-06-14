<?php
session_start();
$pagina_link = 'admin_setores';

// Inclusão dos arquivos essenciais (apenas PHP antes do HTML)
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Variáveis de controle
$autenticacao = $_GET['autenticacao'] ?? '';
$titulo = 'Administradores - Setores';
$pagina = $_GET['pagina'] ?? '';
$action = $_GET['action'] ?? '';
$pag = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
$set_id = isset($_GET['set_id']) ? (int) $_GET['set_id'] : 0;
$num_por_pagina = 10;
$primeiro_registro = ($pag - 1) * $num_por_pagina;
$pageBreadcrumb = "Administradores &raquo; <a href='admin_setores.php?pagina=admin_setores$autenticacao'>Setores</a>";

// Função para exibir mensagens e redirecionar
function exibirMensagem($mensagem)
{
    $msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    echo "<script>alert('$msg'); window.location.href = 'admin_setores.php?pagina=admin_setores';</script>";
    exit;
}

// CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $set_nome = trim($_POST['set_nome'] ?? '');

    if ($action === 'adicionar') {
        $stmt = $pdo->prepare("INSERT INTO admin_setores (set_nome) VALUES (:set_nome)");
        if ($stmt->execute(['set_nome' => $set_nome])) {
            exibirMensagem('Cadastro efetuado com sucesso.');
        } else {
            exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
        }
    }

    if ($action === 'editar' && $set_id) {
        $stmt = $pdo->prepare("UPDATE admin_setores SET set_nome = :set_nome WHERE set_id = :set_id");
        if ($stmt->execute(['set_nome' => $set_nome, 'set_id' => $set_id])) {
            exibirMensagem('Dados alterados com sucesso.');
        } else {
            exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
        }
    }
}

if ($action === 'excluir' && $set_id) {
    $stmt = $pdo->prepare("DELETE FROM admin_setores WHERE set_id = :set_id");
    if ($stmt->execute(['set_id' => $set_id])) {
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
    <title>Admin - Setores</title>
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
        <?php if ($pagina === "admin_setores" || $pagina == ''): ?>
            <div class="titulo"><?php echo $pageBreadcrumb; ?></div>
            <div id="botoes">
                <input value="Novo Setor" type="button"
                    onclick="window.location.href='admin_setores.php?pagina=adicionar_admin_setores<?php echo $autenticacao; ?>';" />
            </div>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM admin_setores ORDER BY set_nome ASC LIMIT :offset, :limit");
            $stmt->bindValue(':offset', $primeiro_registro, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $num_por_pagina, PDO::PARAM_INT);
            $stmt->execute();
            $setores = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $totalRegistros = $pdo->query("SELECT COUNT(*) FROM admin_setores")->fetchColumn();
            ?>
            <?php if ($setores): ?>
                <table align="center" width="100%" border="0" cellspacing="0" cellpadding="10" class="bordatabela">
                    <tr>
                        <td class="titulo_tabela">Nome</td>
                        <td class="titulo_tabela" align="center">Gerenciar</td>
                    </tr>
                    <?php foreach ($setores as $setor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($setor['set_nome']); ?></td>
                            <td align="center">
                                <a
                                    href="admin_setores.php?pagina=editar_admin_setores&set_id=<?php echo $setor['set_id'] . $autenticacao; ?>">
                                    <img border="0" src="../imagens/icon-editar.png" alt="Editar">
                                </a>
                                <a href="javascript:void(0);" onclick="
                        if(confirm('Deseja excluir <?php echo addslashes(htmlspecialchars($setor['set_nome'])); ?>?')){window.location.href='admin_setores.php?pagina=admin_setores&action=excluir&set_id=<?php echo $setor['set_id'] . $autenticacao; ?>';}
                    ">
                                    <img border="0" src="../imagens/icon-excluir.png" alt="Excluir">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php
                $totalPaginas = ceil($totalRegistros / $num_por_pagina);
                if ($totalPaginas > 1) {
                    echo "<div class='paginacao'>";
                    for ($i = 1; $i <= $totalPaginas; $i++) {
                        $active = $i == $pag ? "style='font-weight:bold;'" : "";
                        $url = "admin_setores.php?pagina=admin_setores&pag=$i$autenticacao";
                        echo "<a href='$url' $active>$i</a> ";
                    }
                    echo "</div>";
                }
                ?>
            <?php else: ?>
                <br><br><br>Não há nenhum setor cadastrado.
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($pagina === "adicionar_admin_setores"): ?>
            <div class="titulo"><?php echo $pageBreadcrumb; ?> &raquo; Adicionar</div>
            <form method="post"
                action="admin_setores.php?pagina=adicionar_admin_setores&action=adicionar<?php echo $autenticacao; ?>">
                <label for="set_nome">Nome do Setor:</label><br>
                <input type="text" name="set_nome" id="set_nome" required><br><br>
                <input type="submit" value="Salvar">
                <input type="button" value="Cancelar"
                    onclick="window.location.href='admin_setores.php?pagina=admin_setores<?php echo $autenticacao; ?>';">
            </form>
        <?php endif; ?>

        <?php if ($pagina === "editar_admin_setores" && $set_id):
            $stmt = $pdo->prepare("SELECT * FROM admin_setores WHERE set_id = :set_id");
            $stmt->bindValue(':set_id', $set_id, PDO::PARAM_INT);
            $stmt->execute();
            $setor = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$setor) {
                exibirMensagem('Setor não encontrado.');
            }
            ?>
            <div class="titulo"><?php echo $pageBreadcrumb; ?> &raquo; Editar</div>
            <form method="post"
                action="admin_setores.php?pagina=editar_admin_setores&action=editar&set_id=<?php echo $set_id . $autenticacao; ?>">
                <label for="set_nome">Nome do Setor:</label><br>
                <input type="text" name="set_nome" id="set_nome" value="<?php echo htmlspecialchars($setor['set_nome']); ?>"
                    required><br><br>
                <input type="submit" value="Salvar">
                <input type="button" value="Cancelar"
                    onclick="window.location.href='admin_setores.php?pagina=admin_setores<?php echo $autenticacao; ?>';">
            </form>
        <?php endif; ?>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>