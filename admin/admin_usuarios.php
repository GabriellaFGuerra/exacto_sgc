<?php
session_start();
$pagina_link = 'admin_usuarios';

require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Título da página
$titulo = 'Administradores - Usuários';
const USUARIOS_POR_PAGINA = 10;

// Função para exibir mensagem e redirecionar
function exibirMensagem(string $mensagem): void
{
	$mensagemSegura = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
	echo "<script>alert('{$mensagemSegura}'); window.location.href = 'admin_usuarios.php?pagina=admin_usuarios';</script>";
	exit;
}

// Funções utilitárias
function getPost(string $chave): string
{
	return trim($_POST[$chave] ?? '');
}
function getInt(string $chave): ?int
{
	return isset($_GET[$chave]) ? (int) $_GET[$chave] : null;
}
function buscarUsuarios(PDO $pdo, int $offset, int $limite): array
{
	$sql = "SELECT u.*, s.set_nome 
            FROM admin_usuarios u
            LEFT JOIN admin_setores s ON s.set_id = u.usu_setor
            ORDER BY u.usu_nome ASC
            LIMIT :offset, :limite";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
	$stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function contarUsuarios(PDO $pdo): int
{
	$sql = "SELECT COUNT(*) FROM admin_usuarios";
	return (int) $pdo->query($sql)->fetchColumn();
}
function iconeStatus(int $status): string
{
	$icon = $status == 1 ? 'icon-ativo.png' : 'icon-inativo.png';
	$alt = $status == 1 ? 'Ativo' : 'Inativo';
	return "<img src='../imagens/$icon' width='15' height='15' alt='$alt'>";
}
function iconeNotificacao(int $notificacao): string
{
	$icon = $notificacao == 1 ? 'ok.png' : 'x.png';
	$alt = $notificacao == 1 ? 'Sim' : 'Não';
	return "<img src='../imagens/$icon' width='15' height='15' alt='$alt'>";
}
function acoesUsuario(int $usu_id, string $usu_nome, int $usu_status, string $autenticacao): string
{
	$toggleAction = $usu_status == 1 ? 'desativar' : 'ativar';
	$toggleIcon = "<a href='admin_usuarios.php?pagina=admin_usuarios&action=$toggleAction&usu_id=$usu_id$autenticacao'><img src='../imagens/icon-ativa-desativa.png' alt='Ativar/Desativar'></a>";
	$editIcon = "<a href='admin_usuarios.php?pagina=editar_admin_usuarios&usu_id=$usu_id$autenticacao'><img src='../imagens/icon-editar.png' alt='Editar'></a>";
	$deleteIcon = "<a onclick=\"abreMask('Deseja realmente excluir o usuário <b>" . htmlspecialchars($usu_nome, ENT_QUOTES, 'UTF-8') . "</b>?<br><br><input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'admin_usuarios.php?pagina=admin_usuarios&action=excluir&usu_id=$usu_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input value=\' Não \' type=\'button\' class=\'close_janela\'');\"><img src='../imagens/icon-excluir.png' alt='Excluir'></a>";
	return "$toggleIcon $editIcon $deleteIcon";
}
function renderizarTabelaUsuarios(array $usuarios, string $autenticacao): string
{
	$html = "<table class='bordatabela' width='100%' cellpadding='10'>
        <tr>
            <td class='titulo_tabela'>Nome</td>
            <td class='titulo_tabela'>Email</td>
            <td class='titulo_tabela'>Setor</td>
            <td class='titulo_tabela'>Login</td>
            <td class='titulo_tabela' align='center'>Status</td>
            <td class='titulo_tabela' align='center'>Recebe notificação?</td>
            <td class='titulo_tabela' align='center'>Gerenciar</td>
        </tr>";
	$rowClass = ['linhaimpar', 'linhapar'];
	foreach ($usuarios as $i => $usuario) {
		$class = $rowClass[$i % 2];
		$html .= "<tr class='$class'>
            <td>" . htmlspecialchars($usuario['usu_nome'], ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . htmlspecialchars($usuario['usu_email'], ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . htmlspecialchars($usuario['set_nome'], ENT_QUOTES, 'UTF-8') . "</td>
            <td>" . htmlspecialchars($usuario['usu_login'], ENT_QUOTES, 'UTF-8') . "</td>
            <td align='center'>" . iconeStatus((int) $usuario['usu_status']) . "</td>
            <td align='center'>" . iconeNotificacao((int) $usuario['usu_notificacao']) . "</td>
            <td align='center'>" . acoesUsuario((int) $usuario['usu_id'], $usuario['usu_nome'], (int) $usuario['usu_status'], $autenticacao) . "</td>
        </tr>";
	}
	$html .= "</table>";
	return $html;
}
function renderizarPaginacao(int $paginaAtual, int $totalPaginas, string $baseUrl): string
{
	if ($totalPaginas <= 1)
		return '';
	$html = "<div class='pagination'>";
	for ($i = 1; $i <= $totalPaginas; $i++) {
		$active = $i == $paginaAtual ? "style='font-weight:bold;'" : '';
		$html .= "<a href='{$baseUrl}&pag={$i}' $active>{$i}</a> ";
	}
	$html .= "</div>";
	return $html;
}

// --- Bloco de adição ---
if (isset($_GET['pagina']) && $_GET['pagina'] === 'adicionar_admin_usuarios') {
	$setores = $pdo->query("SELECT set_id, set_nome FROM admin_setores ORDER BY set_nome ASC")->fetchAll(PDO::FETCH_ASSOC);

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$usu_nome = trim($_POST['usu_nome'] ?? '');
		$usu_email = trim($_POST['usu_email'] ?? '');
		$usu_setor = (int) ($_POST['usu_setor'] ?? 0);
		$usu_login = trim($_POST['usu_login'] ?? '');
		$usu_senha = trim($_POST['usu_senha'] ?? '');
		$usu_status = (int) ($_POST['usu_status'] ?? 1);
		$usu_notificacao = (int) ($_POST['usu_notificacao'] ?? 0);

		$stmt = $pdo->prepare("INSERT INTO admin_usuarios (usu_nome, usu_email, usu_setor, usu_login, usu_senha, usu_status, usu_notificacao) VALUES (:usu_nome, :usu_email, :usu_setor, :usu_login, :usu_senha, :usu_status, :usu_notificacao)");
		$ok = $stmt->execute([
			':usu_nome' => $usu_nome,
			':usu_email' => $usu_email,
			':usu_setor' => $usu_setor,
			':usu_login' => $usu_login,
			':usu_senha' => password_hash($usu_senha, PASSWORD_DEFAULT),
			':usu_status' => $usu_status,
			':usu_notificacao' => $usu_notificacao
		]);
		if ($ok) {
			exibirMensagem('Cadastro efetuado com sucesso.');
		} else {
			exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
		}
	}
	?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Adicionar Usuário</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
</head>

<body>
    <?php include '../mod_topo/topo.php'; ?>
    <div class="centro">
        <div class="titulo">Adicionar Usuário</div>
        <form method="post" action="admin_usuarios.php?pagina=adicionar_admin_usuarios">
            <label for="usu_nome">Nome:</label><br>
            <input type="text" name="usu_nome" id="usu_nome" required><br><br>
            <label for="usu_email">Email:</label><br>
            <input type="email" name="usu_email" id="usu_email" required><br><br>
            <label for="usu_setor">Setor:</label><br>
            <select name="usu_setor" id="usu_setor" required>
                <option value="">Selecione</option>
                <?php foreach ($setores as $setor): ?>
                <option value="<?php echo $setor['set_id']; ?>"><?php echo htmlspecialchars($setor['set_nome']); ?>
                </option>
                <?php endforeach; ?>
            </select><br><br>
            <label for="usu_login">Login:</label><br>
            <input type="text" name="usu_login" id="usu_login" required><br><br>
            <label for="usu_senha">Senha:</label><br>
            <input type="password" name="usu_senha" id="usu_senha" required><br><br>
            <label for="usu_status">Status:</label><br>
            <select name="usu_status" id="usu_status">
                <option value="1">Ativo</option>
                <option value="0">Inativo</option>
            </select><br><br>
            <label for="usu_notificacao">Recebe notificação?</label><br>
            <select name="usu_notificacao" id="usu_notificacao">
                <option value="1">Sim</option>
                <option value="0">Não</option>
            </select><br><br>
            <input type="submit" value="Salvar">
            <input type="button" value="Cancelar"
                onclick="window.location.href='admin_usuarios.php?pagina=admin_usuarios';">
        </form>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>
<?php
	exit;
}

// --- Bloco de edição ---
if (isset($_GET['pagina']) && $_GET['pagina'] === 'editar_admin_usuarios' && isset($_GET['usu_id'])) {
	$usu_id = (int) $_GET['usu_id'];

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$usu_nome = trim($_POST['usu_nome'] ?? '');
		$usu_email = trim($_POST['usu_email'] ?? '');
		$usu_setor = (int) ($_POST['usu_setor'] ?? 0);
		$usu_login = trim($_POST['usu_login'] ?? '');
		$usu_status = (int) ($_POST['usu_status'] ?? 0);
		$usu_notificacao = (int) ($_POST['usu_notificacao'] ?? 0);

		$stmt = $pdo->prepare("UPDATE admin_usuarios SET usu_nome = :usu_nome, usu_email = :usu_email, usu_setor = :usu_setor, usu_login = :usu_login, usu_status = :usu_status, usu_notificacao = :usu_notificacao WHERE usu_id = :usu_id");
		$ok = $stmt->execute([
			':usu_nome' => $usu_nome,
			':usu_email' => $usu_email,
			':usu_setor' => $usu_setor,
			':usu_login' => $usu_login,
			':usu_status' => $usu_status,
			':usu_notificacao' => $usu_notificacao,
			':usu_id' => $usu_id
		]);
		if ($ok) {
			exibirMensagem('Dados alterados com sucesso.');
		} else {
			exibirMensagem('Erro ao alterar dados, tente novamente.');
		}
	}

	$stmt = $pdo->prepare("SELECT * FROM admin_usuarios WHERE usu_id = :usu_id");
	$stmt->execute([':usu_id' => $usu_id]);
	$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

	$setores = $pdo->query("SELECT set_id, set_nome FROM admin_setores ORDER BY set_nome ASC")->fetchAll(PDO::FETCH_ASSOC);

	if ($usuario):
		?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Editar Usuário</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
</head>

<body>
    <?php include '../mod_topo/topo.php'; ?>
    <div class="centro">
        <div class="titulo">Editar Usuário</div>
        <form method="post" action="admin_usuarios.php?pagina=editar_admin_usuarios&usu_id=<?php echo $usu_id; ?>">
            <label for="usu_nome">Nome:</label><br>
            <input type="text" name="usu_nome" id="usu_nome"
                value="<?php echo htmlspecialchars($usuario['usu_nome']); ?>" required><br><br>
            <label for="usu_email">Email:</label><br>
            <input type="email" name="usu_email" id="usu_email"
                value="<?php echo htmlspecialchars($usuario['usu_email']); ?>" required><br><br>
            <label for="usu_setor">Setor:</label><br>
            <select name="usu_setor" id="usu_setor" required>
                <option value="">Selecione</option>
                <?php foreach ($setores as $setor): ?>
                <option value="<?php echo $setor['set_id']; ?>" <?php if ($usuario['usu_setor'] == $setor['set_id'])
								   echo 'selected'; ?>>
                    <?php echo htmlspecialchars($setor['set_nome']); ?>
                </option>
                <?php endforeach; ?>
            </select><br><br>
            <label for="usu_login">Login:</label><br>
            <input type="text" name="usu_login" id="usu_login"
                value="<?php echo htmlspecialchars($usuario['usu_login']); ?>" required><br><br>
            <label for="usu_status">Status:</label><br>
            <select name="usu_status" id="usu_status">
                <option value="1" <?php if ($usuario['usu_status'] == 1)
							echo 'selected'; ?>>Ativo</option>
                <option value="0" <?php if ($usuario['usu_status'] == 0)
							echo 'selected'; ?>>Inativo</option>
            </select><br><br>
            <label for="usu_notificacao">Recebe notificação?</label><br>
            <select name="usu_notificacao" id="usu_notificacao">
                <option value="1" <?php if ($usuario['usu_notificacao'] == 1)
							echo 'selected'; ?>>Sim</option>
                <option value="0" <?php if ($usuario['usu_notificacao'] == 0)
							echo 'selected'; ?>>Não</option>
            </select><br><br>
            <input type="submit" value="Salvar">
            <input type="button" value="Cancelar"
                onclick="window.location.href='admin_usuarios.php?pagina=admin_usuarios';">
        </form>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>
<?php
	else:
		exibirMensagem('Usuário não encontrado.');
	endif;
	exit;
}

// --- Bloco de exclusão ---
if (isset($_GET['action']) && $_GET['action'] === 'excluir' && isset($_GET['usu_id'])) {
	$usu_id = (int) $_GET['usu_id'];
	$stmt = $pdo->prepare("DELETE FROM admin_usuarios WHERE usu_id = :usu_id");
	if ($stmt->execute([':usu_id' => $usu_id])) {
		exibirMensagem('Exclusão realizada com sucesso.');
	} else {
		exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
	}
	exit;
}

// --- Bloco de ativar/desativar ---
if (isset($_GET['action']) && in_array($_GET['action'], ['ativar', 'desativar']) && isset($_GET['usu_id'])) {
	$usu_id = (int) $_GET['usu_id'];
	$novo_status = $_GET['action'] === 'ativar' ? 1 : 0;
	$stmt = $pdo->prepare("UPDATE admin_usuarios SET usu_status = :status WHERE usu_id = :usu_id");
	if ($stmt->execute([':status' => $novo_status, ':usu_id' => $usu_id])) {
		$msg = $novo_status ? 'Ativação realizada com sucesso.' : 'Desativação realizada com sucesso.';
		exibirMensagem($msg);
	} else {
		exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
	}
	exit;
}

// --- Página principal ---
$autenticacao = $_GET['autenticacao'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$paginaTitulo = 'Usuários';
$paginaAtual = isset($_GET['pag']) ? max(1, intval($_GET['pag'])) : 1;
$offset = ($paginaAtual - 1) * USUARIOS_POR_PAGINA;

$totalUsuarios = contarUsuarios($pdo);
$totalPaginas = ceil($totalUsuarios / USUARIOS_POR_PAGINA);

$usuarios = buscarUsuarios($pdo, $offset, USUARIOS_POR_PAGINA);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>
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
    <?php if ($pagina === "admin_usuarios" || $pagina == ''): ?>
    <div class="centro">
        <div class="titulo"><?php echo $paginaTitulo; ?></div>
        <div id="botoes">
            <input value="Novo Usuário" type="button"
                onclick="window.location.href='admin_usuarios.php?pagina=adicionar_admin_usuarios<?php echo $autenticacao; ?>';" />
        </div>
        <?php
			if ($usuarios) {
				echo renderizarTabelaUsuarios($usuarios, $autenticacao);
				$baseUrl = "admin_usuarios.php?pagina=admin_usuarios$autenticacao";
				echo renderizarPaginacao($paginaAtual, $totalPaginas, $baseUrl);
			} else {
				echo "<br><br><br>Não há nenhum usuário cadastrado.";
			}
			?>
    </div>
    <?php endif; ?>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>