<?php
session_start();
$pagina_link = 'cadastro_fornecedores';
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

	$pagina = $_GET['pagina'] ?? $_POST['pagina'] ?? '';
	$action = $_GET['action'] ?? $_POST['action'] ?? '';
	$autenticacao = $_GET['autenticacao'] ?? $_POST['autenticacao'] ?? '';
	$pag = $_GET['pag'] ?? $_POST['pag'] ?? 1;

	$page = "Cadastros &raquo; <a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores$autenticacao'>Fornecedores</a>";

	if ($action == "adicionar") {
		$for_nome_razao = $_POST['for_nome_razao'] ?? '';
		$for_cnpj = $_POST['for_cnpj'] ?? '';
		$for_autonomo = $_POST['for_autonomo'] ?? "0";
		$for_nome_mae = $_POST['for_nome_mae'] ?? '';
		$for_data_nasc = $_POST['for_data_nasc'] ?? '';
		$for_data_nasc = $for_data_nasc ? implode("-", array_reverse(explode("/", $for_data_nasc))) : null;
		$for_rg = $_POST['for_rg'] ?? '';
		$for_cpf = $_POST['for_cpf'] ?? '';
		$for_pis = $_POST['for_pis'] ?? '';
		$for_cep = $_POST['for_cep'] ?? '';
		$for_uf = $_POST['for_uf'] ?? null;
		$for_municipio = $_POST['for_municipio'] ?? null;
		$for_bairro = $_POST['for_bairro'] ?? '';
		$for_endereco = $_POST['for_endereco'] ?? '';
		$for_numero = $_POST['for_numero'] ?? '';
		$for_comp = $_POST['for_comp'] ?? '';
		$for_telefone = $_POST['for_telefone'] ?? '';
		$for_telefone2 = $_POST['for_telefone2'] ?? '';
		$for_telefone3 = $_POST['for_telefone3'] ?? '';
		$for_email = $_POST['for_email'] ?? '';
		$for_banco = $_POST['for_banco'] ?? '';
		$for_agencia = $_POST['for_agencia'] ?? '';
		$for_cc = $_POST['for_cc'] ?? '';
		$for_status = $_POST['for_status'] ?? '1';
		$for_observacoes = $_POST['for_observacoes'] ?? '';

		$sql = "INSERT INTO cadastro_fornecedores (
		for_nome_razao, for_cnpj, for_autonomo, for_nome_mae, for_data_nasc, for_rg, for_cpf, for_pis,
		for_cep, for_uf, for_municipio, for_bairro, for_endereco, for_numero, for_comp,
		for_telefone, for_telefone2, for_telefone3, for_email, for_banco, for_agencia, for_cc,
		for_status, for_observacoes
	) VALUES (
		:for_nome_razao, :for_cnpj, :for_autonomo, :for_nome_mae, :for_data_nasc, :for_rg, :for_cpf, :for_pis,
		:for_cep, :for_uf, :for_municipio, :for_bairro, :for_endereco, :for_numero, :for_comp,
		:for_telefone, :for_telefone2, :for_telefone3, :for_email, :for_banco, :for_agencia, :for_cc,
		:for_status, :for_observacoes
	)";
		$stmt = $pdo->prepare($sql);
		$result = $stmt->execute([
			':for_nome_razao' => $for_nome_razao,
			':for_cnpj' => $for_cnpj,
			':for_autonomo' => $for_autonomo,
			':for_nome_mae' => $for_nome_mae,
			':for_data_nasc' => $for_data_nasc,
			':for_rg' => $for_rg,
			':for_cpf' => $for_cpf,
			':for_pis' => $for_pis,
			':for_cep' => $for_cep,
			':for_uf' => $for_uf ?: null,
			':for_municipio' => $for_municipio ?: null,
			':for_bairro' => $for_bairro,
			':for_endereco' => $for_endereco,
			':for_numero' => $for_numero,
			':for_comp' => $for_comp,
			':for_telefone' => $for_telefone,
			':for_telefone2' => $for_telefone2,
			':for_telefone3' => $for_telefone3,
			':for_email' => $for_email,
			':for_banco' => $for_banco,
			':for_agencia' => $for_agencia,
			':for_cc' => $for_cc,
			':for_status' => $for_status,
			':for_observacoes' => $for_observacoes
		]);
		if ($result) {
			$ultimo_id = $pdo->lastInsertId();
			$erro = 0;
			$sql_itens = "SELECT * FROM cadastro_tipos_servicos";
			$query_itens = $pdo->query($sql_itens);
			$itens = $query_itens->fetchAll(PDO::FETCH_ASSOC);
			foreach ($itens as $item) {
				$tps_id = $item['tps_id'];
				$fse_servico = $_POST['item_check_' . $tps_id] ?? '';
				if ($fse_servico != '') {
					$sql_insere_item = "INSERT INTO cadastro_fornecedores_servicos (fse_fornecedor, fse_servico) VALUES (?, ?)";
					$stmt_item = $pdo->prepare($sql_insere_item);
					if (!$stmt_item->execute([$ultimo_id, $fse_servico])) {
						$erro = 1;
					}
				}
			}
			if ($erro != 1) {
				echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br>'+
				'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
			</SCRIPT>
			";
			} else {
				echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>'+
				'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
			</SCRIPT>
			";
			}
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
		";
		}
	}

	if ($action == 'editar') {
		$for_id = $_GET['for_id'] ?? $_POST['for_id'] ?? '';
		$for_nome_razao = $_POST['for_nome_razao'] ?? '';
		$for_cnpj = $_POST['for_cnpj'] ?? '';
		$for_autonomo = $_POST['for_autonomo'] ?? "0";
		$for_nome_mae = $_POST['for_nome_mae'] ?? '';
		$for_data_nasc = $_POST['for_data_nasc'] ?? '';
		$for_data_nasc = $for_data_nasc ? implode("-", array_reverse(explode("/", $for_data_nasc))) : null;
		$for_rg = $_POST['for_rg'] ?? '';
		$for_cpf = $_POST['for_cpf'] ?? '';
		$for_pis = $_POST['for_pis'] ?? '';
		$for_cep = $_POST['for_cep'] ?? '';
		$for_uf = $_POST['for_uf'] ?? null;
		$for_municipio = $_POST['for_municipio'] ?? null;
		$for_bairro = $_POST['for_bairro'] ?? '';
		$for_endereco = $_POST['for_endereco'] ?? '';
		$for_numero = $_POST['for_numero'] ?? '';
		$for_comp = $_POST['for_comp'] ?? '';
		$for_telefone = $_POST['for_telefone'] ?? '';
		$for_telefone2 = $_POST['for_telefone2'] ?? '';
		$for_telefone3 = $_POST['for_telefone3'] ?? '';
		$for_email = $_POST['for_email'] ?? '';
		$for_banco = $_POST['for_banco'] ?? '';
		$for_agencia = $_POST['for_agencia'] ?? '';
		$for_cc = $_POST['for_cc'] ?? '';
		$for_status = $_POST['for_status'] ?? '1';
		$for_observacoes = $_POST['for_observacoes'] ?? '';

		$sqlEnviaEdit = "UPDATE cadastro_fornecedores SET 
		for_nome_razao = :for_nome_razao,
		for_cnpj = :for_cnpj,
		for_autonomo = :for_autonomo,
		for_nome_mae = :for_nome_mae,
		for_data_nasc = :for_data_nasc,
		for_rg = :for_rg,
		for_cpf = :for_cpf,
		for_pis = :for_pis,
		for_cep = :for_cep,
		for_uf = :for_uf,
		for_municipio = :for_municipio,
		for_bairro = :for_bairro,
		for_endereco = :for_endereco,
		for_numero = :for_numero,
		for_comp = :for_comp,
		for_telefone = :for_telefone,
		for_telefone2 = :for_telefone2,
		for_telefone3 = :for_telefone3,
		for_email = :for_email,
		for_banco = :for_banco,
		for_agencia = :for_agencia,
		for_cc = :for_cc,
		for_status = :for_status,
		for_observacoes = :for_observacoes
		WHERE for_id = :for_id";
		$stmt = $pdo->prepare($sqlEnviaEdit);
		$result = $stmt->execute([
			':for_nome_razao' => $for_nome_razao,
			':for_cnpj' => $for_cnpj,
			':for_autonomo' => $for_autonomo,
			':for_nome_mae' => $for_nome_mae,
			':for_data_nasc' => $for_data_nasc,
			':for_rg' => $for_rg,
			':for_cpf' => $for_cpf,
			':for_pis' => $for_pis,
			':for_cep' => $for_cep,
			':for_uf' => $for_uf ?: null,
			':for_municipio' => $for_municipio ?: null,
			':for_bairro' => $for_bairro,
			':for_endereco' => $for_endereco,
			':for_numero' => $for_numero,
			':for_comp' => $for_comp,
			':for_telefone' => $for_telefone,
			':for_telefone2' => $for_telefone2,
			':for_telefone3' => $for_telefone3,
			':for_email' => $for_email,
			':for_banco' => $for_banco,
			':for_agencia' => $for_agencia,
			':for_cc' => $for_cc,
			':for_status' => $for_status,
			':for_observacoes' => $for_observacoes,
			':for_id' => $for_id
		]);
		if ($result) {
			$ultimo_id = $for_id;
			$erro = 0;
			$sql_itens = "SELECT * FROM cadastro_tipos_servicos";
			$query_itens = $pdo->query($sql_itens);
			$itens = $query_itens->fetchAll(PDO::FETCH_ASSOC);
			foreach ($itens as $item) {
				$tps_id = $item['tps_id'];
				$servico = $_POST['item_check_' . $tps_id] ?? '';
				$sql_compara = "SELECT * FROM cadastro_fornecedores_servicos WHERE fse_fornecedor = ? AND fse_servico = ?";
				$stmt_compara = $pdo->prepare($sql_compara);
				$stmt_compara->execute([$ultimo_id, $tps_id]);
				$rows_compara = $stmt_compara->rowCount();
				if ($rows_compara == 0 && $servico != '') {
					$sql_insere_item = "INSERT INTO cadastro_fornecedores_servicos (fse_fornecedor, fse_servico) VALUES (?, ?)";
					$stmt_item = $pdo->prepare($sql_insere_item);
					if (!$stmt_item->execute([$ultimo_id, $servico])) {
						$erro = 1;
					}
				} elseif ($rows_compara > 0 && $servico == '') {
					$row_compara = $stmt_compara->fetch(PDO::FETCH_ASSOC);
					$fse_id = $row_compara['fse_id'];
					$sql_deleta_item = "DELETE FROM cadastro_fornecedores_servicos WHERE fse_id = ?";
					$stmt_del = $pdo->prepare($sql_deleta_item);
					if (!$stmt_del->execute([$fse_id])) {
						$erro = 1;
					}
				}
			}
			if ($erro != 1) {
				echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br>'+
				'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
			</SCRIPT>
			";
			} else {
				echo "
			<SCRIPT language='JavaScript'>
				abreMask(
				'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
				'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
			</SCRIPT>
			";
			}
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
		";
		}
	}

	if ($action == 'excluir') {
		$for_id = $_GET['for_id'] ?? '';
		$sql = "DELETE FROM cadastro_fornecedores WHERE for_id = ?";
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute([$for_id])) {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
		}
	}
	if ($action == 'ativar') {
		$for_id = $_GET['for_id'] ?? '';
		$sql = "UPDATE cadastro_fornecedores SET for_status = 1 WHERE for_id = ?";
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute([$for_id])) {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Ativação realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
		}
	}
	if ($action == 'desativar') {
		$for_id = $_GET['for_id'] ?? '';
		$sql = "UPDATE cadastro_fornecedores SET for_status = 0 WHERE for_id = ?";
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute([$for_id])) {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Desativação realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
		}
	}

	$num_por_pagina = 10;
	$primeiro_registro = ($pag - 1) * $num_por_pagina;
	$fil_nome = $_REQUEST['fil_nome'] ?? '';
	$nome_query = $fil_nome == '' ? "1=1" : "for_nome_razao LIKE :fil_nome";
	$fil_for_cnpj = str_replace([".", "-"], "", $_REQUEST['fil_for_cnpj'] ?? '');
	$cnpj_query = $fil_for_cnpj == '' ? "1=1" : "REPLACE(REPLACE(for_cnpj, '.', ''), '-', '') LIKE :fil_for_cnpj";
	$fil_tipo_servico = $_REQUEST['fil_tipo_servico'] ?? '';
	if ($fil_tipo_servico == '') {
		$tipo_servico_query = "1=1";
		$fil_tipo_servico_n = "Tipo de Serviço Prestado";
	} else {
		$tipo_servico_query = "fse_servico = :fil_tipo_servico";
		$sql_tipos_servicos = "SELECT * FROM cadastro_tipos_servicos WHERE tps_id = ?";
		$query_tipos_servicos = $pdo->prepare($sql_tipos_servicos);
		$query_tipos_servicos->execute([$fil_tipo_servico]);
		$row_tipo_servico = $query_tipos_servicos->fetch(PDO::FETCH_ASSOC);
		$fil_tipo_servico_n = $row_tipo_servico['tps_nome'] ?? '';
	}
	$sql = "SELECT * FROM cadastro_fornecedores 
	LEFT JOIN cadastro_fornecedores_servicos ON cadastro_fornecedores_servicos.fse_fornecedor = cadastro_fornecedores.for_id
	WHERE $nome_query AND $cnpj_query AND $tipo_servico_query
	GROUP BY for_id
	ORDER BY for_nome_razao ASC
	LIMIT $primeiro_registro, $num_por_pagina";
	$cnt = "SELECT COUNT(DISTINCT(for_id)) FROM cadastro_fornecedores
	LEFT JOIN cadastro_fornecedores_servicos ON cadastro_fornecedores_servicos.fse_fornecedor = cadastro_fornecedores.for_id
	WHERE $nome_query AND $cnpj_query AND $tipo_servico_query";
	$params = [];
	if ($fil_nome != '')
		$params[':fil_nome'] = "%$fil_nome%";
	if ($fil_for_cnpj != '')
		$params[':fil_for_cnpj'] = "%$fil_for_cnpj%";
	if ($fil_tipo_servico != '')
		$params[':fil_tipo_servico'] = $fil_tipo_servico;

	$query = $pdo->prepare($sql);
	$query->execute($params);
	$rows = $query->rowCount();

	if ($pagina == "cadastro_fornecedores") {
		echo "<div class='titulo'> $page </div>";

		// Filtros de busca
		?>
		<form method="get" action="cadastro_fornecedores.php">
			<input type="hidden" name="pagina" value="cadastro_fornecedores">
			<input type="text" name="fil_nome" placeholder="Nome/Razão Social"
				value="<?php echo htmlspecialchars($fil_nome); ?>">
			<input type="text" name="fil_for_cnpj" placeholder="CNPJ"
				value="<?php echo htmlspecialchars($_REQUEST['fil_for_cnpj'] ?? ''); ?>">
			<select name="fil_tipo_servico">
				<option value=""><?php echo $fil_tipo_servico_n; ?></option>
				<?php
				$sql_servicos = "SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome ASC";
				$query_servicos = $pdo->query($sql_servicos);
				while ($row_servico = $query_servicos->fetch(PDO::FETCH_ASSOC)) {
					$selected = ($fil_tipo_servico == $row_servico['tps_id']) ? 'selected' : '';
					echo "<option value='{$row_servico['tps_id']}' $selected>{$row_servico['tps_nome']}</option>";
				}
				?>
			</select>
			<input type="submit" value="Filtrar">
		</form>
		<?php

		// Tabela de resultados
		echo "<table class='tabela'>";
		echo "<tr>
			<th>Nome/Razão Social</th>
			<th>CNPJ</th>
			<th>Status</th>
			<th>Ações</th>
		</tr>";

		$resultados = $query->fetchAll(PDO::FETCH_ASSOC);
		if (count($resultados) > 0) {
			foreach ($resultados as $row) {
				$status = $row['for_status'] == 1 ? 'Ativo' : 'Inativo';
				echo "<tr>";
				echo "<td>" . htmlspecialchars($row['for_nome_razao']) . "</td>";
				echo "<td>" . htmlspecialchars($row['for_cnpj']) . "</td>";
				echo "<td>$status</td>";
				echo "<td>
				<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=editar&for_id={$row['for_id']}'>Editar</a> |
				<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=excluir&for_id={$row['for_id']}' onclick=\"return confirm('Tem certeza que deseja excluir este fornecedor?');\">Excluir</a> |
				";
				if ($row['for_status'] == 1) {
					echo "<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=desativar&for_id={$row['for_id']}'>Desativar</a>";
				} else {
					echo "<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=ativar&for_id={$row['for_id']}'>Ativar</a>";
				}
				echo "</td>";
				echo "</tr>";
			}
		} else {
			echo "<tr><td colspan='4'>Nenhum fornecedor encontrado.</td></tr>";
		}
		echo "</table>";

		// Paginação
		$query_cnt = $pdo->prepare($cnt);
		$query_cnt->execute($params);
		$total_registros = $query_cnt->fetchColumn();
		$total_paginas = ceil($total_registros / $num_por_pagina);

		if ($total_paginas > 1) {
			echo "<div class='paginacao'>";
			for ($i = 1; $i <= $total_paginas; $i++) {
				$active = ($i == $pag) ? "style='font-weight:bold;'" : "";
				$querystring = http_build_query(array_merge($_GET, ['pag' => $i]));
				echo "<a href='cadastro_fornecedores.php?$querystring' $active>$i</a> ";
			}
			echo "</div>";
		}

		// Botão de adicionar novo fornecedor
		echo "<div style='margin-top:20px;'><a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=adicionar' class='botao'>Adicionar Novo Fornecedor</a></div>";
	}

	// Formulário de adicionar/editar fornecedor
	if ($action == 'adicionar' || $action == 'editar') {
		$dados = [];
		$for_id = '';
		if ($action == 'editar') {
			$for_id = $_GET['for_id'] ?? '';
			$stmt_edit = $pdo->prepare("SELECT * FROM cadastro_fornecedores WHERE for_id = ?");
			$stmt_edit->execute([$for_id]);
			$dados = $stmt_edit->fetch(PDO::FETCH_ASSOC);
			if (!$dados) {
				echo "<div class='erro'>Fornecedor não encontrado.</div>";
				return;
			}
		}

		// Campos do formulário
		$campos = [
			'for_nome_razao' => '',
			'for_cnpj' => '',
			'for_autonomo' => '0',
			'for_nome_mae' => '',
			'for_data_nasc' => '',
			'for_rg' => '',
			'for_cpf' => '',
			'for_pis' => '',
			'for_cep' => '',
			'for_uf' => '',
			'for_municipio' => '',
			'for_bairro' => '',
			'for_endereco' => '',
			'for_numero' => '',
			'for_comp' => '',
			'for_telefone' => '',
			'for_telefone2' => '',
			'for_telefone3' => '',
			'for_email' => '',
			'for_banco' => '',
			'for_agencia' => '',
			'for_cc' => '',
			'for_status' => '1',
			'for_observacoes' => ''
		];
		foreach ($campos as $campo => $valor_padrao) {
			if ($campo == 'for_data_nasc' && isset($dados[$campo]) && $dados[$campo]) {
				$$campo = date('d/m/Y', strtotime($dados[$campo]));
			} else {
				$$campo = $dados[$campo] ?? $valor_padrao;
			}
		}

		// Serviços já cadastrados para o fornecedor (em edição)
		$servicos_fornecedor = [];
		if ($action == 'editar') {
			$stmt_serv = $pdo->prepare("SELECT fse_servico FROM cadastro_fornecedores_servicos WHERE fse_fornecedor = ?");
			$stmt_serv->execute([$for_id]);
			$servicos_fornecedor = $stmt_serv->fetchAll(PDO::FETCH_COLUMN);
		}

		echo "<form method='post' action='cadastro_fornecedores.php?pagina=cadastro_fornecedores'>";
		echo "<input type='hidden' name='action' value='" . ($action == 'editar' ? 'editar' : 'adicionar') . "'>";
		if ($action == 'editar') {
			echo "<input type='hidden' name='for_id' value='" . htmlspecialchars($for_id) . "'>";
		}
		?>
		<table class="formulario">
			<tr>
				<td>Nome/Razão Social:</td>
				<td><input type="text" name="for_nome_razao" value="<?php echo htmlspecialchars($for_nome_razao); ?>"
						required></td>
			</tr>
			<tr>
				<td>CNPJ:</td>
				<td><input type="text" name="for_cnpj" value="<?php echo htmlspecialchars($for_cnpj); ?>"></td>
			</tr>
			<tr>
				<td>Autônomo:</td>
				<td>
					<select name="for_autonomo">
						<option value="0" <?php if ($for_autonomo == '0')
							echo 'selected'; ?>>Não</option>
						<option value="1" <?php if ($for_autonomo == '1')
							echo 'selected'; ?>>Sim</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Nome da Mãe:</td>
				<td><input type="text" name="for_nome_mae" value="<?php echo htmlspecialchars($for_nome_mae); ?>"></td>
			</tr>
			<tr>
				<td>Data de Nascimento:</td>
				<td><input type="text" name="for_data_nasc" value="<?php echo htmlspecialchars($for_data_nasc); ?>"></td>
			</tr>
			<tr>
				<td>RG:</td>
				<td><input type="text" name="for_rg" value="<?php echo htmlspecialchars($for_rg); ?>"></td>
			</tr>
			<tr>
				<td>CPF:</td>
				<td><input type="text" name="for_cpf" value="<?php echo htmlspecialchars($for_cpf); ?>"></td>
			</tr>
			<tr>
				<td>PIS:</td>
				<td><input type="text" name="for_pis" value="<?php echo htmlspecialchars($for_pis); ?>"></td>
			</tr>
			<tr>
				<td>CEP:</td>
				<td><input type="text" name="for_cep" value="<?php echo htmlspecialchars($for_cep); ?>"></td>
			</tr>
			<tr>
				<td>UF:</td>
				<td><input type="text" name="for_uf" value="<?php echo htmlspecialchars($for_uf); ?>"></td>
			</tr>
			<tr>
				<td>Município:</td>
				<td><input type="text" name="for_municipio" value="<?php echo htmlspecialchars($for_municipio); ?>"></td>
			</tr>
			<tr>
				<td>Bairro:</td>
				<td><input type="text" name="for_bairro" value="<?php echo htmlspecialchars($for_bairro); ?>"></td>
			</tr>
			<tr>
				<td>Endereço:</td>
				<td><input type="text" name="for_endereco" value="<?php echo htmlspecialchars($for_endereco); ?>"></td>
			</tr>
			<tr>
				<td>Número:</td>
				<td><input type="text" name="for_numero" value="<?php echo htmlspecialchars($for_numero); ?>"></td>
			</tr>
			<tr>
				<td>Complemento:</td>
				<td><input type="text" name="for_comp" value="<?php echo htmlspecialchars($for_comp); ?>"></td>
			</tr>
			<tr>
				<td>Telefone:</td>
				<td><input type="text" name="for_telefone" value="<?php echo htmlspecialchars($for_telefone); ?>"></td>
			</tr>
			<tr>
				<td>Telefone 2:</td>
				<td><input type="text" name="for_telefone2" value="<?php echo htmlspecialchars($for_telefone2); ?>"></td>
			</tr>
			<tr>
				<td>Telefone 3:</td>
				<td><input type="text" name="for_telefone3" value="<?php echo htmlspecialchars($for_telefone3); ?>"></td>
			</tr>
			<tr>
				<td>Email:</td>
				<td><input type="email" name="for_email" value="<?php echo htmlspecialchars($for_email); ?>"></td>
			</tr>
			<tr>
				<td>Banco:</td>
				<td><input type="text" name="for_banco" value="<?php echo htmlspecialchars($for_banco); ?>"></td>
			</tr>
			<tr>
				<td>Agência:</td>
				<td><input type="text" name="for_agencia" value="<?php echo htmlspecialchars($for_agencia); ?>"></td>
			</tr>
			<tr>
				<td>Conta Corrente:</td>
				<td><input type="text" name="for_cc" value="<?php echo htmlspecialchars($for_cc); ?>"></td>
			</tr>
			<tr>
				<td>Status:</td>
				<td>
					<select name="for_status">
						<option value="1" <?php if ($for_status == '1')
							echo 'selected'; ?>>Ativo</option>
						<option value="0" <?php if ($for_status == '0')
							echo 'selected'; ?>>Inativo</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Observações:</td>
				<td><textarea name="for_observacoes"><?php echo htmlspecialchars($for_observacoes); ?></textarea></td>
			</tr>
			<tr>
				<td>Serviços Prestados:</td>
				<td>
					<?php
					$query_servicos = $pdo->query("SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome ASC");
					while ($row_servico = $query_servicos->fetch(PDO::FETCH_ASSOC)) {
						$checked = ($action == 'editar' && in_array($row_servico['tps_id'], $servicos_fornecedor)) ? 'checked' : '';
						echo "<label><input type='checkbox' name='item_check_{$row_servico['tps_id']}' value='{$row_servico['tps_id']}' $checked> {$row_servico['tps_nome']}</label><br>";
					}
					?>
				</td>
			</tr>
			<tr>
				<td colspan="2" style="text-align:center;">
					<input type="submit" value="Salvar">
					<a href="cadastro_fornecedores.php?pagina=cadastro_fornecedores" class="botao">Cancelar</a>
				</td>
			</tr>
		</table>
		</form>
		<?php
	}

	include('../mod_rodape/rodape.php');
	?>
</body>

</html>