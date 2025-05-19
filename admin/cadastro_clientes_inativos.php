<?php
session_start();

require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Constantes
const CAMINHO_FOTO_CLIENTE = '../admin/clientes/';
const FOTO_PADRAO = '../imagens/nophoto.png';
const ITENS_POR_PAGINA = 10;

// Título da página
$titulo = 'Cadastros - Clientes Inativos';

// Variáveis de controle
$acao = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$paginaAtual = max(1, (int) ($_GET['pag'] ?? 1));
$autenticacao = $_GET['autenticacao'] ?? '';
$usuario_id = $_SESSION['usuario_id'] ?? 0;

// Função para upload de foto do cliente
function uploadFoto(array $file, string $destino): string
{
	if (!empty($file['name'][0])) {
		if (!file_exists($destino)) {
			mkdir($destino, 0755, true);
		}
		$nomeOriginal = $file['name'][0];
		$tmpName = $file['tmp_name'][0];
		$extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
		$novoNome = $destino . md5(mt_rand(1, 10000) . $nomeOriginal) . '.' . $extensao;
		move_uploaded_file($tmpName, $novoNome);
		return $novoNome;
	}
	return '';
}

// Função para exibir mensagens
function exibirMensagem(string $icone, string $mensagem, string $acaoFechar = "class='close_janela'"): void
{
	echo "
    <script>
        abreMask(
            '<img src=\"../imagens/$icone.png\"> $mensagem<br><br>'+
            '<input value=\" Ok \" type=\"button\" $acaoFechar>'
        );
    </script>
    ";
}

// CRUD
function adicionarCliente(PDO $pdo): void
{
	$dados = [
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
	$ok = $stmt->execute(array_values($dados));

	if ($ok) {
		$lastId = $pdo->lastInsertId();
		$foto = uploadFoto($_FILES['cli_foto'], CAMINHO_FOTO_CLIENTE);
		if ($foto) {
			$stmt = $pdo->prepare('UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?');
			$stmt->execute([$foto, $lastId]);
		}
		exibirMensagem('ok', 'Cadastro efetuado com sucesso.');
	} else {
		exibirMensagem('x', 'Erro ao efetuar cadastro, por favor tente novamente.', "onclick=\"javascript:window.history.back();\"");
	}
}

function editarCliente(PDO $pdo): void
{
	$cli_id = $_GET['cli_id'] ?? 0;
	$cli_senha_post = $_POST['cli_senha'] ?? '';

	$stmt = $pdo->prepare('SELECT cli_senha FROM cadastro_clientes WHERE cli_id = ?');
	$stmt->execute([$cli_id]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	$senhaAtual = $row['cli_senha'] ?? '';

	$cli_senha = password_verify($cli_senha_post, $senhaAtual) ? $senhaAtual : password_hash($cli_senha_post, PASSWORD_DEFAULT);

	$dados = [
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
	$ok = $stmt->execute($dados);

	if ($ok) {
		if (!empty($_FILES['cli_foto']['name'][0])) {
			// Remove foto antiga
			$stmt = $pdo->prepare('SELECT cli_foto FROM cadastro_clientes WHERE cli_id = ?');
			$stmt->execute([$cli_id]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);
			if (!empty($row['cli_foto']) && file_exists($row['cli_foto'])) {
				unlink($row['cli_foto']);
			}
			$foto = uploadFoto($_FILES['cli_foto'], CAMINHO_FOTO_CLIENTE);
			if ($foto) {
				$stmt = $pdo->prepare('UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?');
				$stmt->execute([$foto, $cli_id]);
			}
		}
		exibirMensagem('ok', 'Dados alterados com sucesso.');
	} else {
		exibirMensagem('x', 'Erro ao alterar dados, por favor tente novamente.', "onclick=\"javascript:window.history.back();\"");
	}
}

function excluirCliente(PDO $pdo): void
{
	$cli_id = $_GET['cli_id'] ?? 0;
	$stmt = $pdo->prepare('UPDATE cadastro_clientes SET cli_deletado = 0 WHERE cli_id = ?');
	$ok = $stmt->execute([$cli_id]);
	if ($ok) {
		exibirMensagem('ok', 'Exclusão realizada com sucesso');
	} else {
		exibirMensagem('x', 'Este item não pode ser excluído pois está relacionado com alguma tabela.', "onclick=\"javascript:window.history.back();\"");
	}
}

function setarStatusCliente(PDO $pdo, int $status): void
{
	$cli_id = $_GET['cli_id'] ?? 0;
	$stmt = $pdo->prepare('UPDATE cadastro_clientes SET cli_status = ? WHERE cli_id = ?');
	$ok = $stmt->execute([$status, $cli_id]);
	if ($ok) {
		$msg = $status ? 'Ativação realizada com sucesso' : 'Desativação realizada com sucesso';
		exibirMensagem('ok', $msg);
	} else {
		exibirMensagem('x', 'Erro ao alterar dados, por favor tente novamente.', "onclick=\"javascript:window.history.back();\"");
	}
}

// Ações
switch ($acao) {
	case 'adicionar':
		adicionarCliente($pdo);
		break;
	case 'editar':
		editarCliente($pdo);
		break;
	case 'excluir':
		excluirCliente($pdo);
		break;
	case 'ativar':
		setarStatusCliente($pdo, 1);
		break;
	case 'desativar':
		setarStatusCliente($pdo, 0);
		break;
}

// Filtros e paginação
$filtroNome = $_REQUEST['fil_nome'] ?? '';
$filtroCnpj = str_replace(['.', '-'], '', $_REQUEST['fil_cli_cnpj'] ?? '');

$where = [
	'cli_status = 0',
	'cli_deletado = 1',
	'ucl_usuario = :usuario_id'
];
$params = [':usuario_id' => $usuario_id];

if ($filtroNome !== '') {
	$where[] = 'cli_nome_razao LIKE :fil_nome';
	$params[':fil_nome'] = "%$filtroNome%";
}
if ($filtroCnpj !== '') {
	$where[] = "REPLACE(REPLACE(cli_cnpj, '.', ''), '-', '') LIKE :fil_cli_cnpj";
	$params[':fil_cli_cnpj'] = "%$filtroCnpj%";
}

$whereSql = implode(' AND ', $where);

// Total de registros para paginação
$countSql = "SELECT COUNT(*) FROM cadastro_clientes 
    INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
    WHERE $whereSql";
$countStmt = $pdo->prepare($countSql);
foreach ($params as $key => $value) {
	$countStmt->bindValue($key, $value);
}
$countStmt->execute();
$totalRegistros = (int) $countStmt->fetchColumn();
$totalPaginas = max(1, ceil($totalRegistros / ITENS_POR_PAGINA));
$primeiroRegistro = ($paginaAtual - 1) * ITENS_POR_PAGINA;

// Consulta paginada
$sql = "SELECT * FROM cadastro_clientes 
    INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
    WHERE $whereSql
    ORDER BY cli_nome_razao ASC
    LIMIT $primeiroRegistro, " . ITENS_POR_PAGINA;

$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
	$stmt->bindValue($key, $value);
}
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para exibir paginação
function exibirPaginacao(int $paginaAtual, int $totalPaginas, string $baseUrl): void
{
	if ($totalPaginas <= 1)
		return;
	echo '<div class="pagination">';
	for ($i = 1; $i <= $totalPaginas; $i++) {
		$active = $i === $paginaAtual ? 'style="font-weight:bold;"' : '';
		echo "<a href=\"{$baseUrl}&pag={$i}\" $active>$i</a> ";
	}
	echo '</div>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?php echo $titulo; ?></title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php
	include '../css/style.php';
	require_once '../mod_includes/php/funcoes-jquery.php';
	?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php include '../mod_topo/topo.php'; ?>
    <?php if ($pagina === 'cadastro_clientes_inativos'): ?>
    <div class="centro">
        <div class="titulo"><?php echo $titulo; ?></div>
        <div class="filtro">
            <form name="form_filtro" id="form_filtro" enctype="multipart/form-data" method="post"
                action="cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos<?php echo $autenticacao; ?>">
                <input name="fil_nome" id="fil_nome" value="<?php echo htmlspecialchars($filtroNome); ?>"
                    placeholder="Nome/Razão Social">
                <input type="text" name="fil_cli_cnpj" id="fil_cli_cnpj" placeholder="C.N.P.J"
                    value="<?php echo htmlspecialchars($filtroCnpj); ?>">
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
						$cli_foto = $cliente['cli_foto'] ?: FOTO_PADRAO;
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
                    href="cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=<?php echo $cli_status ? 'desativar' : 'ativar'; ?>&cli_id=<?php echo $cli_id . $autenticacao; ?>">
                    <img border="0" src="../imagens/icon-ativa-desativa.png">
                </a>
                <a onclick="
					abreMask(
						'Deseja realmente excluir o cliente <b><?php echo $cli_nome_razao; ?></b>?<br><br>'+
						'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=excluir&cli_id=<?php echo $cli_id . $autenticacao; ?>\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\' Não \' type=\'button\' class=\'close_janela\'>' 
					);
				">
                    <img border="0" src="../imagens/icon-excluir.png">
                </a>
            </div>
            <tr class="<?php echo $rowClass; ?>">
                <td><img src="<?php echo htmlspecialchars($cli_foto); ?>" width="100"></td>
                <td><?php echo $cli_nome_razao; ?></td>
                <td><?php echo $cli_cnpj; ?></td>
                <td><?php echo $cli_telefone; ?></td>
                <td><?php echo $cli_email; ?></td>
                <td align="center">
                    <img border="0" src="../imagens/icon-<?php echo $cli_status ? 'ativo' : 'inativo'; ?>.png"
                        width="15" height="15">
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
				$baseUrl = "cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos$autenticacao";
				exibirPaginacao($paginaAtual, $totalPaginas, $baseUrl);
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