<?php
session_start();
$pagina_link = 'cadastro_tipos_servicos';
include '../mod_includes/php/connect.php';

function renderHeader($titulo = '')
{
	?>
	<!DOCTYPE html>
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title><?= htmlspecialchars($titulo) ?></title>
		<meta name="author" content="MogiComp">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="shortcut icon" href="../imagens/favicon.png">
		<?php include '../css/style.php'; ?>
		<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
		<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
		<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
		<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
		<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
	</head>
	<body>
	<?php
}

function renderFooter()
{
	include '../mod_rodape/rodape.php';
	echo '</body></html>';
}

function showMessage($success, $msg)
{
	$icon = $success ? 'ok.png' : 'x.png';
	echo "<script>abreMask('<img src=../imagens/$icon> $msg<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>');</script>";
}

function redirectBackWithError($msg)
{
	echo "<script>abreMask('<img src=../imagens/x.png> $msg<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');</script>";
}

function getPagination($total, $perPage, $currentPage, $baseUrl)
{
	$totalPages = ceil($total / $perPage);
	if ($totalPages <= 1) return '';

	$pagination = "<div class='pagination'>";
	for ($i = 1; $i <= $totalPages; $i++) {
		$active = $i == $currentPage ? "style='font-weight:bold;'" : '';
		$pagination .= "<a href='{$baseUrl}&pag={$i}' $active>{$i}</a> ";
	}
	$pagination .= "</div>";
	return $pagination;
}

// Includes
include '../mod_includes/php/funcoes-jquery.php';
require_once '../mod_includes/php/verificalogin.php';
include '../mod_topo/topo.php';
require_once '../mod_includes/php/verificapermissao.php';

// Variáveis
$pageTitle = "Cadastros &raquo; <a href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos$autenticacao'>Tipos de Serviço</a>";
$action = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$pag = isset($_GET['pag']) ? (int)$_GET['pag'] : 1;
$numPorPagina = 20;
$primeiroRegistro = ($pag - 1) * $numPorPagina;

// Ações
if ($action === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$tps_nome = trim($_POST['tps_nome'] ?? '');
	$stmt = $pdo->prepare('INSERT INTO cadastro_tipos_servicos (tps_nome) VALUES (:tps_nome)');
	if ($stmt->execute([':tps_nome' => $tps_nome])) {
		showMessage(true, 'Cadastro efetuado com sucesso.');
	} else {
		redirectBackWithError('Erro ao efetuar cadastro, por favor tente novamente.');
	}
}

if ($action === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$tps_id = $_GET['tps_id'] ?? '';
	$tps_nome = trim($_POST['tps_nome'] ?? '');
	$stmt = $pdo->prepare('UPDATE cadastro_tipos_servicos SET tps_nome = :tps_nome WHERE tps_id = :tps_id');
	if ($stmt->execute([':tps_nome' => $tps_nome, ':tps_id' => $tps_id])) {
		showMessage(true, 'Dados alterados com sucesso.');
	} else {
		redirectBackWithError('Erro ao alterar dados, por favor tente novamente.');
	}
}

if ($action === 'excluir') {
	$tps_id = $_GET['tps_id'] ?? '';
	$stmt = $pdo->prepare('DELETE FROM cadastro_tipos_servicos WHERE tps_id = :tps_id');
	if ($stmt->execute([':tps_id' => $tps_id])) {
		showMessage(true, 'Exclusão realizada com sucesso');
	} else {
		redirectBackWithError('Este tipo de serviço não pode ser excluído pois está relacionado com alguma tabela.');
	}
}

// Renderização
renderHeader('Tipos de Serviço');

if ($pagina === 'cadastro_tipos_servicos') {
	$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome ASC LIMIT :offset, :limit');
	$stmt->bindValue(':offset', $primeiroRegistro, PDO::PARAM_INT);
	$stmt->bindValue(':limit', $numPorPagina, PDO::PARAM_INT);
	$stmt->execute();
	$servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$total = $pdo->query('SELECT COUNT(*) FROM cadastro_tipos_servicos')->fetchColumn();

	echo "<div class='centro'>
		<div class='titulo'> $pageTitle </div>
		<div id='botoes'><input value='Novo Serviço' type='button' onclick=\"window.location.href='cadastro_tipos_servicos.php?pagina=adicionar_cadastro_tipos_servicos$autenticacao';\" /></div>";

	if ($servicos) {
		echo "<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Serviço</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>";
		foreach ($servicos as $index => $servico) {
			$tps_id = $servico['tps_id'];
			$tps_nome = htmlspecialchars($servico['tps_nome']);
			$rowClass = $index % 2 == 0 ? 'linhaimpar' : 'linhapar';
			?>
			<script type='text/javascript'>
				jQuery(document).ready(function($) {
					$('#normal-button-<?= $tps_id ?>').toolbar({content: '#user-options-<?= $tps_id ?>', position: 'top', hideOnClick: true});
				});
			</script>
			<div id='user-options-<?= $tps_id ?>' class='toolbar-icons' style='display: none;'>
				<a href='cadastro_tipos_servicos.php?pagina=editar_cadastro_tipos_servicos&tps_id=<?= $tps_id . $autenticacao ?>'><img border='0' src='../imagens/icon-editar.png'></a>
				<a onclick="
					abreMask(
						'Deseja realmente excluir o tipo de serviço <b><?= addslashes($tps_nome) ?></b>?<br><br>'+
						'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos&action=excluir&tps_id=<?= $tps_id . $autenticacao ?>\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\' Não \' type=\'button\' class=\'close_janela\'' );
					">
					<img border='0' src='../imagens/icon-excluir.png'>
				</a>
			</div>
			<tr class='<?= $rowClass ?>'>
				<td><?= $tps_nome ?></td>
				<td align=center><div id='normal-button-<?= $tps_id ?>' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
			</tr>
			<?php
		}
		echo '</table>';
		$baseUrl = "cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos$autenticacao";
		echo getPagination($total, $numPorPagina, $pag, $baseUrl);
	} else {
		echo '<br><br><br>Não há nenhum tipo de serviço cadastrado.';
	}
	echo "<div class='titulo'></div></div>";
}

if ($pagina === 'adicionar_cadastro_tipos_servicos') {
	?>
	<form name='form_cadastro_tipos_servicos' id='form_cadastro_tipos_servicos' enctype='multipart/form-data' method='post' action='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos&action=adicionar<?= $autenticacao ?>'>
		<div class='centro'>
			<div class='titulo'> <?= $pageTitle ?> &raquo; Adicionar </div>
			<table align='center' cellspacing='0' width='500'>
				<tr>
					<td align='center'>
						<input name='tps_nome' id='tps_nome' placeholder='Nome do Serviço'>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='submit' id='bt_cadastro_tipos_servicos' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick="window.location.href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos<?= $autenticacao ?>';" value='Cancelar'/>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'></div>
		</div>
	</form>
	<?php
}

if ($pagina === 'editar_cadastro_tipos_servicos') {
	$tps_id = $_GET['tps_id'] ?? '';
	$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_servicos WHERE tps_id = :tps_id');
	$stmt->execute([':tps_id' => $tps_id]);
	$servico = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($servico) {
		$tps_nome = htmlspecialchars($servico['tps_nome']);
		?>
		<form name='form_cadastro_tipos_servicos' id='form_cadastro_tipos_servicos' enctype='multipart/form-data' method='post' action='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos&action=editar&tps_id=<?= $tps_id . $autenticacao ?>'>
			<div class='centro'>
				<div class='titulo'> <?= $pageTitle ?> &raquo; Editar: <?= $tps_nome ?> </div>
				<table align='center' cellspacing='0'>
					<tr>
						<td align='left'>
							<input name='tps_nome' id='tps_nome' value="<?= $tps_nome ?>" placeholder='Nome do Serviço'>
							<p>
							<center>
							<div id='erro' align='center'>&nbsp;</div>
							<input type='submit' id='bt_cadastro_tipos_servicos' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
							<input type='button' id='botao_cancelar' onclick="window.location.href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos<?= $autenticacao ?>';" value='Cancelar'/>
							</center>
						</td>
					</tr>
				</table>
				<div class='titulo'></div>
			</div>
		</form>
		<?php
	}
}

renderFooter();