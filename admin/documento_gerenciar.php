<?php
session_start();
$pagina_link = 'documento_gerenciar';
include('../mod_includes/php/connect.php');

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php echo $titulo ?? ''; ?></title>
	<meta name="author" content="MogiComp">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
	<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
	<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
	<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
	<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
	<?php
	include('../mod_includes/php/funcoes-jquery.php');
	require_once('../mod_includes/php/verificalogin.php');
	include("../mod_topo/topo.php");
	require_once('../mod_includes/php/verificapermissao.php');

	$page = "Documentos &raquo; <a href='documento_gerenciar.php?pagina=documento_gerenciar$autenticacao'>Gerenciar</a>";
	$action = $_GET['action'] ?? '';
	$pagina = $_GET['pagina'] ?? '';
	$autenticacao = $_GET['autenticacao'] ?? '';
	$pag = $_GET['pag'] ?? 1;

	function formatDateToDb($date)
	{
		if (!$date)
			return null;
		$parts = explode('/', $date);
		if (count($parts) == 3) {
			return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
		}
		return $date;
	}

	function formatDateToBr($date)
	{
		if (!$date)
			return '';
		$parts = explode('-', $date);
		if (count($parts) == 3) {
			return "{$parts[2]}/{$parts[1]}/{$parts[0]}";
		}
		return $date;
	}

	if ($action === "adicionar") {
		$doc_cliente = $_POST['doc_cliente_id'] ?? null;
		$doc_orcamento = $_POST['doc_orcamento'] ?? null;
		$doc_tipo = $_POST['doc_tipo'] ?? null;
		$doc_data_emissao = formatDateToDb($_POST['doc_data_emissao'] ?? '');
		$doc_periodicidade = $_POST['doc_periodicidade'] ?? null;
		$doc_observacoes = $_POST['doc_observacoes'] ?? null;

		$doc_data_vencimento = null;
		if ($doc_data_emissao && $doc_periodicidade) {
			$date = new DateTime($doc_data_emissao);
			switch ($doc_periodicidade) {
				case 6:
					$date->modify('+6 months');
					break;
				case 12:
					$date->modify('+1 year');
					break;
				case 24:
					$date->modify('+2 years');
					break;
				case 36:
					$date->modify('+3 years');
					break;
				case 48:
					$date->modify('+4 years');
					break;
				case 60:
					$date->modify('+5 years');
					break;
			}
			$doc_data_vencimento = $date->format('Y-m-d');
		}

		$stmt = $pdo->prepare("INSERT INTO documento_gerenciar (
		doc_cliente, doc_orcamento, doc_tipo, doc_data_emissao, doc_periodicidade, doc_data_vencimento, doc_observacoes
	) VALUES (
		:doc_cliente, :doc_orcamento, :doc_tipo, :doc_data_emissao, :doc_periodicidade, :doc_data_vencimento, :doc_observacoes
	)");
		$stmt->bindValue(':doc_cliente', $doc_cliente);
		$stmt->bindValue(':doc_orcamento', $doc_orcamento ?: null, PDO::PARAM_INT);
		$stmt->bindValue(':doc_tipo', $doc_tipo);
		$stmt->bindValue(':doc_data_emissao', $doc_data_emissao);
		$stmt->bindValue(':doc_periodicidade', $doc_periodicidade);
		$stmt->bindValue(':doc_data_vencimento', $doc_data_vencimento);
		$stmt->bindValue(':doc_observacoes', $doc_observacoes);

		if ($stmt->execute()) {
			$ultimo_id = $pdo->lastInsertId();
			$caminho = "../admin/docs/$ultimo_id/";
			if (!file_exists($caminho)) {
				mkdir($caminho, 0755, true);
			}
			$doc_anexo = $_FILES['doc_anexo']['name'] ?? [];
			$tmp_anexo = $_FILES['doc_anexo']['tmp_name'] ?? [];
			foreach ((array) $doc_anexo as $k => $value) {
				if ($doc_anexo[$k] != '') {
					$extensao = pathinfo($doc_anexo[$k], PATHINFO_EXTENSION);
					$arquivo = $caminho . md5(mt_rand(1, 10000) . $doc_anexo[$k]) . '.' . $extensao;
					move_uploaded_file($tmp_anexo[$k], $arquivo);
					$stmt2 = $pdo->prepare("UPDATE documento_gerenciar SET doc_anexo = :arquivo WHERE doc_id = :id");
					$stmt2->execute([':arquivo' => $arquivo, ':id' => $ultimo_id]);
				}
			}
			echo "<script>abreMask('<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>');</script>";
		} else {
			echo "<script>abreMask('<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');</script>";
		}
	}

	if ($action === 'editar') {
		$doc_id = $_GET['doc_id'] ?? null;
		$doc_cliente = $_POST['doc_cliente_id'] ?? null;
		$doc_orcamento = $_POST['doc_orcamento'] ?? null;
		$doc_tipo = $_POST['doc_tipo'] ?? null;
		$doc_data_emissao = formatDateToDb($_POST['doc_data_emissao'] ?? '');
		$doc_periodicidade = $_POST['doc_periodicidade'] ?? null;
		$doc_observacoes = $_POST['doc_observacoes'] ?? null;

		$doc_data_vencimento = null;
		if ($doc_data_emissao && $doc_periodicidade) {
			$date = new DateTime($doc_data_emissao);
			switch ($doc_periodicidade) {
				case 6:
					$date->modify('+6 months');
					break;
				case 12:
					$date->modify('+1 year');
					break;
				case 24:
					$date->modify('+2 years');
					break;
				case 36:
					$date->modify('+3 years');
					break;
				case 48:
					$date->modify('+4 years');
					break;
				case 60:
					$date->modify('+5 years');
					break;
			}
			$doc_data_vencimento = $date->format('Y-m-d');
		}

		$stmt = $pdo->prepare("UPDATE documento_gerenciar SET 
		doc_orcamento = :doc_orcamento,
		doc_tipo = :doc_tipo,
		doc_data_emissao = :doc_data_emissao,
		doc_periodicidade = :doc_periodicidade,
		doc_data_vencimento = :doc_data_vencimento,
		doc_observacoes = :doc_observacoes
		WHERE doc_id = :doc_id
	");
		$stmt->bindValue(':doc_orcamento', $doc_orcamento ?: null, PDO::PARAM_INT);
		$stmt->bindValue(':doc_tipo', $doc_tipo);
		$stmt->bindValue(':doc_data_emissao', $doc_data_emissao);
		$stmt->bindValue(':doc_periodicidade', $doc_periodicidade);
		$stmt->bindValue(':doc_data_vencimento', $doc_data_vencimento);
		$stmt->bindValue(':doc_observacoes', $doc_observacoes);
		$stmt->bindValue(':doc_id', $doc_id);

		$erro = false;
		if ($stmt->execute()) {
			$ultimo_id = $doc_id;
			$caminho = "../admin/docs/$ultimo_id/";
			if (!file_exists($caminho)) {
				mkdir($caminho, 0755, true);
			}
			$stmt2 = $pdo->prepare("SELECT doc_anexo FROM documento_gerenciar WHERE doc_id = :doc_id");
			$stmt2->execute([':doc_id' => $doc_id]);
			$anexo = $stmt2->fetchColumn();

			$doc_anexo = $_FILES['doc_anexo']['name'] ?? [];
			$tmp_anexo = $_FILES['doc_anexo']['tmp_name'] ?? [];
			foreach ((array) $doc_anexo as $k => $value) {
				if ($doc_anexo[$k] != '') {
					$extensao = pathinfo($doc_anexo[$k], PATHINFO_EXTENSION);
					$arquivo = $caminho . md5(mt_rand(1, 10000) . $doc_anexo[$k]) . '.' . $extensao;
					move_uploaded_file($tmp_anexo[$k], $arquivo);
					if ($anexo && file_exists($anexo)) {
						unlink($anexo);
					}
					$stmt3 = $pdo->prepare("UPDATE documento_gerenciar SET doc_anexo = :arquivo WHERE doc_id = :doc_id");
					if (!$stmt3->execute([':arquivo' => $arquivo, ':doc_id' => $doc_id])) {
						$erro = true;
					}
				}
			}
			if (!$erro) {
				echo "<script>abreMask('<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>');</script>";
			} else {
				echo "<script>abreMask('<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');</script>";
			}
		} else {
			echo "<script>abreMask('<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');</script>";
		}
	}

	if ($action === 'excluir') {
		$doc_id = $_GET['doc_id'] ?? null;
		$stmt = $pdo->prepare("DELETE FROM documento_gerenciar WHERE doc_id = :doc_id");
		if ($stmt->execute([':doc_id' => $doc_id])) {
			echo "<script>abreMask('<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br><input value=\' OK \' type=\'button\' class=\'close_janela\'>');</script>";
		} else {
			echo "<script>abreMask('<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');</script>";
		}
	}

	// Filtros
	$num_por_pagina = 10;
	$primeiro_registro = ($pag - 1) * $num_por_pagina;
	$fil_nome = $_REQUEST['fil_nome'] ?? '';
	$fil_doc_tipo = $_REQUEST['fil_doc_tipo'] ?? '';
	$fil_data_inicio = formatDateToDb($_REQUEST['fil_data_inicio'] ?? '');
	$fil_data_fim = formatDateToDb($_REQUEST['fil_data_fim'] ?? '');
	$fil_vencido = $_REQUEST['fil_vencido'] ?? '';

	$nome_query = $fil_nome ? "cli_nome_razao LIKE :fil_nome" : "1=1";
	$tipo_doc_query = $fil_doc_tipo ? "doc_tipo = :fil_doc_tipo" : "1=1";
	$data_query = "1=1";
	if ($fil_data_inicio && $fil_data_fim) {
		$data_query = "doc_data_vencimento BETWEEN :fil_data_inicio AND :fil_data_fim";
	} elseif ($fil_data_inicio) {
		$data_query = "doc_data_vencimento >= :fil_data_inicio";
	} elseif ($fil_data_fim) {
		$data_query = "doc_data_vencimento <= :fil_data_fim";
	}
	$vencido_query = "1=1";
	if ($fil_vencido === 'Sim') {
		$vencido_query = "doc_data_vencimento <= :hoje";
	} elseif ($fil_vencido === 'Não') {
		$vencido_query = "doc_data_vencimento > :hoje";
	}

	$where = "cli_status = 1 and cli_deletado = 1 and ucl_usuario = :usuario_id AND $nome_query AND $tipo_doc_query AND $data_query AND $vencido_query";
	$sql = "SELECT documento_gerenciar.*, cadastro_clientes.cli_nome_razao, cadastro_tipos_docs.tpd_nome, orcamento_gerenciar.orc_id, cadastro_tipos_servicos.tps_nome
		FROM documento_gerenciar
		LEFT JOIN (cadastro_clientes
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
		LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
		LEFT JOIN (orcamento_gerenciar
			LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
		ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
		WHERE $where
		ORDER BY doc_data_cadastro DESC
		LIMIT $primeiro_registro, $num_por_pagina";

	$params = [
		':usuario_id' => $_SESSION['usuario_id']
	];
	if ($fil_nome)
		$params[':fil_nome'] = "%$fil_nome%";
	if ($fil_doc_tipo)
		$params[':fil_doc_tipo'] = $fil_doc_tipo;
	if ($fil_data_inicio && $fil_data_fim) {
		$params[':fil_data_inicio'] = $fil_data_inicio;
		$params[':fil_data_fim'] = $fil_data_fim . ' 23:59:59';
	} elseif ($fil_data_inicio) {
		$params[':fil_data_inicio'] = $fil_data_inicio;
	} elseif ($fil_data_fim) {
		$params[':fil_data_fim'] = $fil_data_fim . ' 23:59:59';
	}
	if ($fil_vencido === 'Sim' || $fil_vencido === 'Não') {
		$params[':hoje'] = date('Y-m-d');
	}

	$query = $pdo->prepare($sql);
	$query->execute($params);
	$rows = $query->rowCount();

	if ($pagina == "documento_gerenciar") {
		echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Documento' type='button' onclick=javascript:window.location.href='documento_gerenciar.php?pagina=adicionar_documento_gerenciar$autenticacao'; /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='documento_gerenciar.php?pagina=documento_gerenciar$autenticacao'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<select name='fil_doc_tipo' id='fil_doc_tipo'>
				<option value='$fil_doc_tipo'>Tipo de documento</option>";
		$sql_tpd = "SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC";
		foreach ($pdo->query($sql_tpd) as $row_tpd) {
			echo "<option value='{$row_tpd['tpd_id']}'>{$row_tpd['tpd_nome']}</option>";
		}
		echo "
			</select>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Venc. Início' style='width:150px;' value='" . ($_REQUEST['fil_data_inicio'] ?? '') . "' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Venc. Fim'  style='width:150px;' value='" . ($_REQUEST['fil_data_fim'] ?? '') . "' onkeypress='return mascaraData(this,event);'>
			<select name='fil_vencido' id='fil_vencido' style='width:150px;'>
				<option value='$fil_vencido'>$fil_vencido</option>
				<option value='Sim'>Sim</option>
				<option value='Não'>Não</option>
				<option value=''>Todos</option>				
			</select>	
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
		";
		if ($rows > 0) {
			echo "
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Tipo de Doc</td>
				<td class='titulo_tabela'>Cliente</td>
				<td class='titulo_tabela'>Orçamento</td>
				<td class='titulo_tabela' align='center'>Data Emissão</td>
				<td class='titulo_tabela' align='center'>Periodicidade</td>
				<td class='titulo_tabela' align='center'>Data Vencimento</td>
				<td class='titulo_tabela' align='center'>Data Cadastro</td>
				<td class='titulo_tabela' align='center'>Anexo</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>";
			$c = 0;
			foreach ($query as $row) {
				$doc_id = $row['doc_id'];
				$cli_nome_razao = $row['cli_nome_razao'];
				$orc_id = $row['orc_id'];
				$tps_nome = $row['tps_nome'];
				$tpd_nome = $row['tpd_nome'];
				$doc_anexo = $row['doc_anexo'];
				$doc_data_emissao = formatDateToBr($row['doc_data_emissao']);
				$doc_periodicidade = $row['doc_periodicidade'];
				$doc_data_vencimento = formatDateToBr($row['doc_data_vencimento']);
				$doc_data_cadastro = formatDateToBr(substr($row['doc_data_cadastro'], 0, 10));
				$doc_hora_cadastro = substr($row['doc_data_cadastro'], 11, 5);

				$periodicidade_nomes = [
					6 => "Semestral",
					12 => "Anual",
					24 => "Bienal",
					36 => "Trienal",
					48 => "Quadrienal",
					60 => "Quinquenal"
				];
				$doc_periodicidade_n = $periodicidade_nomes[$doc_periodicidade] ?? '';

				$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
				$c++;

				echo "<tr class='$c1'>
				<td>$tpd_nome</td>
				<td>$cli_nome_razao</td>
				<td>$orc_id ($tps_nome)</td>
				<td align=center>$doc_data_emissao</td>
				<td align=center>$doc_periodicidade_n</td>
				<td align=center>$doc_data_vencimento</td>
				<td align='center'>$doc_data_cadastro<br><span class='detalhe'>$doc_hora_cadastro</span></td>
				<td align='center'>";
				if ($doc_anexo) {
					echo "<a href='$doc_anexo' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";
				}
				echo "</td>
				<td align=center>
					<div id='normal-button-$doc_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div>
					<div id='user-options-$doc_id' class='toolbar-icons' style='display: none;'>
						<a href='documento_gerenciar.php?pagina=editar_documento_gerenciar&doc_id=$doc_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
						<a onclick=\"abreMask('Deseja realmente excluir o documento <b>$cli_nome_razao</b>?<br><br><input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'documento_gerenciar.php?pagina=documento_gerenciar&action=excluir&doc_id=$doc_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input value=\' Não \' type=\'button\' class=\'close_janela\'>');\"><img border='0' src='../imagens/icon-excluir.png'></a>
					</div>
				</td>
			</tr>";
			}
			echo "</table>";
			// Paginação
			$sql_total = "SELECT COUNT(*) FROM documento_gerenciar
			LEFT JOIN (cadastro_clientes
				INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
			ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
			LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
			LEFT JOIN (orcamento_gerenciar
				LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
			ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
			WHERE $where";
			$stmt_total = $pdo->prepare($sql_total);
			$stmt_total->execute($params);
			$total_registros = $stmt_total->fetchColumn();
			$total_paginas = ceil($total_registros / $num_por_pagina);

			if ($total_paginas > 1) {
				echo "<div class='paginacao' style='text-align:center; margin:20px 0;'>";
				for ($i = 1; $i <= $total_paginas; $i++) {
					if ($i == $pag) {
						echo "<span style='font-weight:bold; color:#000;'>$i</span> ";
					} else {
						// Mantém os filtros na URL
						$queryString = http_build_query(array_merge($_GET, $_POST, ['pag' => $i]));
						echo "<a href='documento_gerenciar.php?$queryString'>$i</a> ";
					}
				}
				echo "</div>";
			}
		} else {
			echo "<br><br><br>Não há nenhum documento cadastrado.";
		}
		echo "<div class='titulo'>  </div></div>";
	}

	if ($pagina == 'adicionar_documento_gerenciar') {
		echo "	
	<form name='form_documento_gerenciar' id='form_documento_gerenciar' enctype='multipart/form-data' method='post' action='documento_gerenciar.php?pagina=documento_gerenciar&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='950'>
			<tr>
				<td align='left'>
					<div class='formtitulo'>Selecione o cliente</div>
					<div class='suggestion'>
						<input name='doc_cliente_id' id='doc_cliente_id'  type='hidden' value='' />
						<input name='doc_cliente' id='doc_cliente' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' />
						<div class='suggestionsBox' id='suggestions' style='display: none;'>
							<div class='suggestionList' id='autoSuggestionsList'>&nbsp;</div>
						</div>
					</div>
					<p><br><br>
					<select name='doc_orcamento' id='doc_orcamento'>
						<option value=''>Selecione o orçamento caso tenha relação</option>
					</select>
					<p>
					<select name='doc_tipo' id='doc_tipo'>
						<option value=''>Selecione o tipo de documento</option>";
		$sql = "SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC";
		foreach ($pdo->query($sql) as $row) {
			echo "<option value='{$row['tpd_id']}'>{$row['tpd_nome']}</option>";
		}
		echo "
					</select>
					<p>
					<input name='doc_anexo[]' id='doc_anexo' type='file' onchange='verificaExtensao(this);'> &nbsp;
					<p>
					<input type='text' name='doc_data_emissao' id='doc_data_emissao' placeholder='Data Emissão' onkeypress='return mascaraData(this,event);'>
					<select name='doc_periodicidade' id='doc_periodicidade'>
						<option value=''>Periodicidade</option>
						<option value='6'>Semestral</option>
						<option value='12'>Anual</option>
						<option value='24'>Bienal</option>
						<option value='36'>Trienal</option>
						<option value='48'>Quadrienal</option>
						<option value='60'>Quinquenal</option>
					</select>
					<p>
					<textarea name='doc_observacoes' id='doc_observacoes' placeholder='Observações'></textarea>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='button' id='bt_documento_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='documento_gerenciar.php?pagina=documento_gerenciar$autenticacao'; value='Cancelar'/></center>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
	}

	if ($pagina == 'editar_documento_gerenciar') {
		$doc_id = $_GET['doc_id'] ?? null;
		$stmt = $pdo->prepare("SELECT documento_gerenciar.*, cadastro_clientes.cli_nome_razao, cadastro_clientes.cli_cnpj, orcamento_gerenciar.orc_id, cadastro_tipos_servicos.tps_nome, cadastro_tipos_docs.tpd_nome
		FROM documento_gerenciar
		LEFT JOIN (cadastro_clientes
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
		LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
		LEFT JOIN (orcamento_gerenciar
			LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
		ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
		WHERE cadastro_usuarios_clientes.ucl_usuario = :usuario_id AND doc_id = :doc_id");
		$stmt->execute([':usuario_id' => $_SESSION['usuario_id'], ':doc_id' => $doc_id]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($row) {
			$doc_cliente = $row['doc_cliente'];
			$cli_nome_razao = $row['cli_nome_razao'];
			$cli_cnpj = $row['cli_cnpj'];
			$doc_orcamento = $row['doc_orcamento'];
			$orc_id = $row['orc_id'];
			$tps_nome = $row['tps_nome'];
			$doc_tipo = $row['doc_tipo'];
			$tpd_nome = $row['tpd_nome'];
			$doc_data_emissao = formatDateToBr($row['doc_data_emissao']);
			$doc_periodicidade = $row['doc_periodicidade'];
			$doc_observacoes = $row['doc_observacoes'];
			$doc_anexo = $row['doc_anexo'];
			$periodicidade_nomes = [
				6 => "Semestral",
				12 => "Anual",
				24 => "Bienal",
				36 => "Trienal",
				48 => "Quadrienal",
				60 => "Quinquenal"
			];
			$doc_periodicidade_n = $periodicidade_nomes[$doc_periodicidade] ?? '';
			echo "
		<form name='form_documento_gerenciar' id='form_documento_gerenciar' enctype='multipart/form-data' method='post' action='documento_gerenciar.php?pagina=documento_gerenciar&action=editar&doc_id=$doc_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $cli_nome_razao </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input type='hidden' name='doc_id' id='doc_id' value='$doc_id' placeholder='ID'>
						<div class='formtitulo'>Selecione o cliente que deseja abrir o documento</div>
						<div class='suggestion'>
							<input name='doc_cliente_id' id='doc_cliente_id'  type='hidden' value='$doc_cliente' />
							<input name='doc_cliente_block' id='doc_cliente_block' value='$cli_nome_razao ($cli_cnpj)' type='text' placeholder='Digite as iniciais ou CNPJ do cliente para procurar' autocomplete='off' readonly />
							<div class='suggestionsBox' id='suggestions' style='display: none;'>
								<div class='suggestionList' id='autoSuggestionsList'>&nbsp;</div>
							</div>
						</div>
						<p><br><br>";
			if ($doc_orcamento) {
				echo "<select name='doc_orcamento' id='doc_orcamento'>
								 <option value='$doc_orcamento'>$orc_id ($tps_nome)</option>
							  </select>
							  <p>";
			}
			echo "
						<select name='doc_tipo' id='doc_tipo'>
							<option value='$doc_tipo'>$tpd_nome</option>";
			$sql = "SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC";
			foreach ($pdo->query($sql) as $row2) {
				echo "<option value='{$row2['tpd_id']}'>{$row2['tpd_nome']}</option>";
			}
			echo "
						</select>
						<p>
						<input name='doc_anexo[]' id='doc_anexo' type='file' onchange='verificaExtensao(this);'> &nbsp;";
			if ($doc_anexo) {
				echo "Anexo atual: <a href='$doc_anexo' target='_blank'><img src='../imagens/pdf.png' valign='middle'></a>";
			}
			echo "
						<p>
						<input type='text' name='doc_data_emissao' id='doc_data_emissao' value='$doc_data_emissao' placeholder='Data Emissão' onkeypress='return mascaraData(this,event);'>
						<select name='doc_periodicidade' id='doc_periodicidade'>
							<option value='$doc_periodicidade'>$doc_periodicidade_n</option>
							<option value='6'>Semestral</option>
							<option value='12'>Anual</option>
							<option value='24'>Bienal</option>
							<option value='36'>Trienal</option>
							<option value='48'>Quadrienal</option>
							<option value='60'>Quinquenal</option>
						</select>
						<p>
						<textarea name='doc_observacoes' id='doc_observacoes' placeholder='Observações'>$doc_observacoes</textarea>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='button' id='bt_documento_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=javascript:window.location.href='documento_gerenciar.php?pagina=documento_gerenciar$autenticacao'; value='Cancelar'/></center>
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
	?>
</body>

</html>