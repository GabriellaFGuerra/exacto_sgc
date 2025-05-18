<?php
session_start();
$paginaAtual = 'prestacao_gerenciar';
require_once '../mod_includes/php/connect.php';

require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';
include '../mod_includes/php/funcoes-jquery.php';
include '../mod_topo/topo.php';

$titulo = $titulo ?? 'Prestação de Contas';

function exibirMascara($mensagem)
{
	echo "<script>abreMask(`$mensagem`);</script>";
}

function exibirMascaraAcao($mensagem)
{
	echo "<script>abreMaskAcao(`$mensagem`);</script>";
}

$caminhoPagina = "Prestação de Contas &raquo; <a href='prestacao_gerenciar.php?pagina=prestacao_gerenciar'>Gerenciar</a>";

$acao = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? 'prestacao_gerenciar';
$autenticacao = $_GET['autenticacao'] ?? '';
$paginaNumero = isset($_GET['pag']) ? (int) $_GET['pag'] : 1;

// Função para tratar datas
function formatarData($data, $formatoEntrada = 'd/m/Y', $formatoSaida = 'Y-m-d')
{
	$dataObj = DateTime::createFromFormat($formatoEntrada, $data);
	return $dataObj ? $dataObj->format($formatoSaida) : null;
}

// Processamento de formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($acao === "adicionar") {
		$clienteId = $_POST['pre_cliente_id'] ?? '';
		$referencia = ($_POST['pre_ref_mes'] ?? '') . '/' . ($_POST['pre_ref_ano'] ?? '');
		$dataEnvio = formatarData($_POST['pre_data_envio'] ?? '');
		$enviadoPor = $_POST['pre_enviado_por'] ?? '';
		$observacoes = $_POST['pre_observacoes'] ?? '';

		$stmt = $pdo->prepare("INSERT INTO prestacao_gerenciar (pre_cliente, pre_referencia, pre_data_envio, pre_enviado_por, pre_observacoes) VALUES (?, ?, ?, ?, ?)");
		if ($stmt->execute([$clienteId, $referencia, $dataEnvio, $enviadoPor, $observacoes])) {
			exibirMascara("<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			exibirMascara("<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	if ($acao === 'editar') {
		$prestacaoId = $_GET['pre_id'] ?? '';
		$referencia = ($_POST['pre_ref_mes'] ?? '') . '/' . ($_POST['pre_ref_ano'] ?? '');
		$dataEnvio = formatarData($_POST['pre_data_envio'] ?? '');
		$enviadoPor = $_POST['pre_enviado_por'] ?? '';
		$observacoes = $_POST['pre_observacoes'] ?? '';

		$stmt = $pdo->prepare("UPDATE prestacao_gerenciar SET pre_referencia = ?, pre_data_envio = ?, pre_enviado_por = ?, pre_observacoes = ? WHERE pre_id = ?");
		if ($stmt->execute([$referencia, $dataEnvio, $enviadoPor, $observacoes, $prestacaoId])) {
			exibirMascara("<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			exibirMascara("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	if ($acao === 'comprovante') {
		$erro = false;
		$prestacaoId = $_GET['pre_id'] ?? '';
		$arquivos = $_FILES['pre_comprovante'] ?? null;
		$caminho = "../admin/prestacao_comprovante/$prestacaoId/";
		if (!is_dir($caminho)) {
			mkdir($caminho, 0755, true);
		}
		$arquivoFinal = '';
		if ($arquivos && is_array($arquivos['name'])) {
			foreach ($arquivos['name'] as $indice => $nomeArquivo) {
				if ($nomeArquivo) {
					$extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
					$nomeFinal = $caminho . md5(mt_rand(1, 10000) . $nomeArquivo) . '.' . $extensao;
					if (move_uploaded_file($arquivos['tmp_name'][$indice], $nomeFinal)) {
						$arquivoFinal = $nomeFinal;
					} else {
						$erro = true;
					}
				}
			}
			if ($arquivoFinal) {
				$stmt = $pdo->prepare("UPDATE prestacao_gerenciar SET pre_comprovante = ? WHERE pre_id = ?");
				if (!$stmt->execute([$arquivoFinal, $prestacaoId])) {
					$erro = true;
				}
			}
		}
		if (!$erro) {
			exibirMascara("<img src=../imagens/ok.png> Anexo enviado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			exibirMascara("<img src=../imagens/x.png> Erro ao enviar anexo.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}
}

// Exclusão
if ($acao === 'excluir') {
	$prestacaoId = $_GET['pre_id'] ?? '';
	$stmt = $pdo->prepare("DELETE FROM prestacao_gerenciar WHERE pre_id = ?");
	if ($stmt->execute([$prestacaoId])) {
		exibirMascara("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		exibirMascara("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

// Ativação/Desativação
if ($acao === 'ativar' || $acao === 'desativar') {
	$prestacaoId = $_GET['pre_id'] ?? '';
	$status = $acao === 'ativar' ? 1 : 0;
	$stmt = $pdo->prepare("UPDATE prestacao_gerenciar SET pre_status = ? WHERE pre_id = ?");
	if ($stmt->execute([$status, $prestacaoId])) {
		$mensagem = $status ? 'Ativação' : 'Desativação';
		exibirMascara("<img src=../imagens/ok.png> $mensagem realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		exibirMascara("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

// Filtros
$registrosPorPagina = 10;
$primeiroRegistro = ($paginaNumero - 1) * $registrosPorPagina;

$filtroPrestacao = $_REQUEST['fil_prestacao'] ?? '';
$filtroNome = $_REQUEST['fil_nome'] ?? '';
$filtroReferencia = $_REQUEST['fil_referencia'] ?? '';
$filtroDataInicio = $_REQUEST['fil_data_inicio'] ?? '';
$filtroDataFim = $_REQUEST['fil_data_fim'] ?? '';

$where = [];
$params = [];

$where[] = "cli_status = 1 AND cli_deletado = 1 AND ucl_usuario = ?";
$params[] = $_SESSION['usuario_id'];

if ($filtroPrestacao) {
	$where[] = "pre_id = ?";
	$params[] = $filtroPrestacao;
}
if ($filtroNome) {
	$where[] = "cli_nome_razao LIKE ?";
	$params[] = "%$filtroNome%";
}
if ($filtroReferencia) {
	$where[] = "pre_referencia = ?";
	$params[] = $filtroReferencia;
}
if ($filtroDataInicio || $filtroDataFim) {
	$dataInicio = $filtroDataInicio ? formatarData($filtroDataInicio) : null;
	$dataFim = $filtroDataFim ? formatarData($filtroDataFim, 'd/m/Y', 'Y-m-d 23:59:59') : null;
	if ($dataInicio && $dataFim) {
		$where[] = "pre_data_cadastro BETWEEN ? AND ?";
		$params[] = $dataInicio;
		$params[] = $dataFim;
	} elseif ($dataInicio) {
		$where[] = "pre_data_cadastro >= ?";
		$params[] = $dataInicio;
	} elseif ($dataFim) {
		$where[] = "pre_data_cadastro <= ?";
		$params[] = $dataFim;
	}
}

$whereSql = implode(' AND ', $where);

// Listagem com paginação
if ($pagina === "prestacao_gerenciar") {
	$sql = "SELECT * FROM prestacao_gerenciar 
		LEFT JOIN (cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
		WHERE $whereSql
		ORDER BY pre_data_cadastro DESC
		LIMIT $primeiroRegistro, $registrosPorPagina";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	$prestacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$sqlTotal = "SELECT COUNT(*) FROM prestacao_gerenciar 
		LEFT JOIN (cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
		WHERE $whereSql";
	$stmtTotal = $pdo->prepare($sqlTotal);
	$stmtTotal->execute($params);
	$totalRegistros = $stmtTotal->fetchColumn();

	// Cabeçalho HTML
	echo "<!DOCTYPE html>
<html lang='pt-br'>
<head>
<title>$titulo</title>
<meta name='author' content='MogiComp'>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<link rel='shortcut icon' href='../imagens/favicon.png'>";
	include "../css/style.php";
	echo "
<script src='../mod_includes/js/funcoes.js'></script>
<script src='../mod_includes/js/jquery-1.8.3.min.js'></script>
<link href='../mod_includes/js/toolbar/jquery.toolbars.css' rel='stylesheet' />
<link href='../mod_includes/js/toolbar/bootstrap.icons.css' rel='stylesheet'>
<script src='../mod_includes/js/toolbar/jquery.toolbar.js'></script>
</head>
<body>
<div class='centro'>
	<div class='titulo'> $caminhoPagina  </div>
	<div id='botoes'><input value='Nova Prestação de Conta' type='button' onclick=\"window.location.href='prestacao_gerenciar.php?pagina=adicionar_prestacao_gerenciar$autenticacao';\" /></div>
	<div class='filtro'>
		<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='prestacao_gerenciar.php?pagina=prestacao_gerenciar$autenticacao'>
		<input name='fil_prestacao' id='fil_prestacao' value='$filtroPrestacao' placeholder='N° '>
		<input name='fil_nome' id='fil_nome' value='$filtroNome' placeholder='Cliente'>
		<input name='fil_referencia' id='fil_referencia' value='$filtroReferencia' placeholder='Referência '>
		<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='$filtroDataInicio' onkeypress='return mascaraData(this,event);'>
		<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='$filtroDataFim' onkeypress='return mascaraData(this,event);'>
		<input type='submit' value='Filtrar'> 
		</form>
	</div>
	";

	if ($prestacoes) {
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
		$contador = 0;
		foreach ($prestacoes as $prestacao) {
			$preId = $prestacao['pre_id'];
			$clienteNome = $prestacao['cli_nome_razao'];
			$referencia = $prestacao['pre_referencia'];
			$dataEnvio = $prestacao['pre_data_envio'] ? (new DateTime($prestacao['pre_data_envio']))->format('d/m/Y') : '';
			$enviadoPor = $prestacao['pre_enviado_por'];
			$observacoes = $prestacao['pre_observacoes'];
			$dataCadastro = $prestacao['pre_data_cadastro'] ? (new DateTime($prestacao['pre_data_cadastro']))->format('d/m/Y') : '';
			$horaCadastro = $prestacao['pre_data_cadastro'] ? (new DateTime($prestacao['pre_data_cadastro']))->format('H:i') : '';
			$comprovante = $prestacao['pre_comprovante'];
			$classeLinha = $contador % 2 == 0 ? "linhaimpar" : "linhapar";
			$contador++;
			echo "
			<script>
				$(function() {
					$('#normal-button-$preId').toolbar({content: '#user-options-$preId', position: 'top', hideOnClick: true});
				});
			</script>
			<div id='user-options-$preId' class='toolbar-icons' style='display: none;'>
				<a href='#' title='Anexar comprovante' onclick=\"
					exibirMascaraAcao(
						'<form name=\\'form_envia_comprovante\\' id=\\'form_envia_comprovante\\' enctype=\\'multipart/form-data\\' method=\\'post\\' action=\\'prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=comprovante&pre_id=$preId$autenticacao\\'>'+
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
				<a title='Editar' href='prestacao_gerenciar.php?pagina=editar_prestacao_gerenciar&pre_id=$preId$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
				<a title='Excluir' onclick=\"
					exibirMascara(
						'Deseja realmente excluir a prestação <b>$preId</b>?<br><br>'+
						'<input value=\\' Sim \\' type=\\'button\\' onclick=\\'window.location.href=\\'prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=excluir&pre_id=$preId$autenticacao\\';\\'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\\' Não \\' type=\\'button\\' class=\\'close_janela\\'>');
					\">
					<img border='0' src='../imagens/icon-excluir.png'>
				</a>
			</div>
			";
			echo "<tr class='$classeLinha'>
				<td>$preId</td>
				<td>$clienteNome</td>
				<td>$referencia</td>
				<td>$dataEnvio</td>
				<td>$enviadoPor</td>
				<td>$observacoes</td>
				<td align='center'>$dataCadastro<br><span class='detalhe'>$horaCadastro</span></td>
				<td align='center'><a href='prestacao_imprimir.php?pre_id=$preId&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
				<td align='center'>";
			if ($comprovante) {
				echo "<a href='$comprovante' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";
			}
			echo "</td>
				<td align=center><div id='normal-button-$preId' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
			</tr>";
		}
		echo "</table>";

		// Paginação
		$urlPaginacao = "&pagina=prestacao_gerenciar&fil_prestacao=$filtroPrestacao&fil_nome=$filtroNome&fil_referencia=$filtroReferencia&fil_data_inicio=$filtroDataInicio&fil_data_fim=$filtroDataFim$autenticacao";
		include "../mod_includes/php/paginacao.php";
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
		<div class='titulo'> $caminhoPagina &raquo; Adicionar  </div>
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
	$prestacaoId = $_GET['pre_id'] ?? '';
	$stmt = $pdo->prepare("SELECT * FROM prestacao_gerenciar 
		LEFT JOIN (cadastro_clientes 
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
		WHERE ucl_usuario = ? AND pre_id = ?");
	$stmt->execute([$_SESSION['usuario_id'], $prestacaoId]);
	$prestacao = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($prestacao) {
		$clienteId = $prestacao['pre_cliente'];
		$clienteNome = $prestacao['cli_nome_razao'];
		$clienteCnpj = $prestacao['cli_cnpj'];
		$referencia = $prestacao['pre_referencia'];
		[$mesReferencia, $anoReferencia] = explode('/', $referencia);
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
		$nomeMesReferencia = $meses[$mesReferencia] ?? $mesReferencia;
		$dataEnvio = $prestacao['pre_data_envio'] ? (new DateTime($prestacao['pre_data_envio']))->format('d/m/Y') : '';
		$enviadoPor = $prestacao['pre_enviado_por'];
		$observacoes = $prestacao['pre_observacoes'];
		echo "
		<form name='form_prestacao_gerenciar' id='form_prestacao_gerenciar' enctype='multipart/form-data' method='post' action='prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=editar&pre_id=$prestacaoId$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $caminhoPagina &raquo; Editar: $clienteNome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<div class='formtitulo'>Selecione o cliente</div>
						<div class='suggestion'>
							<input name='pre_cliente_id' id='pre_cliente_id'  type='hidden' value='$clienteId' />
							<input name='pre_cliente_block' id='pre_cliente_block' value='$clienteNome ($clienteCnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>&nbsp;</div>
							</div>
						</div>
						<p><br><br>
						Referência:<br>
						<select name='pre_ref_mes' id='pre_ref_mes'>
							<option value='$mesReferencia'>$nomeMesReferencia</option>";
		foreach ($meses as $num => $nome) {
			echo "<option value='$num'>$nome</option>";
		}
		echo "</select>
						/<input type='text' id='pre_ref_ano' name='pre_ref_ano' value='$anoReferencia' placeholder='Ano' />
						<p>
						<input type='text' id='pre_data_envio' name='pre_data_envio' value='$dataEnvio' placeholder='Data Envio' onkeypress='return mascaraData(this,event);' />
						<p>
						<input type='text' id='pre_enviado_por' name='pre_enviado_por' value='$enviadoPor' placeholder='Enviado por:' />
						<p>
						<textarea name='pre_observacoes' id='pre_observacoes' placeholder='Observações'>$observacoes</textarea>
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

include '../mod_rodape/rodape.php';
echo "</body></html>";