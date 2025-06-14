<?php
session_start();
$pagina_link = 'cadastro_gerentes';

// Inclusão dos arquivos essenciais
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Título e breadcrumb
$titulo = 'Cadastro de Gerentes';
$autenticacao = $_GET['autenticacao'] ?? '';
$page = "Cadastros &raquo; <a href='cadastro_gerentes.php?pagina=cadastro_gerentes$autenticacao'>Gerentes</a>";

// Função para exibir mensagens e redirecionar
function exibirMensagem($mensagem)
{
    $msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
    echo "<script>alert('$msg'); window.location.href = 'cadastro_gerentes.php?pagina=cadastro_gerentes';</script>";
    exit;
}

// Função utilitária para pegar parâmetros
function getParam($nome, $padrao = '')
{
    return $_GET[$nome] ?? $_POST[$nome] ?? $padrao;
}

// Variáveis de controle
$action = getParam('action');
$pagina = getParam('pagina');
$pag = (int) getParam('pag', 1);
$filterName = trim(getParam('fil_nome'));

// CRUD - Adicionar Gerente
if ($action === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $gerenteNome = trim($_POST['ger_nome'] ?? '');
    $stmt = $pdo->prepare('INSERT INTO cadastro_gerentes (ger_nome) VALUES (:ger_nome)');
    if ($stmt->execute([':ger_nome' => $gerenteNome])) {
        exibirMensagem('Cadastro efetuado com sucesso.');
    } else {
        exibirMensagem('Erro ao efetuar cadastro, tente novamente.');
    }
}

// CRUD - Editar Gerente
if ($action === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $gerenteId = getParam('ger_id');
    $gerenteNome = trim($_POST['ger_nome'] ?? '');
    $stmt = $pdo->prepare('UPDATE cadastro_gerentes SET ger_nome = :ger_nome WHERE ger_id = :ger_id');
    if ($stmt->execute([':ger_nome' => $gerenteNome, ':ger_id' => $gerenteId])) {
        exibirMensagem('Dados alterados com sucesso.');
    } else {
        exibirMensagem('Erro ao alterar dados, tente novamente.');
    }
}

// CRUD - Excluir Gerente
if ($action === 'excluir') {
    $gerenteId = getParam('ger_id');
    $stmt = $pdo->prepare('DELETE FROM cadastro_gerentes WHERE ger_id = :ger_id');
    if ($stmt->execute([':ger_id' => $gerenteId])) {
        exibirMensagem('Exclusão realizada com sucesso.');
    } else {
        exibirMensagem('Não foi possível excluir, pode estar relacionado a outra tabela.');
    }
}

// Paginação e filtro
$recordsPerPage = 10;
$offset = ($pag - 1) * $recordsPerPage;
$whereClause = $filterName !== '' ? 'ger_nome LIKE :filterName' : '1=1';
$params = $filterName !== '' ? [':filterName' => "%$filterName%"] : [];

// Listagem principal
if ($pagina === 'cadastro_gerentes' || $pagina == '') {
    $sql = "SELECT * FROM cadastro_gerentes WHERE $whereClause ORDER BY ger_nome ASC LIMIT :offset, :limit";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    $gerentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countSql = "SELECT COUNT(*) FROM cadastro_gerentes WHERE $whereClause";
    $countStmt = $pdo->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetchColumn();
    $totalPages = ceil($totalRecords / $recordsPerPage);
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <title>Admin - Cadastro de Gerentes</title>
        <meta charset="utf-8" />
        <link rel="shortcut icon" href="../imagens/favicon.png">
        <?php include '../css/style.php'; ?>
        <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
        <script src="../mod_includes/js/funcoes.js"></script>
    </head>

    <body>
        <?php
        require_once '../mod_includes/php/funcoes-jquery.php';
        include '../mod_topo/topo.php';
        ?>
        <div class="centro">
            <div class="titulo"><?php echo $page; ?></div>
            <div id="botoes">
                <input value="Novo Gerente" type="button"
                    onclick="window.location.href='cadastro_gerentes.php?pagina=adicionar_cadastro_gerentes<?php echo $autenticacao; ?>';" />
            </div>
            <div class="filtro">
                <form name="form_filtro" id="form_filtro" method="post"
                    action="cadastro_gerentes.php?pagina=cadastro_gerentes<?php echo $autenticacao; ?>">
                    <input name="fil_nome" id="fil_nome" value="<?php echo htmlspecialchars($filterName); ?>"
                        placeholder="Nome">
                    <input type="submit" value="Filtrar">
                </form>
            </div>
            <?php if ($totalRecords > 0): ?>
                <table align="center" width="100%" border="0" cellspacing="0" cellpadding="10" class="bordatabela">
                    <tr>
                        <td class="titulo_tabela">Nome</td>
                        <td class="titulo_tabela" align="center">Gerenciar</td>
                    </tr>
                    <?php foreach ($gerentes as $index => $gerente): ?>
                        <?php
                        $gerenteId = $gerente['ger_id'];
                        $gerenteNome = htmlspecialchars($gerente['ger_nome']);
                        $rowClass = $index % 2 === 0 ? 'linhaimpar' : 'linhapar';
                        ?>
                        <tr class="<?php echo $rowClass; ?>">
                            <td><?php echo $gerenteNome; ?></td>
                            <td align="center">
                                <a
                                    href="cadastro_gerentes.php?pagina=editar_cadastro_gerentes&ger_id=<?php echo $gerenteId . $autenticacao; ?>">
                                    <img border="0" src="../imagens/icon-editar.png" alt="Editar">
                                </a>
                                &nbsp;
                                <a href="javascript:void(0);" onclick="
                        if(confirm('Deseja realmente excluir o gerente &quot;<?php echo $gerenteNome; ?>&quot;?')){window.location.href='cadastro_gerentes.php?pagina=cadastro_gerentes&action=excluir&ger_id=<?php echo $gerenteId . $autenticacao; ?>';}
                    ">
                                    <img border="0" src="../imagens/icon-excluir.png" alt="Excluir">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php if ($totalPages > 1): ?>
                    <div class="paginacao">
                        <?php for ($i = 1; $i <= $totalPages; $i++):
                            $active = $i == $pag ? "style='font-weight:bold;'" : "";
                            $url = "cadastro_gerentes.php?pagina=cadastro_gerentes&pag=$i$autenticacao";
                            ?>
                            <a href="<?php echo $url; ?>" <?php echo $active; ?>><?php echo $i; ?></a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <br><br><br>Não há nenhum gerente cadastrado.
            <?php endif; ?>
            <div class="titulo"></div>
        </div>
        <?php include '../mod_rodape/rodape.php'; ?>
    </body>

    </html>
    <?php
    exit;
}

// Formulário de adição
if ($pagina === 'adicionar_cadastro_gerentes') {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">

    <head>
        <title>Admin - Adicionar Gerente</title>
        <meta charset="utf-8" />
        <?php include '../css/style.php'; ?>
        <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
        <script src="../mod_includes/js/funcoes.js"></script>
    </head>

    <body>
        <?php
        require_once '../mod_includes/php/funcoes-jquery.php';
        include '../mod_topo/topo.php';
        ?>
        <form name="form_cadastro_gerentes" id="form_cadastro_gerentes" method="post"
            action="cadastro_gerentes.php?pagina=cadastro_gerentes&action=adicionar<?php echo $autenticacao; ?>">
            <div class="centro">
                <div class="titulo"><?php echo $page; ?> &raquo; Adicionar</div>
                <table align="center" cellspacing="0" width="680">
                    <tr>
                        <td align="left">
                            <input name="ger_nome" id="ger_nome" placeholder="Nome" required>
                            <p>
                                <center>
                                    <div id="erro" align="center">&nbsp;</div>
                                    <input type="submit" id="bt_cadastro_gerentes" value="Salvar" />&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="button" id="botao_cancelar"
                                        onclick="window.location.href='cadastro_gerentes.php?pagina=cadastro_gerentes<?php echo $autenticacao; ?>';"
                                        value="Cancelar" />
                                </center>
                        </td>
                    </tr>
                </table>
                <div class="titulo"></div>
            </div>
        </form>
        <?php include '../mod_rodape/rodape.php'; ?>
    </body>

    </html>
    <?php
    exit;
}

// Formulário de edição
if ($pagina === 'editar_cadastro_gerentes') {
    $gerenteId = getParam('ger_id');
    $stmt = $pdo->prepare('SELECT * FROM cadastro_gerentes WHERE ger_id = :ger_id');
    $stmt->execute([':ger_id' => $gerenteId]);
    $gerente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($gerente) {
        $gerenteNome = htmlspecialchars($gerente['ger_nome']);
        ?>
        <!DOCTYPE html>
        <html lang="pt-br">

        <head>
            <title>Admin - Editar Gerente: <?php echo $gerenteNome; ?></title>
            <meta charset="utf-8" />
            <?php include '../css/style.php'; ?>
            <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
            <script src="../mod_includes/js/funcoes.js"></script>
        </head>

        <body>
            <?php
            require_once '../mod_includes/php/funcoes-jquery.php';
            include '../mod_topo/topo.php';
            ?>
            <form name="form_cadastro_gerentes" id="form_cadastro_gerentes" method="post"
                action="cadastro_gerentes.php?pagina=cadastro_gerentes&action=editar&ger_id=<?php echo $gerenteId . $autenticacao; ?>">
                <div class="centro">
                    <div class="titulo"><?php echo $page; ?> &raquo; Editar: <?php echo $gerenteNome; ?></div>
                    <table align="center" cellspacing="0">
                        <tr>
                            <td align="left">
                                <input type="hidden" name="ger_id" id="ger_id" value="<?php echo $gerenteId; ?>">
                                <input name="ger_nome" id="ger_nome" value="<?php echo $gerenteNome; ?>" placeholder="Nome"
                                    required>
                                <p>
                                    <center>
                                        <div id="erro" align="center">&nbsp;</div>
                                        <input type="submit" id="bt_cadastro_gerentes" value="Salvar" />&nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="button" id="botao_cancelar"
                                            onclick="window.location.href='cadastro_gerentes.php?pagina=cadastro_gerentes<?php echo $autenticacao; ?>';"
                                            value="Cancelar" />
                                    </center>
                            </td>
                        </tr>
                    </table>
                    <div class="titulo"></div>
                </div>
            </form>
            <?php include '../mod_rodape/rodape.php'; ?>
        </body>

        </html>
        <?php
    }
    exit;
}