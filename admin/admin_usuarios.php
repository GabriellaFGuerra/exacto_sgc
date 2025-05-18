<?php
session_start();
$pagina_link = 'admin_usuarios';
include '../mod_includes/php/connect.php';

function getUsuarios($pdo, $offset, $limit)
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

function renderStatusIcon($status)
{
	$icon = $status == 1 ? 'icon-ativo.png' : 'icon-inativo.png';
	$alt = $status == 1 ? 'Ativo' : 'Inativo';
	return "<img src='../imagens/$icon' width='15' height='15' alt='$alt'>";
}

function renderNotificacaoIcon($notificacao)
{
	$icon = $notificacao == 1 ? 'ok.png' : 'x.png';
	$alt = $notificacao == 1 ? 'Sim' : 'Não';
	return "<img src='../imagens/$icon' width='15' height='15' alt='$alt'>";
}

function renderUserActions($usu_id, $usu_nome, $usu_status, $autenticacao)
{
	$toggleStatusAction = $usu_status == 1 ? 'desativar' : 'ativar';
	$toggleStatusIcon = "<a href='admin_usuarios.php?pagina=admin_usuarios&action=$toggleStatusAction&usu_id=$usu_id$autenticacao'><img src='../imagens/icon-ativa-desativa.png'></a>";
	$editIcon = "<a href='admin_usuarios.php?pagina=editar_admin_usuarios&usu_id=$usu_id$autenticacao'><img src='../imagens/icon-editar.png'></a>";
	$deleteIcon = "<a onclick=\"abreMask('Deseja realmente excluir o usuário <b>$usu_nome</b>?<br><br><input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'admin_usuarios.php?pagina=admin_usuarios&action=excluir&usu_id=$usu_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input value=\' Não \' type=\'button\' class=\'close_janela\'');\"><img src='../imagens/icon-excluir.png'></a>";
	return "$toggleStatusIcon $editIcon $deleteIcon";
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
	$c = 0;
	foreach ($usuarios as $usuario) {
		$c1 = $c++ % 2 === 0 ? "linhaimpar" : "linhapar";
		$html .= "<tr class='$c1'>
			<td>" . htmlspecialchars($usuario['usu_nome']) . "</td>
			<td>" . htmlspecialchars($usuario['usu_email']) . "</td>
			<td>" . htmlspecialchars($usuario['set_nome']) . "</td>
			<td>" . htmlspecialchars($usuario['usu_login']) . "</td>
			<td align='center'>" . renderStatusIcon($usuario['usu_status']) . "</td>
			<td align='center'>" . renderNotificacaoIcon($usuario['usu_notificacao']) . "</td>
			<td align='center'>" . renderUserActions($usuario['usu_id'], htmlspecialchars($usuario['usu_nome']), $usuario['usu_status'], $autenticacao) . "</td>
		</tr>";
	}
	$html .= "</table>";
	return $html;
}

// Exemplo de uso na listagem:
$num_por_pagina = 10;
$pag = $_GET['pag'] ?? 1;
$primeiro_registro = ($pag - 1) * $num_por_pagina;
$usuarios = getUsuarios($pdo, $primeiro_registro, $num_por_pagina);

if ($pagina === "admin_usuarios") {
	echo "<div class='centro'>
		<div class='titulo'> $page </div>
		<div id='botoes'><input value='Novo Usuário' type='button' onclick=javascript:window.location.href='admin_usuarios.php?pagina=adicionar_admin_usuarios$autenticacao'; /></div>";
	if (count($usuarios) > 0) {
		echo renderUsuariosTable($usuarios, $autenticacao);
		$variavel = "&pagina=admin_usuarios$autenticacao";
		include "../mod_includes/php/paginacao.php";
	} else {
		echo "<br><br><br>Não há nenhum usuário cadastrado.";
	}
	echo "<div class='titulo'>  </div></div>";
}
?>