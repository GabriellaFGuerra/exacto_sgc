<?php
session_start();
$pagina_link = 'admin_usuarios';
include '../mod_includes/php/connect.php';

define('USERS_PER_PAGE', 10);

function fetchUsuarios($pdo, $offset, $limit)
{
	$sql = "SELECT * FROM admin_usuarios 
			LEFT JOIN admin_setores ON admin_setores.set_id = admin_usuarios.usu_setor
			ORDER BY usu_nome ASC
			LIMIT :offset, :limit";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
	$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countUsuarios($pdo)
{
	$sql = "SELECT COUNT(*) FROM admin_usuarios";
	return $pdo->query($sql)->fetchColumn();
}

function statusIcon($status)
{
	$icon = $status == 1 ? 'icon-ativo.png' : 'icon-inativo.png';
	$alt = $status == 1 ? 'Ativo' : 'Inativo';
	return "<img src='../imagens/$icon' width='15' height='15' alt='$alt'>";
}

function notificacaoIcon($notificacao)
{
	$icon = $notificacao == 1 ? 'ok.png' : 'x.png';
	$alt = $notificacao == 1 ? 'Sim' : 'Não';
	return "<img src='../imagens/$icon' width='15' height='15' alt='$alt'>";
}

function userActions($usu_id, $usu_nome, $usu_status, $autenticacao)
{
	$toggleAction = $usu_status == 1 ? 'desativar' : 'ativar';
	$toggleIcon = "<a href='admin_usuarios.php?pagina=admin_usuarios&action=$toggleAction&usu_id=$usu_id$autenticacao'><img src='../imagens/icon-ativa-desativa.png'></a>";
	$editIcon = "<a href='admin_usuarios.php?pagina=editar_admin_usuarios&usu_id=$usu_id$autenticacao'><img src='../imagens/icon-editar.png'></a>";
	$deleteIcon = "<a onclick=\"abreMask('Deseja realmente excluir o usuário <b>" . htmlspecialchars($usu_nome) . "</b>?<br><br><input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'admin_usuarios.php?pagina=admin_usuarios&action=excluir&usu_id=$usu_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input value=\' Não \' type=\'button\' class=\'close_janela\'');\"><img src='../imagens/icon-excluir.png'></a>";
	return "$toggleIcon $editIcon $deleteIcon";
}

function renderUsuariosTable($usuarios, $autenticacao)
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
			<td>" . htmlspecialchars($usuario['usu_nome']) . "</td>
			<td>" . htmlspecialchars($usuario['usu_email']) . "</td>
			<td>" . htmlspecialchars($usuario['set_nome']) . "</td>
			<td>" . htmlspecialchars($usuario['usu_login']) . "</td>
			<td align='center'>" . statusIcon($usuario['usu_status']) . "</td>
			<td align='center'>" . notificacaoIcon($usuario['usu_notificacao']) . "</td>
			<td align='center'>" . userActions($usuario['usu_id'], $usuario['usu_nome'], $usuario['usu_status'], $autenticacao) . "</td>
		</tr>";
	}
	$html .= "</table>";
	return $html;
}

function renderPagination($currentPage, $totalPages, $baseUrl)
{
	if ($totalPages <= 1)
		return '';
	$html = "<div class='pagination'>";
	for ($i = 1; $i <= $totalPages; $i++) {
		$active = $i == $currentPage ? "style='font-weight:bold;'" : '';
		$html .= "<a href='{$baseUrl}&pag={$i}' $active>{$i}</a> ";
	}
	$html .= "</div>";
	return $html;
}

// --- Página principal ---
$autenticacao = $_GET['autenticacao'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$pageTitle = 'Usuários';
$currentPage = isset($_GET['pag']) ? max(1, intval($_GET['pag'])) : 1;
$offset = ($currentPage - 1) * USERS_PER_PAGE;

$totalUsuarios = countUsuarios($pdo);
$totalPages = ceil($totalUsuarios / USERS_PER_PAGE);

$usuarios = fetchUsuarios($pdo, $offset, USERS_PER_PAGE);

if ($pagina === "admin_usuarios") {
	echo "<div class='centro'>
		<div class='titulo'> $pageTitle </div>
		<div id='botoes'><input value='Novo Usuário' type='button' onclick=\"window.location.href='admin_usuarios.php?pagina=adicionar_admin_usuarios$autenticacao';\" /></div>";

	if ($usuarios) {
		echo renderUsuariosTable($usuarios, $autenticacao);
		$baseUrl = "admin_usuarios.php?pagina=admin_usuarios$autenticacao";
		echo renderPagination($currentPage, $totalPages, $baseUrl);
	} else {
		echo "<br><br><br>Não há nenhum usuário cadastrado.";
	}
	echo "<div class='titulo'>  </div></div>";
}
?>