<?php
session_start();
$pagina_link = 'cadastro_gerentes';

require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';
require_once '../mod_includes/php/funcoes-jquery.php';
require_once '../mod_topo/topo.php';

$titulo = $titulo ?? 'Cadastro de Gerentes';
$page = "Cadastros &raquo; <a href='cadastro_gerentes.php?pagina=cadastro_gerentes$autenticacao'>Gerentes</a>";

function showMaskMessage($icon, $message, $okBtn = true, $backBtn = false)
{
	$button = $okBtn ? "<input value=' Ok ' type='button' class='close_janela'>" : '';
	if ($backBtn) {
		$button = "<input value=' Ok ' type='button' onclick=javascript:window.history.back();>";
	}
	return "
	<script>
		abreMask(
			'<img src=../imagens/$icon.png> $message<br><br>$button'
		);
	</script>
	";
}

function getRequestParam($name, $default = '')
{
	return $_GET[$name] ?? $_POST[$name] ?? $_REQUEST[$name] ?? $default;
}

// Actions
$action = getRequestParam('action');
$pagina = getRequestParam('pagina');
$pag = (int) getRequestParam('pag', 1);
$autenticacao = $autenticacao ?? '';
$filterName = trim(getRequestParam('fil_nome'));

if ($action === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$gerenteNome = trim($_POST['ger_nome'] ?? '');
	$stmt = $pdo->prepare('INSERT INTO cadastro_gerentes (ger_nome) VALUES (:ger_nome)');
	if ($stmt->execute([':ger_nome' => $gerenteNome])) {
		echo showMaskMessage('ok', 'Cadastro efetuado com sucesso.');
	} else {
		echo showMaskMessage('x', 'Erro ao efetuar cadastro, por favor tente novamente.', false, true);
	}
	exit;
}

if ($action === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$gerenteId = getRequestParam('ger_id');
	$gerenteNome = trim($_POST['ger_nome'] ?? '');
	$stmt = $pdo->prepare('UPDATE cadastro_gerentes SET ger_nome = :ger_nome WHERE ger_id = :ger_id');
	if ($stmt->execute([':ger_nome' => $gerenteNome, ':ger_id' => $gerenteId])) {
		echo showMaskMessage('ok', 'Dados alterados com sucesso.');
	} else {
		echo showMaskMessage('x', 'Erro ao alterar dados, por favor tente novamente.', false, true);
	}
	exit;
}

if ($action === 'excluir') {
	$gerenteId = getRequestParam('ger_id');
	$stmt = $pdo->prepare('DELETE FROM cadastro_gerentes WHERE ger_id = :ger_id');
	if ($stmt->execute([':ger_id' => $gerenteId])) {
		echo showMaskMessage('ok', 'Exclusão realizada com sucesso');
	} else {
		echo showMaskMessage('x', 'Este item não pode ser excluído pois está relacionado com alguma tabela.', false, true);
	}
	exit;
}

// Pagination and Filter
$recordsPerPage = 10;
$offset = ($pag - 1) * $recordsPerPage;
$whereClause = $filterName !== '' ? 'ger_nome LIKE :filterName' : '1=1';
$params = $filterName !== '' ? [':filterName' => "%$filterName%"] : [];

if ($pagina === 'cadastro_gerentes') {
	// List
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

	echo "
	<!DOCTYPE html>
	<html>
	<head>
		<title>$titulo</title>
		<meta name='author' content='MogiComp'>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		<link rel='shortcut icon' href='../imagens/favicon.png'>
	";
	include '../css/style.php';
	echo "
		<script src='../mod_includes/js/funcoes.js'></script>
		<script src='../mod_includes/js/jquery-1.8.3.min.js'></script>
		<link href='../mod_includes/js/toolbar/jquery.toolbars.css' rel='stylesheet' />
		<link href='../mod_includes/js/toolbar/bootstrap.icons.css' rel='stylesheet'>
		<script src='../mod_includes/js/toolbar/jquery.toolbar.js'></script>
	</head>
	<body>
	<div class='centro'>
		<div class='titulo'> $page </div>
		<div id='botoes'>
			<input value='Novo Gerente' type='button' onclick=\"window.location.href='cadastro_gerentes.php?pagina=adicionar_cadastro_gerentes$autenticacao';\" />
		</div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' method='post' action='cadastro_gerentes.php?pagina=cadastro_gerentes$autenticacao'>
				<input name='fil_nome' id='fil_nome' value='$filterName' placeholder='Nome'>
				<input type='submit' value='Filtrar'> 
			</form>
		</div>
	";

	if ($totalRecords > 0) {
		echo "
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Nome</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>
		";
		foreach ($gerentes as $index => $gerente) {
			$gerenteId = $gerente['ger_id'];
			$gerenteNome = htmlspecialchars($gerente['ger_nome']);
			$rowClass = $index % 2 === 0 ? 'linhaimpar' : 'linhapar';
			echo "
			<script>
				jQuery(function($) {
					$('#normal-button-$gerenteId').toolbar({content: '#user-options-$gerenteId', position: 'top', hideOnClick: true});
				});
			</script>
			<div id='user-options-$gerenteId' class='toolbar-icons' style='display: none;'>
				<a href='cadastro_gerentes.php?pagina=editar_cadastro_gerentes&ger_id=$gerenteId$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
				<a onclick=\"
					abreMask(
						'Deseja realmente excluir o gerente <b>$gerenteNome</b>?<br><br>'+
						'<input value=\' Sim \' type=\'button\' onclick=window.location.href=\\'cadastro_gerentes.php?pagina=cadastro_gerentes&action=excluir&ger_id=$gerenteId$autenticacao\\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\' Não \' type=\'button\' class=\'close_janela\'>'
					);
				\">
					<img border='0' src='../imagens/icon-excluir.png'>
				</a>
			</div>
			<tr class='$rowClass'>
				<td>$gerenteNome</td>
				<td align='center'><div id='normal-button-$gerenteId' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
			</tr>
			";
		}
		echo '</table>';

		// Pagination
		$totalPages = ceil($totalRecords / $recordsPerPage);
		if ($totalPages > 1) {
			echo "<div class='paginacao'>";
			for ($i = 1; $i <= $totalPages; $i++) {
				$activeClass = $i == $pag ? "style='font-weight:bold;'" : '';
				$url = "cadastro_gerentes.php?pagina=cadastro_gerentes&pag=$i$autenticacao";
				echo "<a href='$url' $activeClass>[$i]</a> ";
			}
			echo "</div>";
		}
	} else {
		echo '<br><br><br>Não há nenhum gerente cadastrado.';
	}
	echo "<div class='titulo'></div></div>";
	include '../mod_rodape/rodape.php';
	echo '</body></html>';
	exit;
}

if ($pagina === 'adicionar_cadastro_gerentes') {
	echo "
	<!DOCTYPE html>
	<html>
	<head>
		<title>$titulo</title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	";
	include '../css/style.php';
	echo "
	</head>
	<body>
	<form name='form_cadastro_gerentes' id='form_cadastro_gerentes' method='post' action='cadastro_gerentes.php?pagina=cadastro_gerentes&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar </div>
		<table align='center' cellspacing='0' width='680'>
			<tr>
				<td align='left'>
					<input name='ger_nome' id='ger_nome' placeholder='Nome'>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='submit' id='bt_cadastro_gerentes' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=\"window.location.href='cadastro_gerentes.php?pagina=cadastro_gerentes$autenticacao';\" value='Cancelar'/>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'></div>
	</div>
	</form>
	";
	include '../mod_rodape/rodape.php';
	echo '</body></html>';
	exit;
}

if ($pagina === 'editar_cadastro_gerentes') {
	$gerenteId = getRequestParam('ger_id');
	$stmt = $pdo->prepare('SELECT * FROM cadastro_gerentes WHERE ger_id = :ger_id');
	$stmt->execute([':ger_id' => $gerenteId]);
	$gerente = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($gerente) {
		$gerenteNome = htmlspecialchars($gerente['ger_nome']);
		echo "
		<!DOCTYPE html>
		<html>
		<head>
			<title>$titulo</title>
			<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
		";
		include '../css/style.php';
		echo "
		</head>
		<body>
		<form name='form_cadastro_gerentes' id='form_cadastro_gerentes' method='post' action='cadastro_gerentes.php?pagina=cadastro_gerentes&action=editar&ger_id=$gerenteId$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $gerenteNome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='ger_id' id='ger_id' value='$gerenteId'>
						<input name='ger_nome' id='ger_nome' value='$gerenteNome' placeholder='Nome'>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='submit' id='bt_cadastro_gerentes' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=\"window.location.href='cadastro_gerentes.php?pagina=cadastro_gerentes$autenticacao';\" value='Cancelar'/>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'></div>
		</div>
		</form>
		";
		include '../mod_rodape/rodape.php';
		echo '</body></html>';
	}
	exit;
}
?>