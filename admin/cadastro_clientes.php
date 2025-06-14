<?php
session_start();
$pagina_link = 'cadastro_clientes';
require_once '../mod_includes/php/connect.php';

// Título padronizado da página
$titulo = 'Cadastros - Clientes';

// Função para exibir mensagens e redirecionar
function exibirMensagem($mensagem)
{
	$msg = htmlspecialchars(strip_tags($mensagem), ENT_QUOTES, 'UTF-8');
	echo "<script>alert('$msg'); window.location.href = 'cadastro_clientes.php?pagina=cadastro_clientes';</script>";
	exit;
}

// Função de paginação
function renderPagination($total, $perPage, $currentPage, $baseUrl, $extraParams = [])
{
	$totalPages = ceil($total / $perPage);
	if ($totalPages <= 1)
		return;
	$query = http_build_query(array_merge($extraParams, ['pagina' => 'cadastro_clientes']));
	echo "<div class='pagination'>";
	for ($i = 1; $i <= $totalPages; $i++) {
		$active = $i == $currentPage ? "style='font-weight:bold;'" : '';
		echo "<a href='{$baseUrl}?{$query}&pag={$i}' {$active}>{$i}</a> ";
	}
	echo "</div>";
}

// Variáveis de controle
$action = $_REQUEST['action'] ?? '';
$pagina = $_REQUEST['pagina'] ?? '';
$pag = max(1, (int) ($_REQUEST['pag'] ?? 1));
$numPorPagina = 10;

// CRUD - Adicionar Cliente
if ($action === "adicionar") {
	$cli_nome_razao = $_POST['cli_nome_razao'] ?? '';
	$cli_cnpj = $_POST['cli_cnpj'] ?? '';
	$cli_cep = $_POST['cli_cep'] ?? '';
	$cli_uf = $_POST['cli_uf'] ?? '';
	$cli_municipio = $_POST['cli_municipio'] ?? '';
	$cli_bairro = $_POST['cli_bairro'] ?? '';
	$cli_endereco = $_POST['cli_endereco'] ?? '';
	$cli_numero = $_POST['cli_numero'] ?? '';
	$cli_comp = $_POST['cli_comp'] ?? '';
	$cli_telefone = $_POST['cli_telefone'] ?? '';
	$cli_email = $_POST['cli_email'] ?? '';
	$cli_senha = password_hash($_POST['cli_senha'] ?? '', PASSWORD_DEFAULT);
	$cli_status = $_POST['cli_status'] ?? 1;

	$stmt = $pdo->prepare(
		"INSERT INTO cadastro_clientes (
            cli_nome_razao, cli_cnpj, cli_cep, cli_uf, cli_municipio, cli_bairro, cli_endereco, cli_numero, cli_comp, cli_telefone, cli_email, cli_senha, cli_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
	);
	$ok = $stmt->execute([
		$cli_nome_razao,
		$cli_cnpj,
		$cli_cep,
		$cli_uf,
		$cli_municipio,
		$cli_bairro,
		$cli_endereco,
		$cli_numero,
		$cli_comp,
		$cli_telefone,
		$cli_email,
		$cli_senha,
		$cli_status
	]);
	if ($ok) {
		$ultimo_id = $pdo->lastInsertId();
		$caminho = "../admin/clientes/";
		if (!empty($_FILES['cli_foto']['name'][0])) {
			if (!file_exists($caminho))
				mkdir($caminho, 0755, true);
			$nomeArquivo = $_FILES['cli_foto']['name'][0];
			$nomeTemporario = $_FILES['cli_foto']['tmp_name'][0];
			$extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
			$arquivo = $caminho . md5(mt_rand(1, 10000) . $nomeArquivo) . '.' . $extensao;
			move_uploaded_file($nomeTemporario, $arquivo);
			$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?");
			$stmt->execute([$arquivo, $ultimo_id]);
		}
		exibirMensagem('Cadastro efetuado com sucesso.');
	} else {
		exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
	}
}

// CRUD - Editar Cliente
if ($action === 'editar') {
	$cli_id = $_GET['cli_id'] ?? 0;
	$cli_nome_razao = $_POST['cli_nome_razao'] ?? '';
	$cli_cnpj = $_POST['cli_cnpj'] ?? '';
	$cli_cep = $_POST['cli_cep'] ?? '';
	$cli_uf = $_POST['cli_uf'] ?? '';
	$cli_municipio = $_POST['cli_municipio'] ?? '';
	$cli_bairro = $_POST['cli_bairro'] ?? '';
	$cli_endereco = $_POST['cli_endereco'] ?? '';
	$cli_numero = $_POST['cli_numero'] ?? '';
	$cli_comp = $_POST['cli_comp'] ?? '';
	$cli_telefone = $_POST['cli_telefone'] ?? '';
	$cli_email = $_POST['cli_email'] ?? '';
	$cli_senha = $_POST['cli_senha'] ?? '';
	$cli_status = $_POST['cli_status'] ?? 1;

	$stmt = $pdo->prepare("SELECT cli_senha FROM cadastro_clientes WHERE cli_id = ?");
	$stmt->execute([$cli_id]);
	$senhaAntiga = $stmt->fetchColumn();
	if (password_verify($cli_senha, $senhaAntiga)) {
		$cli_senha = $senhaAntiga;
	} else {
		$cli_senha = password_hash($cli_senha, PASSWORD_DEFAULT);
	}

	$stmt = $pdo->prepare(
		"UPDATE cadastro_clientes SET 
            cli_nome_razao = ?, cli_cnpj = ?, cli_cep = ?, cli_uf = ?, cli_municipio = ?, cli_bairro = ?, cli_endereco = ?, cli_numero = ?, cli_comp = ?, cli_telefone = ?, cli_email = ?, cli_senha = ?, cli_status = ?
            WHERE cli_id = ?"
	);
	$ok = $stmt->execute([
		$cli_nome_razao,
		$cli_cnpj,
		$cli_cep,
		$cli_uf,
		$cli_municipio,
		$cli_bairro,
		$cli_endereco,
		$cli_numero,
		$cli_comp,
		$cli_telefone,
		$cli_email,
		$cli_senha,
		$cli_status,
		$cli_id
	]);
	if ($ok) {
		$caminho = "../admin/clientes/";
		if (!empty($_FILES['cli_foto']['name'][0])) {
			if (!file_exists($caminho))
				mkdir($caminho, 0755, true);
			$nomeArquivo = $_FILES['cli_foto']['name'][0];
			$nomeTemporario = $_FILES['cli_foto']['tmp_name'][0];
			$extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
			$arquivo = $caminho . md5(mt_rand(1, 10000) . $nomeArquivo) . '.' . $extensao;

			$stmt = $pdo->prepare("SELECT cli_foto FROM cadastro_clientes WHERE cli_id = ?");
			$stmt->execute([$cli_id]);
			$cli_foto_old = $stmt->fetchColumn();
			if ($cli_foto_old && file_exists($cli_foto_old))
				unlink($cli_foto_old);

			move_uploaded_file($nomeTemporario, $arquivo);
			$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?");
			$stmt->execute([$arquivo, $cli_id]);
		}
		exibirMensagem('Dados alterados com sucesso.');
	} else {
		exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
	}
}

// CRUD - Excluir Cliente (soft delete)
if ($action === 'excluir') {
	$cli_id = $_GET['cli_id'] ?? 0;
	$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_deletado = 0 WHERE cli_id = ?");
	if ($stmt->execute([$cli_id])) {
		exibirMensagem('Exclusão realizada com sucesso.');
	} else {
		exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
	}
}

// CRUD - Ativar/Desativar Cliente
if ($action === 'ativar' || $action === 'desativar') {
	$cli_id = $_GET['cli_id'] ?? 0;
	$status = $action === 'ativar' ? 1 : 0;
	$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_status = ? WHERE cli_id = ?");
	if ($stmt->execute([$status, $cli_id])) {
		$msg = $status ? "Ativação realizada com sucesso." : "Desativação realizada com sucesso.";
		exibirMensagem($msg);
	} else {
		exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
	}
}

// Filtros
$fil_nome = $_REQUEST['fil_nome'] ?? '';
$fil_cli_cnpj = str_replace(['.', '-'], '', $_REQUEST['fil_cli_cnpj'] ?? '');

$where = [];
$params = [':usuario_id' => $_SESSION['usuario_id']];
$where[] = "cli_deletado = 1";
$where[] = "cli_status = 1";
$where[] = "ucl_usuario = :usuario_id";
if ($fil_nome) {
	$where[] = "cli_nome_razao LIKE :fil_nome";
	$params[':fil_nome'] = "%$fil_nome%";
}
if ($fil_cli_cnpj) {
	$where[] = "REPLACE(REPLACE(cli_cnpj, '.', ''), '-', '') LIKE :fil_cli_cnpj";
	$params[':fil_cli_cnpj'] = "%$fil_cli_cnpj%";
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
$totalClientes = $countStmt->fetchColumn();

// Consulta paginada
$sql = "SELECT * FROM cadastro_clientes 
    INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
    WHERE $whereSql
    ORDER BY cli_nome_razao ASC
    LIMIT :offset, :limit";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $value) {
	$stmt->bindValue($key, $value);
}
$stmt->bindValue(':offset', ($pag - 1) * $numPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':limit', $numPorPagina, PDO::PARAM_INT);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<title>Admin - Cadastro de Clientes</title>
	<meta name="author" content="MogiComp">
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include '../css/style.php'; ?>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
	<script src="../mod_includes/js/funcoes.js"></script>
</head>

<body>
	<?php include '../mod_includes/php/funcoes-jquery.php'; ?>
	<?php include '../mod_includes/php/topo.php'; ?>
	<?php if ($pagina == "cadastro_clientes"): ?>
		<div class='centro'>
			<div class='titulo'>
				<?php echo $titulo; ?>
			</div>
			<div id='botoes'>
				<input value='Novo Cliente' type='button'
					onclick="window.location.href='cadastro_clientes.php?pagina=adicionar_cadastro_clientes';" />
			</div>
			<div class='filtro'>
				<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post'
					action='cadastro_clientes.php?pagina=cadastro_clientes'>
					<input name='fil_nome' id='fil_nome' value='<?php echo htmlspecialchars($fil_nome); ?>'
						placeholder='Nome/Razão Social'>
					<input type='text' name='fil_cli_cnpj' id='fil_cli_cnpj' placeholder='C.N.P.J'
						value='<?php echo htmlspecialchars($fil_cli_cnpj); ?>'>
					<input type='submit' value='Filtrar'>
				</form>
			</div>
			<?php if (count($clientes) > 0): ?>
				<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
					<tr>
						<td class='titulo_tabela'>Logo</td>
						<td class='titulo_tabela'>Razão Social</td>
						<td class='titulo_tabela'>CNPJ</td>
						<td class='titulo_tabela'>Telefone</td>
						<td class='titulo_tabela'>Email</td>
						<td class='titulo_tabela'>Status</td>
						<td class='titulo_tabela' align='center'>Gerenciar</td>
					</tr>
					<?php $c = 0;
					foreach ($clientes as $cliente): ?>
						<?php
						$cli_id = $cliente['cli_id'];
						$cli_nome_razao = htmlspecialchars($cliente['cli_nome_razao']);
						$cli_cnpj = htmlspecialchars($cliente['cli_cnpj']);
						$cli_telefone = htmlspecialchars($cliente['cli_telefone']);
						$cli_email = htmlspecialchars($cliente['cli_email']);
						$cli_foto = $cliente['cli_foto'] ?: '../imagens/nophoto.png';
						$cli_status = $cliente['cli_status'];
						$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
						$c++;
						?>
						<tr class='<?php echo $c1; ?>'>
							<td><img src='<?php echo $cli_foto; ?>' width='100'></td>
							<td>
								<?php echo $cli_nome_razao; ?>
							</td>
							<td>
								<?php echo $cli_cnpj; ?>
							</td>
							<td>
								<?php echo $cli_telefone; ?>
							</td>
							<td>
								<?php echo $cli_email; ?>
							</td>
							<td align="center">
								<?php if ($cli_status == 1): ?>
									<img border='0' src='../imagens/icon-ativo.png' width='15' height='15'>
								<?php else: ?>
									<img border='0' src='../imagens/icon-inativo.png' width='15' height='15'>
								<?php endif; ?>
							</td>
							<td align="center">
								<a
									href='cadastro_clientes.php?pagina=cadastro_clientes&action=<?php echo $cli_status ? "desativar" : "ativar"; ?>&cli_id=<?php echo $cli_id; ?>'><img
										border='0' src='../imagens/icon-ativa-desativa.png'></a>
								<a href='cadastro_clientes.php?pagina=editar_cadastro_clientes&cli_id=<?php echo $cli_id; ?>'><img
										border='0' src='../imagens/icon-editar.png'></a>
							</td>
						</tr>
					<?php endforeach; ?>
				</table> <?php
				renderPagination($totalClientes, $numPorPagina, $pag, 'cadastro_clientes.php', [
					'fil_nome' => $fil_nome,
					'fil_cli_cnpj' => $fil_cli_cnpj
				]);
				?> 	<?php else: ?>
				<br><br><br>Não há nenhum cliente cadastrado.
			<?php endif; ?>
			<div class='titulo'></div>
		</div>
	<?php endif; ?>

	<?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>