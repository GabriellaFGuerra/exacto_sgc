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

function abreMaskMsg($img, $msg, $okBtn = true, $backBtn = false)
{
	$btn = $okBtn ? "<input value=' Ok ' type='button' class='close_janela'>" : '';
	if ($backBtn) {
		$btn = "<input value=' Ok ' type='button' onclick=javascript:window.history.back();>";
	}
	return "
	<script>
		abreMask(
			'<img src=../imagens/$img.png> $msg<br><br>$btn'
		);
	</script>
	";
}

// Ações
$action = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? $_POST['pagina'] ?? '';
$pag = $_GET['pag'] ?? $_POST['pag'] ?? 1;
$autenticacao = $autenticacao ?? '';
$fil_nome = $_REQUEST['fil_nome'] ?? '';

if ($action === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$ger_nome = $_POST['ger_nome'] ?? '';
	$stmt = $pdo->prepare('INSERT INTO cadastro_gerentes (ger_nome) VALUES (:ger_nome)');
	if ($stmt->execute([':ger_nome' => $ger_nome])) {
		echo abreMaskMsg('ok', 'Cadastro efetuado com sucesso.');
	} else {
		echo abreMaskMsg('x', 'Erro ao efetuar cadastro, por favor tente novamente.', false, true);
	}
}

if ($action === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
	$ger_id = $_GET['ger_id'] ?? $_POST['ger_id'] ?? '';
	$ger_nome = $_POST['ger_nome'] ?? '';
	$stmt = $pdo->prepare('UPDATE cadastro_gerentes SET ger_nome = :ger_nome WHERE ger_id = :ger_id');
	if ($stmt->execute([':ger_nome' => $ger_nome, ':ger_id' => $ger_id])) {
		echo abreMaskMsg('ok', 'Dados alterados com sucesso.');
	} else {
		echo abreMaskMsg('x', 'Erro ao alterar dados, por favor tente novamente.', false, true);
	}
}

if ($action === 'excluir') {
	$ger_id = $_GET['ger_id'] ?? '';
	$stmt = $pdo->prepare('DELETE FROM cadastro_gerentes WHERE ger_id = :ger_id');
	if ($stmt->execute([':ger_id' => $ger_id])) {
		echo abreMaskMsg('ok', 'Exclusão realizada com sucesso');
	} else {
		echo abreMaskMsg('x', 'Este item não pode ser excluído pois está relacionado com alguma tabela.', false, true);
	}
}

// Filtro e paginação
$num_por_pagina = 10;
$primeiro_registro = ($pag - 1) * $num_por_pagina;
$nome_query = $fil_nome !== '' ? 'ger_nome LIKE :fil_nome' : '1=1';
$params = $fil_nome !== '' ? [':fil_nome' => "%$fil_nome%"] : [];

if ($pagina === 'cadastro_gerentes') {
	// Listagem
	$sql = "SELECT * FROM cadastro_gerentes WHERE $nome_query ORDER BY ger_nome ASC LIMIT $primeiro_registro, $num_por_pagina";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	$gerentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$cnt_sql = "SELECT COUNT(*) FROM cadastro_gerentes WHERE $nome_query";
	$cnt_stmt = $pdo->prepare($cnt_sql);
	$cnt_stmt->execute($params);
	$total = $cnt_stmt->fetchColumn();

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
		<div id='botoes'><input value='Novo Gerente' type='button' onclick=\"window.location.href='cadastro_gerentes.php?pagina=adicionar_cadastro_gerentes$autenticacao';\" /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' method='post' action='cadastro_gerentes.php?pagina=cadastro_gerentes$autenticacao'>
				<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Nome'>
				<input type='submit' value='Filtrar'> 
			</form>
		</div>
	";

	if ($total > 0) {
		echo "
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Nome</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>
		";
		$c = 0;
		foreach ($gerentes as $gerente) {
			$ger_id = $gerente['ger_id'];
			$ger_nome = htmlspecialchars($gerente['ger_nome']);
			$c1 = $c++ % 2 == 0 ? 'linhaimpar' : 'linhapar';
			echo "
			<script>
				jQuery(function($) {
					$('#normal-button-$ger_id').toolbar({content: '#user-options-$ger_id', position: 'top', hideOnClick: true});
				});
			</script>
			<div id='user-options-$ger_id' class='toolbar-icons' style='display: none;'>
				<a href='cadastro_gerentes.php?pagina=editar_cadastro_gerentes&ger_id=$ger_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
				<a onclick=\"
					abreMask(
						'Deseja realmente excluir o gerente <b>$ger_nome</b>?<br><br>'+
						'<input value=\' Sim \' type=\'button\' onclick=window.location.href=\\'cadastro_gerentes.php?pagina=cadastro_gerentes&action=excluir&ger_id=$ger_id$autenticacao\\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\' Não \' type=\'button\' class=\'close_janela\'>'
					);
				\">
					<img border='0' src='../imagens/icon-excluir.png'>
				</a>
			</div>
			<tr class='$c1'>
				<td>$ger_nome</td>
				<td align='center'><div id='normal-button-$ger_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
			</tr>
			";
		}
		echo '</table>';
		$variavel = "&pagina=cadastro_gerentes$autenticacao";
		include '../mod_includes/php/paginacao.php';
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
	$ger_id = $_GET['ger_id'] ?? '';
	$stmt = $pdo->prepare('SELECT * FROM cadastro_gerentes WHERE ger_id = :ger_id');
	$stmt->execute([':ger_id' => $ger_id]);
	$gerente = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($gerente) {
		$ger_nome = htmlspecialchars($gerente['ger_nome']);
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
		<form name='form_cadastro_gerentes' id='form_cadastro_gerentes' method='post' action='cadastro_gerentes.php?pagina=cadastro_gerentes&action=editar&ger_id=$ger_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $ger_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='ger_id' id='ger_id' value='$ger_id'>
						<input name='ger_nome' id='ger_nome' value='$ger_nome' placeholder='Nome'>
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