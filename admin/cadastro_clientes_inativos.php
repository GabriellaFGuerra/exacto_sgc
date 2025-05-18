<?php
session_start();

require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';
include '../mod_topo/topo.php';

const CLIENT_PHOTO_PATH = '../admin/clientes/';
const DEFAULT_PHOTO = '../imagens/nophoto.png';
const ITEMS_PER_PAGE = 10;

$pageTitle = 'Cadastros &raquo; <a href="cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos">Clientes Inativos</a>';
$action = $_GET['action'] ?? '';
$currentPage = $_GET['pagina'] ?? '';
$pageNumber = max(1, (int) ($_GET['pag'] ?? 1));
$auth = $_GET['autenticacao'] ?? '';
$userId = $_SESSION['usuario_id'] ?? 0;

// Função para upload de foto
function uploadPhoto(array $file, string $destinationPath): string
{
	if (!empty($file['name'][0])) {
		if (!file_exists($destinationPath)) {
			mkdir($destinationPath, 0755, true);
		}
		$originalName = $file['name'][0];
		$tmpName = $file['tmp_name'][0];
		$extension = pathinfo($originalName, PATHINFO_EXTENSION);
		$newFileName = $destinationPath . md5(mt_rand(1, 10000) . $originalName) . '.' . $extension;
		move_uploaded_file($tmpName, $newFileName);
		return $newFileName;
	}
	return '';
}

// Função para exibir mensagens
function showMessage(string $icon, string $message, string $closeAction = "class='close_janela'"): void
{
	echo "
	<script>
		abreMask(
			'<img src=\"../imagens/$icon.png\"> $message<br><br>'+
			'<input value=\" Ok \" type=\"button\" $closeAction>'
		);
	</script>
	";
}

// CRUD
function addClient(PDO $pdo): void
{
	$data = [
		'cli_nome_razao' => $_POST['cli_nome_razao'] ?? '',
		'cli_cnpj' => $_POST['cli_cnpj'] ?? '',
		'cli_cep' => $_POST['cli_cep'] ?? '',
		'cli_uf' => $_POST['cli_uf'] ?? '',
		'cli_municipio' => $_POST['cli_municipio'] ?? '',
		'cli_bairro' => $_POST['cli_bairro'] ?? '',
		'cli_endereco' => $_POST['cli_endereco'] ?? '',
		'cli_numero' => $_POST['cli_numero'] ?? '',
		'cli_comp' => $_POST['cli_comp'] ?? '',
		'cli_telefone' => $_POST['cli_telefone'] ?? '',
		'cli_email' => $_POST['cli_email'] ?? '',
		'cli_senha' => password_hash($_POST['cli_senha'] ?? '', PASSWORD_DEFAULT),
		'cli_status' => $_POST['cli_status'] ?? 0
	];

	$stmt = $pdo->prepare('INSERT INTO cadastro_clientes (
		cli_nome_razao, cli_cnpj, cli_cep, cli_uf, cli_municipio, cli_bairro, cli_endereco, cli_numero, cli_comp, cli_telefone, cli_email, cli_senha, cli_status
	) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
	$ok = $stmt->execute(array_values($data));

	if ($ok) {
		$lastId = $pdo->lastInsertId();
		$photo = uploadPhoto($_FILES['cli_foto'], CLIENT_PHOTO_PATH);
		if ($photo) {
			$stmt = $pdo->prepare('UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?');
			$stmt->execute([$photo, $lastId]);
		}
		showMessage('ok', 'Cadastro efetuado com sucesso.');
	} else {
		showMessage('x', 'Erro ao efetuar cadastro, por favor tente novamente.', "onclick=\"javascript:window.history.back();\"");
	}
}

function editClient(PDO $pdo): void
{
	$cli_id = $_GET['cli_id'] ?? 0;
	$cli_senha_post = $_POST['cli_senha'] ?? '';

	$stmt = $pdo->prepare('SELECT cli_senha FROM cadastro_clientes WHERE cli_id = ?');
	$stmt->execute([$cli_id]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$currentPassword = $row['cli_senha'] ?? '';

	$cli_senha = password_verify($cli_senha_post, $currentPassword) ? $currentPassword : password_hash($cli_senha_post, PASSWORD_DEFAULT);

	$data = [
		$_POST['cli_nome_razao'] ?? '',
		$_POST['cli_cnpj'] ?? '',
		$_POST['cli_cep'] ?? '',
		$_POST['cli_uf'] ?? '',
		$_POST['cli_municipio'] ?? '',
		$_POST['cli_bairro'] ?? '',
		$_POST['cli_endereco'] ?? '',
		$_POST['cli_numero'] ?? '',
		$_POST['cli_comp'] ?? '',
		$_POST['cli_telefone'] ?? '',
		$_POST['cli_email'] ?? '',
		$cli_senha,
		$_POST['cli_status'] ?? 0,
		$cli_id
	];

	$stmt = $pdo->prepare('UPDATE cadastro_clientes SET
		cli_nome_razao = ?, cli_cnpj = ?, cli_cep = ?, cli_uf = ?, cli_municipio = ?, cli_bairro = ?, cli_endereco = ?, cli_numero = ?, cli_comp = ?, cli_telefone = ?, cli_email = ?, cli_senha = ?, cli_status = ?
		WHERE cli_id = ?');
	$ok = $stmt->execute($data);

	if ($ok) {
		if (!empty($_FILES['cli_foto']['name'][0])) {
			// Remove foto antiga
			$stmt = $pdo->prepare('SELECT cli_foto FROM cadastro_clientes WHERE cli_id = ?');
			$stmt->execute([$cli_id]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if (!empty($row['cli_foto']) && file_exists($row['cli_foto'])) {
				unlink($row['cli_foto']);
			}
			$photo = uploadPhoto($_FILES['cli_foto'], CLIENT_PHOTO_PATH);
			if ($photo) {
				$stmt = $pdo->prepare('UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?');
				$stmt->execute([$photo, $cli_id]);
			}
		}
		showMessage('ok', 'Dados alterados com sucesso.');
	} else {
		showMessage('x', 'Erro ao alterar dados, por favor tente novamente.', "onclick=\"javascript:window.history.back();\"");
	}
}

function softDeleteClient(PDO $pdo): void
{
	$cli_id = $_GET['cli_id'] ?? 0;
	$stmt = $pdo->prepare('UPDATE cadastro_clientes SET cli_deletado = 0 WHERE cli_id = ?');
	$ok = $stmt->execute([$cli_id]);
	if ($ok) {
		showMessage('ok', 'Exclusão realizada com sucesso');
	} else {
		showMessage('x', 'Este item não pode ser excluído pois está relacionado com alguma tabela.', "onclick=\"javascript:window.history.back();\"");
	}
}

function setClientStatus(PDO $pdo, int $status): void
{
	$cli_id = $_GET['cli_id'] ?? 0;
	$stmt = $pdo->prepare('UPDATE cadastro_clientes SET cli_status = ? WHERE cli_id = ?');
	$ok = $stmt->execute([$status, $cli_id]);
	if ($ok) {
		$msg = $status ? 'Ativação realizada com sucesso' : 'Desativação realizada com sucesso';
		showMessage('ok', $msg);
	} else {
		showMessage('x', 'Erro ao alterar dados, por favor tente novamente.', "onclick=\"javascript:window.history.back();\"");
	}
}

// Ações
switch ($action) {
	case 'adicionar':
		addClient($pdo);
		break;
	case 'editar':
		editClient($pdo);
		break;
	case 'excluir':
		softDeleteClient($pdo);
		break;
	case 'ativar':
		setClientStatus($pdo, 1);
		break;
	case 'desativar':
		setClientStatus($pdo, 0);
		break;
}

// Filtros e paginação
$filterName = $_REQUEST['fil_nome'] ?? '';
$filterCnpj = str_replace(['.', '-'], '', $_REQUEST['fil_cli_cnpj'] ?? '');

$whereClauses = [
	'cli_status = 0',
	'cli_deletado = 1',
	'ucl_usuario = :usuario_id'
];
$params = [':usuario_id' => $userId];

if ($filterName !== '') {
	$whereClauses[] = 'cli_nome_razao LIKE :fil_nome';
	$params[':fil_nome'] = "%$filterName%";
}
if ($filterCnpj !== '') {
	$whereClauses[] = "REPLACE(REPLACE(cli_cnpj, '.', ''), '-', '') LIKE :fil_cli_cnpj";
	$params[':fil_cli_cnpj'] = "%$filterCnpj%";
}

$whereSql = implode(' AND ', $whereClauses);

// Total de registros para paginação
$countSql = "SELECT COUNT(*) FROM cadastro_clientes 
	INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
	WHERE $whereSql";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
	$countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalRecords = (int) $countStmt->fetchColumn();
$totalPages = max(1, ceil($totalRecords / ITEMS_PER_PAGE));
$firstRecord = ($pageNumber - 1) * ITEMS_PER_PAGE;

// Consulta paginada
$sql = "SELECT * FROM cadastro_clientes 
	INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
	WHERE $whereSql
	ORDER BY cli_nome_razao ASC
	LIMIT $firstRecord, " . ITEMS_PER_PAGE;

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
	$stmt->bindValue($key, $value);
}
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para exibir paginação
function renderPagination(int $currentPage, int $totalPages, string $baseUrl): void
{
	if ($totalPages <= 1)
		return;
	echo '<div class="pagination">';
	for ($i = 1; $i <= $totalPages; $i++) {
		$active = $i === $currentPage ? 'style="font-weight:bold;"' : '';
		echo "<a href=\"{$baseUrl}&pag={$i}\" $active>$i</a> ";
	}
	echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<title>
		<?php echo $pageTitle; ?>
	</title>
	<meta name="author" content="MogiComp">
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include '../css/style.php'; ?>
	<script src="../mod_includes/js/funcoes.js"></script>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
	<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
	<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet"> <script
		src="../mod_includes/js/toolbar/jquery.toolbar.js"></script> </head> <body> <?php include '../mod_includes/php/funcoes-jquery.php'; ?> <?php if ($currentPage === 'cadastro_clientes_inativos'): ?> <div
			class="centro"> <div class="titulo"><?php echo $pageTitle; ?></div> <div class="filtro">
		<form name="form_filtro" id="form_filtro" enctype="multipart/form-data" method="post"
			action="cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos<?php echo $auth; ?>">
			<input name="fil_nome" id="fil_nome" value="<?php echo htmlspecialchars($filterName); ?>"
				placeholder="Nome/Razão Social">
			<input type="text" name="fil_cli_cnpj" id="fil_cli_cnpj" placeholder="C.N.P.J"
				value="<?php echo htmlspecialchars($filterCnpj); ?>">
			<input type="submit" value="Filtrar">
		</form>
		</div>
		<?php if (count($clientes) > 0): ?>
				<table align="center" width="100%" border="0" cellspacing="0" cellpadding="10" class="bordatabela">
					<tr>
						<td class="titulo_tabela">Logo</td>
						<td class="titulo_tabela">Razão Social</td>
						<td class="titulo_tabela">CNPJ</td>
						<td class="titulo_tabela">Telefone</td>
						<td class="titulo_tabela">Email</td>
						<td class="titulo_tabela">Status</td>
						<td class="titulo_tabela" align="center">Gerenciar</td>
					</tr>
					<?php foreach ($clientes as $index => $cliente): ?>
				<?php
				$cli_id = $cliente['cli_id'];
				$cli_nome_razao = htmlspecialchars($cliente['cli_nome_razao']);
				$cli_cnpj = htmlspecialchars($cliente['cli_cnpj']);
				$cli_telefone = htmlspecialchars($cliente['cli_telefone']);
				$cli_email = htmlspecialchars($cliente['cli_email']);
				$cli_foto = $cliente['cli_foto'] ?: DEFAULT_PHOTO;
				$cli_status = $cliente['cli_status'];
				$rowClass = $index % 2 === 0 ? 'linhaimpar' : 'linhapar';
				?>
				<script>
					jQuery(document).ready(function($) {
						$('#normal-button-<?php echo $cli_id; ?>').toolbar({
								content: '#user-options-<?php echo $cli_id; ?>',
							position: 'top',
							hideOnClick: true
							});
						});
				</script>
				<div id="user-options-<?php echo $cli_id; ?>" class="toolbar-icons" style="display: none;">
					<a
						href="cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=<?php echo $cli_status ? 'desativar' : 'ativar'; ?>&cli_id=<?php echo $cli_id . $auth; ?>">
						<img border="0" src="../imagens/icon-ativa-desativa.png">
					</a>
					<a onclick="
					abreMask(
						'Deseja realmente excluir o cliente <b><?php echo $cli_nome_razao; ?></b>?<br><br>'+
						'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=excluir&cli_id=<?php echo $cli_id . $auth; ?>\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\' Não \' type=\'button\' class=\'close_janela\'>' 
					);
				">
								<img border="0" src="../imagens/icon-excluir.png">
							</a>
						</div>
						<tr class="<?php echo $rowClass; ?>">
							<td><img src="<?php echo htmlspecialchars($cli_foto); ?>" width="100"></td>
							<td>
								<?php echo $cli_nome_razao; ?></td>
							<td>
								<?php echo $cli_cnpj; ?></td
							>
							<td><?php echo $cli_telefone; ?></td>
							<td><?php echo $cli_email; ?></td>
								<td align="center">

													<img border="0" src="../imagens/icon-<?php echo $cli_status ? 'ativo' : 'inativo'; ?>.png" width="15" height="15">
							</td>
							<td align="center">
								<div id="normal-button-<?php echo $cli_id; ?>" class="settings-button">
									<img src="../imagens/icon-cog-small.png" />
								</div>
							</td>
						</tr>
						<?php endforeach; ?>
					</table>
				<?php
				$baseUrl = "cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos$auth";
				renderPagination($pageNumber, $totalPages, $baseUrl);
				?>
				<?php else: ?>
					<br><br><br>Não há nenhum cliente inativo.
				<?php endif; ?>
			<div class="titulo"></div>
		</div>
	<?php endif; ?>

		<?php include '../mod_rodape/rodape.php'; ?>
</body>
</html>