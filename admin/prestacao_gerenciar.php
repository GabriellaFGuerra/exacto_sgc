<?php
session_start();
$pagina_link = 'prestacao_gerenciar';
include('../mod_includes/php/connect.php');

require_once('../mod_includes/php/verificalogin.php');
require_once('../mod_includes/php/verificapermissao.php');
include('../mod_includes/php/funcoes-jquery.php');
include('../mod_topo/topo.php');

$titulo = $titulo ?? 'Prestação de Contas';

function abreMask($msg)
{
	echo "<script>abreMask(`$msg`);</script>";
}

function abreMaskAcao($msg)
{
	echo "<script>abreMaskAcao(`$msg`);</script>";
}

$page = "Prestação de Contas &raquo; <a href='prestacao_gerenciar.php?pagina=prestacao_gerenciar'>Gerenciar</a>";

$action = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? 'prestacao_gerenciar';
$autenticacao = $_GET['autenticacao'] ?? '';
$pag = isset($_GET['pag']) ? (int) $_GET['pag'] : 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Adicionar
	if ($action === "adicionar") {
		$pre_cliente = $_POST['pre_cliente_id'] ?? '';
		$pre_referencia = ($_POST['pre_ref_mes'] ?? '') . '/' . ($_POST['pre_ref_ano'] ?? '');
		$pre_data_envio = DateTime::createFromFormat('d/m/Y', $_POST['pre_data_envio'] ?? '');
		$pre_data_envio = $pre_data_envio ? $pre_data_envio->format('Y-m-d') : null;
		$pre_enviado_por = $_POST['pre_enviado_por'] ?? '';
		$pre_observacoes = $_POST['pre_observacoes'] ?? '';

		$stmt = $pdo->prepare("INSERT INTO prestacao_gerenciar (pre_cliente, pre_referencia, pre_data_envio, pre_enviado_por, pre_observacoes) VALUES (?, ?, ?, ?, ?)");
		if ($stmt->execute([$pre_cliente, $pre_referencia, $pre_data_envio, $pre_enviado_por, $pre_observacoes])) {
			abreMask("<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			abreMask("<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	// Editar
	if ($action === 'editar') {
		$pre_id = $_GET['pre_id'] ?? '';
		$pre_referencia = ($_POST['pre_ref_mes'] ?? '') . '/' . ($_POST['pre_ref_ano'] ?? '');
		$pre_data_envio = DateTime::createFromFormat('d/m/Y', $_POST['pre_data_envio'] ?? '');
		$pre_data_envio = $pre_data_envio ? $pre_data_envio->format('Y-m-d') : null;
		$pre_enviado_por = $_POST['pre_enviado_por'] ?? '';
		$pre_observacoes = $_POST['pre_observacoes'] ?? '';

		$stmt = $pdo->prepare("UPDATE prestacao_gerenciar SET pre_referencia = ?, pre_data_envio = ?, pre_enviado_por = ?, pre_observacoes = ? WHERE pre_id = ?");
		if ($stmt->execute([$pre_referencia, $pre_data_envio, $pre_enviado_por, $pre_observacoes, $pre_id])) {
			abreMask("<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			abreMask("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	// Comprovante
	if ($action === 'comprovante') {
		$erro = false;
		$pre_id = $_GET['pre_id'] ?? '';
		$arquivos = $_FILES['pre_comprovante'] ?? null;
		$caminho = "../admin/prestacao_comprovante/$pre_id/";
		if (!is_dir($caminho)) {
			mkdir($caminho, 0755, true);
		}
		$arquivo_final = '';
		if ($arquivos && is_array($arquivos['name'])) {
			foreach ($arquivos['name'] as $k => $nome) {
				if ($nome) {
					$extensao = pathinfo($nome, PATHINFO_EXTENSION);
					$arquivo = $caminho . md5(mt_rand(1, 10000) . $nome) . '.' . $extensao;
					if (move_uploaded_file($arquivos['tmp_name'][$k], $arquivo)) {
						$arquivo_final = $arquivo;
					} else {
						$erro = true;
					}
				}
			}
			if ($arquivo_final) {
				$stmt = $pdo->prepare("UPDATE prestacao_gerenciar SET pre_comprovante = ? WHERE pre_id = ?");
				if (!$stmt->execute([$arquivo_final, $pre_id])) {
					$erro = true;
				}
			}
		}
		if (!$erro) {
			abreMask("<img src=../imagens/ok.png> Anexo enviado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			abreMask("<img src=../imagens/x.png> Erro ao enviar anexo.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}
}

// Excluir
if ($action === 'excluir') {
	$pre_id = $_GET['pre_id'] ?? '';
	$stmt = $pdo->prepare("DELETE FROM prestacao_gerenciar WHERE pre_id = ?");
	if ($stmt->execute([$pre_id])) {
		abreMask("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

// Ativar/Desativar
if ($action === 'ativar' || $action === 'desativar') {
	$pre_id = $_GET['pre_id'] ?? '';
	$status = $action === 'ativar' ? 1 : 0;
	$stmt = $pdo->prepare("UPDATE prestacao_gerenciar SET pre_status = ? WHERE pre_id = ?");
	if ($stmt->execute([$status, $pre_id])) {
		$msg = $status ? 'Ativação' : 'Desativação';
		abreMask("<img src=../imagens/ok.png> $msg realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

// Filtros
$num_por_pagina = 10;
$primeiro_registro = ($pag - 1) * $num_por_pagina;

$fil_prestacao = $_REQUEST['fil_prestacao'] ?? '';
$fil_nome = $_REQUEST['fil_nome'] ?? '';
$fil_referencia = $_REQUEST['fil_referencia'] ?? '';
$fil_data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
$fil_data_fim = $_REQUEST['fil_data_fim'] ?? '';

$where = [];
$params = [];

$where[] = "cli_status = 1 AND cli_deletado = 1 AND ucl_usuario = ?";
$params[] = $_SESSION['usuario_id'];

if ($fil_prestacao) {
	$where[] = "pre_id = ?";
	$params[] = $fil_prestacao;
}
if ($fil_nome) {
	$where[] = "cli_nome_razao LIKE ?";
	$params[] = "%$fil_nome%";
}
if ($fil_referencia) {
	$where[] = "pre_referencia = ?";
	$params[] = $fil_referencia;
}
if ($fil_data_inicio || $fil_data_fim) {
	$data_inicio = $fil_data_inicio ? DateTime::createFromFormat('d/m/Y', $fil_data_inicio)->format('Y-m-d') : null;
	$data_fim = $fil_data_fim ? DateTime::createFromFormat('d/m/Y', $fil_data_fim)->format('Y-m-d 23:59:59') : null;
	if ($data_inicio && $data_fim) {
		$where[] = "pre_data_cadastro BETWEEN ? AND ?";
		$params[] = $data_inicio;
		$params[] = $data_fim;
	} elseif ($data_inicio) {
		$where[] = "pre_data_cadastro >= ?";
		$params[] = $data_inicio;
	} elseif ($data_fim) {
		$where[] = "pre_data_cadastro <= ?";
		$params[] = $data_fim;
	}
}

$where_sql = implode(' AND ', $where);

// Listagem
if ($pagina === "prestacao_gerenciar") {
	$sql = "SELECT * FROM prestacao_gerenciar 
		LEFT JOIN (cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
		WHERE $where_sql
		ORDER BY pre_data_cadastro DESC
		LIMIT $primeiro_registro, $num_por_pagina";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$cnt_sql = "SELECT COUNT(*) FROM prestacao_gerenciar 
		LEFT JOIN (cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
		WHERE $where_sql";
	$cnt_stmt = $pdo->prepare($cnt_sql);
	$cnt_stmt->execute($params);
	$total = $cnt_stmt->fetchColumn();

	echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
<title>$titulo</title>
<meta name='author' content='MogiComp'>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<link rel='shortcut icon' href='../imagens/favicon.png'>
";
	include("../css/style.php");
	echo "
<script src='../mod_includes/js/funcoes.js'></script>
<script src='../mod_includes/js/jquery-1.8.3.min.js'></script>
<link href='../mod_includes/js/toolbar/jquery.toolbars.css' rel='stylesheet' />
<link href='../mod_includes/js/toolbar/bootstrap.icons.css' rel='stylesheet'>
<script src='../mod_includes/js/toolbar/jquery.toolbar.js'></script>
</head>
<body>
<div class='centro'>
	<div class='titulo'> $page  </div>
	<div id='botoes'><input value='Nova Prestação de Conta' type='button' onclick=\"window.location.href='prestacao_gerenciar.php?pagina=adicionar_prestacao_gerenciar$autenticacao';\" /></div>
	<div class='filtro'>
		<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='prestacao_gerenciar.php?pagina=prestacao_gerenciar$autenticacao'>
		<input name='fil_prestacao' id='fil_prestacao' value='$fil_prestacao' placeholder='N° '>
		<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
		<input name='fil_referencia' id='fil_referencia' value='$fil_referencia' placeholder='Referência '>
		<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='$fil_data_inicio' onkeypress='return mascaraData(this,event);'>
		<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='$fil_data_fim' onkeypress='return mascaraData(this,event);'>
		<input type='submit' value='Filtrar'> 
		</form>
	</div>
	";
	if ($rows) {
		echo "
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>N° Prestação de Conta</td>
				<td class='titulo_tabela'>Cliente</td>
				<td class='titulo_tabela'>Referência</td>
				<td class='titulo_tabela'>Data Envio</td>
				<td class='titulo_tabela'>Por</td>
				<td class='titulo_tabela'>Observação</td>
				<td class='titulo_tabela' align='center'>Data Cadastro</td>
				<td class='titulo_tabela' align='center'>Gerar Protocolo</td>
				<td class='titulo_tabela' align='center'>Protocolo Assinado</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>";
		$c = 0;
		foreach ($rows as $row) {
			$pre_id = $row['pre_id'];
			$cli_nome_razao = $row['cli_nome_razao'];
			$pre_referencia = $row['pre_referencia'];
			$pre_data_envio = $row['pre_data_envio'] ? (new DateTime($row['pre_data_envio']))->format('d/m/Y') : '';
			$pre_enviado_por = $row['pre_enviado_por'];
			$pre_observacoes = $row['pre_observacoes'];
			$pre_data_cadastro = $row['pre_data_cadastro'] ? (new DateTime($row['pre_data_cadastro']))->format('d/m/Y') : '';
			$pre_hora_cadastro = $row['pre_data_cadastro'] ? (new DateTime($row['pre_data_cadastro']))->format('H:i') : '';
			$pre_comprovante = $row['pre_comprovante'];
			$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
			$c++;
			echo "
			<script>
				$(function() {
					$('#normal-button-$pre_id').toolbar({content: '#user-options-$pre_id', position: 'top', hideOnClick: true});
				});
			</script>
			<div id='user-options-$pre_id' class='toolbar-icons' style='display: none;'>
				<a href='#' title='Anexar comprovante' onclick=\"
					abreMaskAcao(
						'<form name=\\'form_envia_comprovante\\' id=\\'form_envia_comprovante\\' enctype=\\'multipart/form-data\\' method=\\'post\\' action=\\'prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=comprovante&pre_id=$pre_id$autenticacao\\'>'+
						'<table align=center>'+
							'<tr>'+
								'<td>'+
									'<input type=\\'file\\' name=\\'pre_comprovante[]\\' id=\\'pre_comprovante\\'><br><br>'+
									'<input id=\\'bt_envia_comprovante\\' value=\\' Salvar \\' type=\\'submit\\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
									'<input value=\\' Cancelar \\' type=\\'button\\' class=\\'close_janela\\'>'+
								'</td>'+
							'</tr>'+
						'<table>'+
						'</form>');
					\">
					<img border='0' src='../imagens/icon-comprovante.png'>
				</a>
				<a title='Editar' href='prestacao_gerenciar.php?pagina=editar_prestacao_gerenciar&pre_id=$pre_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
				<a title='Excluir' onclick=\"
					abreMask(
						'Deseja realmente excluir a prestação <b>$pre_id</b>?<br><br>'+
						'<input value=\\' Sim \\' type=\\'button\\' onclick=\\'window.location.href=\\'prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=excluir&pre_id=$pre_id$autenticacao\\';\\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\\' Não \\' type=\\'button\\' class=\\'close_janela\\'>');
					\">
					<img border='0' src='../imagens/icon-excluir.png'>
				</a>
			</div>
			";
			echo "<tr class='$c1'>
				<td>$pre_id</td>
				<td>$cli_nome_razao</td>
				<td>$pre_referencia</td>
				<td>$pre_data_envio</td>
				<td>$pre_enviado_por</td>
				<td>$pre_observacoes</td>
				<td align='center'>$pre_data_cadastro<br><span class='detalhe'>$pre_hora_cadastro</span></td>
				<td align='center'><a href='prestacao_imprimir.php?pre_id=$pre_id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
				<td align='center'>";
			if ($pre_comprovante) {
				echo "<a href='$pre_comprovante' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";
			}
			echo "</td>
				<td align=center><div id='normal-button-$pre_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
			</tr>";
		}
		echo "</table>";
		// Paginação (ajuste conforme seu sistema)
		$variavel = "&pagina=prestacao_gerenciar&fil_prestacao=$fil_prestacao&fil_nome=$fil_nome&fil_referencia=$fil_referencia&fil_data_inicio=$fil_data_inicio&fil_data_fim=$fil_data_fim$autenticacao";
		include("../mod_includes/php/paginacao.php");
	} else {
		echo "<br><br><br>Não há nenhuma prestação de conta cadastrada.";
	}
	echo "<div class='titulo'>  </div></div>";
}

// Formulário Adicionar
if ($pagina === 'adicionar_prestacao_gerenciar') {
	echo "
	<form name='form_prestacao_gerenciar' id='form_prestacao_gerenciar' enctype='multipart/form-data' method='post' action='prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='950'>
			<tr>
				<td align='left'>
					<div class='formtitulo'>Selecione o cliente</div>
					<div class='suggestion'>
						<input name='pre_cliente_id' id='pre_cliente_id'  type='hidden' value='' />
						<input name='pre_cliente' id='pre_cliente' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' />
						<div class='suggestionsBox' id='suggestions' style='display: none;'>
							<div class='suggestionList' id='autoSuggestionsList'>&nbsp;</div>
						</div>
					</div>
					<p><br><br>
					Referência:<br>
					<select name='pre_ref_mes' id='pre_ref_mes'>
						<option value=''>Mês</option>
						<option value='01'>Janeiro</option>
						<option value='02'>Fevereiro</option>
						<option value='03'>Março</option>
						<option value='04'>Abril</option>
						<option value='05'>Maio</option>
						<option value='06'>Junho</option>
						<option value='07'>Julho</option>
						<option value='08'>Agosto</option>
						<option value='09'>Setembro</option>
						<option value='10'>Outubro</option>
						<option value='11'>Novembro</option>
						<option value='12'>Dezembro</option>
					</select>
					/<input type='text' id='pre_ref_ano' name='pre_ref_ano' value='' placeholder='Ano' />
					<p>
					<input type='text' id='pre_data_envio' name='pre_data_envio' value='' placeholder='Data Envio' onkeypress='return mascaraData(this,event);' />
					<p>
					<input type='text' id='pre_enviado_por' name='pre_enviado_por' value='' placeholder='Enviado por:' />
					<p>
					<textarea name='pre_observacoes' id='pre_observacoes' placeholder='Observações'></textarea>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='submit' id='bt_prestacao_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=\"window.location.href='prestacao_gerenciar.php?pagina=prestacao_gerenciar$autenticacao';\" value='Cancelar'/>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
}

// Formulário Editar
if ($pagina === 'editar_prestacao_gerenciar') {
	$pre_id = $_GET['pre_id'] ?? '';
	$stmt = $pdo->prepare("SELECT * FROM prestacao_gerenciar 
		LEFT JOIN (cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
		WHERE ucl_usuario = ? AND pre_id = ?");
	$stmt->execute([$_SESSION['usuario_id'], $pre_id]);
	$row = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($row) {
		$pre_cliente = $row['pre_cliente'];
		$cli_nome_razao = $row['cli_nome_razao'];
		$cli_cnpj = $row['cli_cnpj'];
		$pre_referencia = $row['pre_referencia'];
		[$pre_ref_mes, $pre_ref_ano] = explode('/', $pre_referencia);
		$meses = [
			'01' => 'Janeiro',
			'02' => 'Fevereiro',
			'03' => 'Março',
			'04' => 'Abril',
			'05' => 'Maio',
			'06' => 'Junho',
			'07' => 'Julho',
			'08' => 'Agosto',
			'09' => 'Setembro',
			'10' => 'Outubro',
			'11' => 'Novembro',
			'12' => 'Dezembro'
		];
		$pre_ref_mes_n = $meses[$pre_ref_mes] ?? $pre_ref_mes;
		$pre_data_envio = $row['pre_data_envio'] ? (new DateTime($row['pre_data_envio']))->format('d/m/Y') : '';
		$pre_enviado_por = $row['pre_enviado_por'];
		$pre_observacoes = $row['pre_observacoes'];
		echo "
		<form name='form_prestacao_gerenciar' id='form_prestacao_gerenciar' enctype='multipart/form-data' method='post' action='prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=editar&pre_id=$pre_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $cli_nome_razao </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<div class='formtitulo'>Selecione o cliente</div>
						<div class='suggestion'>
							<input name='pre_cliente_id' id='pre_cliente_id'  type='hidden' value='$pre_cliente' />
							<input name='pre_cliente_block' id='pre_cliente_block' value='$cli_nome_razao ($cli_cnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>&nbsp;</div>
							</div>
						</div>
						<p><br><br>
						Referência:<br>
						<select name='pre_ref_mes' id='pre_ref_mes'>
							<option value='$pre_ref_mes'>$pre_ref_mes_n</option>";
		foreach ($meses as $num => $nome) {
			echo "<option value='$num'>$nome</option>";
		}
		echo "</select>
						/<input type='text' id='pre_ref_ano' name='pre_ref_ano' value='$pre_ref_ano' placeholder='Ano' />
						<p>
						<input type='text' id='pre_data_envio' name='pre_data_envio' value='$pre_data_envio' placeholder='Data Envio' onkeypress='return mascaraData(this,event);' />
						<p>
						<input type='text' id='pre_enviado_por' name='pre_enviado_por' value='$pre_enviado_por' placeholder='Enviado por:' />
						<p>
						<textarea name='pre_observacoes' id='pre_observacoes' placeholder='Observações'>$pre_observacoes</textarea>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='submit' id='bt_prestacao_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=\"window.location.href='prestacao_gerenciar.php?pagina=prestacao_gerenciar$autenticacao';\" value='Cancelar'/>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'>   </div>
		</div>
		</form>
		";
	}
}

include('../mod_rodape/rodape.php');
echo "</body></html>";