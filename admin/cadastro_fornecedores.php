<?php
session_start();
$pagina_link = 'cadastro_fornecedores';
include '../mod_includes/php/connect.php';

function getInput($key, $default = '', $method = 'REQUEST') {
	$source = $method === 'POST' ? $_POST : ($_GET + $_POST);
	return $source[$key] ?? $default;
}

function formatDateToDb($date) {
	return $date ? implode("-", array_reverse(explode("/", $date))) : null;
}

function formatDateToBr($date) {
	return $date ? date('d/m/Y', strtotime($date)) : '';
}

function renderMessage($type, $message) {
	$icon = $type === 'success' ? 'ok.png' : 'x.png';
	$button = "<input value=' Ok ' type='button' class='close_janela'>";
	if ($type !== 'success') {
		$button = "<input value=' Ok ' type='button' onclick='javascript:window.history.back();'>";
	}
	echo "<script>abreMask('<img src=../imagens/$icon> $message<br><br>$button');</script>";
}

function handleAddFornecedor($pdo) {
	$data = [
		'for_nome_razao' => getInput('for_nome_razao'),
		'for_cnpj' => getInput('for_cnpj'),
		'for_autonomo' => getInput('for_autonomo', '0'),
		'for_nome_mae' => getInput('for_nome_mae'),
		'for_data_nasc' => formatDateToDb(getInput('for_data_nasc')),
		'for_rg' => getInput('for_rg'),
		'for_cpf' => getInput('for_cpf'),
		'for_pis' => getInput('for_pis'),
		'for_cep' => getInput('for_cep'),
		'for_uf' => getInput('for_uf', null),
		'for_municipio' => getInput('for_municipio', null),
		'for_bairro' => getInput('for_bairro'),
		'for_endereco' => getInput('for_endereco'),
		'for_numero' => getInput('for_numero'),
		'for_comp' => getInput('for_comp'),
		'for_telefone' => getInput('for_telefone'),
		'for_telefone2' => getInput('for_telefone2'),
		'for_telefone3' => getInput('for_telefone3'),
		'for_email' => getInput('for_email'),
		'for_banco' => getInput('for_banco'),
		'for_agencia' => getInput('for_agencia'),
		'for_cc' => getInput('for_cc'),
		'for_status' => getInput('for_status', '1'),
		'for_observacoes' => getInput('for_observacoes')
	];

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
	if ($stmt->execute($data)) {
		$fornecedorId = $pdo->lastInsertId();
		if (handleServicosFornecedor($pdo, $fornecedorId)) {
			renderMessage('success', 'Cadastro efetuado com sucesso.');
		} else {
			renderMessage('error', 'Erro ao cadastrar serviços. Tente novamente.');
		}
	} else {
		renderMessage('error', 'Erro ao efetuar cadastro, por favor tente novamente.');
	}
}

function handleEditFornecedor($pdo) {
	$for_id = getInput('for_id');
	$data = [
		'for_nome_razao' => getInput('for_nome_razao'),
		'for_cnpj' => getInput('for_cnpj'),
		'for_autonomo' => getInput('for_autonomo', '0'),
		'for_nome_mae' => getInput('for_nome_mae'),
		'for_data_nasc' => formatDateToDb(getInput('for_data_nasc')),
		'for_rg' => getInput('for_rg'),
		'for_cpf' => getInput('for_cpf'),
		'for_pis' => getInput('for_pis'),
		'for_cep' => getInput('for_cep'),
		'for_uf' => getInput('for_uf', null),
		'for_municipio' => getInput('for_municipio', null),
		'for_bairro' => getInput('for_bairro'),
		'for_endereco' => getInput('for_endereco'),
		'for_numero' => getInput('for_numero'),
		'for_comp' => getInput('for_comp'),
		'for_telefone' => getInput('for_telefone'),
		'for_telefone2' => getInput('for_telefone2'),
		'for_telefone3' => getInput('for_telefone3'),
		'for_email' => getInput('for_email'),
		'for_banco' => getInput('for_banco'),
		'for_agencia' => getInput('for_agencia'),
		'for_cc' => getInput('for_cc'),
		'for_status' => getInput('for_status', '1'),
		'for_observacoes' => getInput('for_observacoes'),
		'for_id' => $for_id
	];

	$sql = "UPDATE cadastro_fornecedores SET 
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
	$stmt = $pdo->prepare($sql);
	if ($stmt->execute($data)) {
		if (handleServicosFornecedor($pdo, $for_id, true)) {
			renderMessage('success', 'Dados alterados com sucesso.');
		} else {
			renderMessage('error', 'Erro ao alterar serviços. Tente novamente.');
		}
	} else {
		renderMessage('error', 'Erro ao alterar dados, por favor tente novamente.');
	}
}

function handleServicosFornecedor($pdo, $fornecedorId, $isEdit = false) {
	try {
		$sql_itens = "SELECT tps_id FROM cadastro_tipos_servicos";
		$itens = $pdo->query($sql_itens)->fetchAll(PDO::FETCH_COLUMN);
		if ($isEdit) {
			// Remove todos os serviços e insere novamente
			$pdo->prepare("DELETE FROM cadastro_fornecedores_servicos WHERE fse_fornecedor = ?")->execute([$fornecedorId]);
		}
		foreach ($itens as $tps_id) {
			$servico = getInput('item_check_' . $tps_id);
			if ($servico) {
				$sql = "INSERT INTO cadastro_fornecedores_servicos (fse_fornecedor, fse_servico) VALUES (?, ?)";
				$pdo->prepare($sql)->execute([$fornecedorId, $servico]);
			}
		}
		return true;
	} catch (Exception $e) {
		return false;
	}
}

function handleDeleteFornecedor($pdo) {
	$for_id = getInput('for_id');
	$sql = "DELETE FROM cadastro_fornecedores WHERE for_id = ?";
	$stmt = $pdo->prepare($sql);
	if ($stmt->execute([$for_id])) {
		renderMessage('success', 'Exclusão realizada com sucesso');
	} else {
		renderMessage('error', 'Este item não pode ser excluído pois está relacionado com alguma tabela.');
	}
}

function handleStatusFornecedor($pdo, $status) {
	$for_id = getInput('for_id');
	$sql = "UPDATE cadastro_fornecedores SET for_status = ? WHERE for_id = ?";
	$stmt = $pdo->prepare($sql);
	if ($stmt->execute([$status, $for_id])) {
		$msg = $status ? 'Ativação realizada com sucesso' : 'Desativação realizada com sucesso';
		renderMessage('success', $msg);
	} else {
		renderMessage('error', 'Erro ao alterar dados, por favor tente novamente.');
	}
}

function getServicos($pdo) {
	return $pdo->query("SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome ASC")->fetchAll(PDO::FETCH_ASSOC);
}

function getServicosFornecedor($pdo, $for_id) {
	$stmt = $pdo->prepare("SELECT fse_servico FROM cadastro_fornecedores_servicos WHERE fse_fornecedor = ?");
	$stmt->execute([$for_id]);
	return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function renderForm($pdo, $action, $for_id = null) {
	$dados = [];
	if ($action === 'editar' && $for_id) {
		$stmt = $pdo->prepare("SELECT * FROM cadastro_fornecedores WHERE for_id = ?");
		$stmt->execute([$for_id]);
		$dados = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$dados) {
			echo "<div class='erro'>Fornecedor não encontrado.</div>";
			return;
		}
	}
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
			$$campo = formatDateToBr($dados[$campo]);
		} else {
			$$campo = $dados[$campo] ?? $valor_padrao;
		}
	}
	$servicos_fornecedor = $action === 'editar' && $for_id ? getServicosFornecedor($pdo, $for_id) : [];
	$servicos = getServicos($pdo);
	?>
<form method='post' action='cadastro_fornecedores.php?pagina=cadastro_fornecedores'>
    <input type='hidden' name='action' value='<?php echo $action; ?>'>
    <?php if ($action === 'editar') echo "<input type='hidden' name='for_id' value='" . htmlspecialchars($for_id) . "'>"; ?>
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
                    <option value="0" <?php if ($for_autonomo == '0') echo 'selected'; ?>>Não</option>
                    <option value="1" <?php if ($for_autonomo == '1') echo 'selected'; ?>>Sim</option>
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
                    <option value="1" <?php if ($for_status == '1') echo 'selected'; ?>>Ativo</option>
                    <option value="0" <?php if ($for_status == '0') echo 'selected'; ?>>Inativo</option>
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
                <?php foreach ($servicos as $servico): 
						$checked = in_array($servico['tps_id'], $servicos_fornecedor) ? 'checked' : '';
						echo "<label><input type='checkbox' name='item_check_{$servico['tps_id']}' value='{$servico['tps_id']}' $checked> {$servico['tps_nome']}</label><br>";
					endforeach; ?>
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

function renderList($pdo, $pag, $num_por_pagina) {
	$fil_nome = getInput('fil_nome');
	$fil_for_cnpj = str_replace([".", "-"], "", getInput('fil_for_cnpj'));
	$fil_tipo_servico = getInput('fil_tipo_servico');
	$primeiro_registro = ($pag - 1) * $num_por_pagina;

	$where = [];
	$params = [];
	if ($fil_nome) {
		$where[] = "for_nome_razao LIKE :fil_nome";
		$params[':fil_nome'] = "%$fil_nome%";
	}
	if ($fil_for_cnpj) {
		$where[] = "REPLACE(REPLACE(for_cnpj, '.', ''), '-', '') LIKE :fil_for_cnpj";
		$params[':fil_for_cnpj'] = "%$fil_for_cnpj%";
	}
	if ($fil_tipo_servico) {
		$where[] = "fse_servico = :fil_tipo_servico";
		$params[':fil_tipo_servico'] = $fil_tipo_servico;
	}
	$whereSql = $where ? implode(' AND ', $where) : '1=1';

	$sql = "SELECT * FROM cadastro_fornecedores 
		LEFT JOIN cadastro_fornecedores_servicos ON cadastro_fornecedores_servicos.fse_fornecedor = cadastro_fornecedores.for_id
		WHERE $whereSql
		GROUP BY for_id
		ORDER BY for_nome_razao ASC
		LIMIT $primeiro_registro, $num_por_pagina";
	$cnt = "SELECT COUNT(DISTINCT(for_id)) FROM cadastro_fornecedores
		LEFT JOIN cadastro_fornecedores_servicos ON cadastro_fornecedores_servicos.fse_fornecedor = cadastro_fornecedores.for_id
		WHERE $whereSql";

	$query = $pdo->prepare($sql);
	$query->execute($params);
	$resultados = $query->fetchAll(PDO::FETCH_ASSOC);

	$query_cnt = $pdo->prepare($cnt);
	$query_cnt->execute($params);
	$total_registros = $query_cnt->fetchColumn();
	$total_paginas = ceil($total_registros / $num_por_pagina);

	// Filtros
	?>
<form method="get" action="cadastro_fornecedores.php">
    <input type="hidden" name="pagina" value="cadastro_fornecedores">
    <input type="text" name="fil_nome" placeholder="Nome/Razão Social"
        value="<?php echo htmlspecialchars($fil_nome); ?>">
    <input type="text" name="fil_for_cnpj" placeholder="CNPJ"
        value="<?php echo htmlspecialchars(getInput('fil_for_cnpj')); ?>">
    <select name="fil_tipo_servico">
        <option value="">Tipo de Serviço Prestado</option>
        <?php foreach (getServicos($pdo) as $servico): ?>
        <option value="<?php echo $servico['tps_id']; ?>"
            <?php if ($fil_tipo_servico == $servico['tps_id']) echo 'selected'; ?>>
            <?php echo $servico['tps_nome']; ?>
        </option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="Filtrar">
</form>
<?php

	// Tabela
	echo "<table class='tabela'>";
	echo "<tr>
		<th>Nome/Razão Social</th>
		<th>CNPJ</th>
		<th>Status</th>
		<th>Ações</th>
	</tr>";
	if ($resultados) {
		foreach ($resultados as $row) {
			$status = $row['for_status'] == 1 ? 'Ativo' : 'Inativo';
			echo "<tr>";
			echo "<td>" . htmlspecialchars($row['for_nome_razao']) . "</td>";
			echo "<td>" . htmlspecialchars($row['for_cnpj']) . "</td>";
			echo "<td>$status</td>";
			echo "<td>
				<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=editar&for_id={$row['for_id']}'>Editar</a> |
				<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=excluir&for_id={$row['for_id']}' onclick=\"return confirm('Tem certeza que deseja excluir este fornecedor?');\">Excluir</a> | ";
			if ($row['for_status'] == 1) {
				echo "<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=desativar&for_id={$row['for_id']}'>Desativar</a>";
			} else {
				echo "<a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=ativar&for_id={$row['for_id']}'>Ativar</a>";
			}
			echo "</td></tr>";
		}
	} else {
		echo "<tr><td colspan='4'>Nenhum fornecedor encontrado.</td></tr>";
	}
	echo "</table>";

	// Paginação
	if ($total_paginas > 1) {
		echo "<div class='paginacao'>";
		for ($i = 1; $i <= $total_paginas; $i++) {
			$active = ($i == $pag) ? "style='font-weight:bold;'" : "";
			$querystring = http_build_query(array_merge($_GET, ['pag' => $i]));
			echo "<a href='cadastro_fornecedores.php?$querystring' $active>$i</a> ";
		}
		echo "</div>";
	}

	// Botão adicionar
	echo "<div style='margin-top:20px;'><a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores&action=adicionar' class='botao'>Adicionar Novo Fornecedor</a></div>";
}

// --- MAIN ---
include '../mod_includes/php/funcoes-jquery.php';
require_once '../mod_includes/php/verificalogin.php';
include '../mod_topo/topo.php';
require_once '../mod_includes/php/verificapermissao.php';

$pagina = getInput('pagina');
$action = getInput('action');
$pag = (int)getInput('pag', 1);
$num_por_pagina = 10;

echo "<div class='titulo'>Cadastros &raquo; <a href='cadastro_fornecedores.php?pagina=cadastro_fornecedores'>Fornecedores</a></div>";

switch ($action) {
	case 'adicionar':
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			handleAddFornecedor($pdo);
		}
		renderForm($pdo, 'adicionar');
		break;
	case 'editar':
		$for_id = getInput('for_id');
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			handleEditFornecedor($pdo);
		}
		renderForm($pdo, 'editar', $for_id);
		break;
	case 'excluir':
		handleDeleteFornecedor($pdo);
		break;
	case 'ativar':
		handleStatusFornecedor($pdo, 1);
		break;
	case 'desativar':
		handleStatusFornecedor($pdo, 0);
		break;
	default:
		renderList($pdo, $pag, $num_por_pagina);
		break;
}

include('../mod_rodape/rodape.php');
?>