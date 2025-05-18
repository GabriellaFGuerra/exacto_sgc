<?php
declare(strict_types=1);
session_start();

require_once '../mod_includes/php/connect.php';
function abreMask(string $msg): void
{
	echo "<script>abreMask(`$msg`);</script>";
	exit;
}

$action = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? ($_POST['pagina'] ?? 'orcamento_gerenciar');
$pag = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;

// Adicionar orçamento
if ($action === "adicionar" && $_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		$orc_cliente = $_POST['orc_cliente_id'] ?? '';
		$orc_tipo_servico = $_POST['orc_tipo_servico'] ?? '';
		$orc_andamento = $_POST['orc_andamento'] ?? '';
		$orc_observacoes = $_POST['orc_observacoes'] ?? '';
		$orc_usuario_responsavel = $_POST['orc_usuario_responsavel'] ?? '';
		$orc_gerente_responsavel = $_POST['orc_gerente_responsavel'] ?? '';
		$orc_prazo = !empty($_POST['orc_prazo']) ? DateTime::createFromFormat('d/m/Y', $_POST['orc_prazo'])->format('Y-m-d') : null;
		$orc_status = $_POST['orc_status'] ?? '';

		$sql = "INSERT INTO orcamento_gerenciar 
			(orc_cliente, orc_tipo_servico, orc_andamento, orc_observacoes, orc_usuario_responsavel, orc_gerente_responsavel, orc_prazo, orc_status) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			$orc_cliente,
			$orc_tipo_servico,
			$orc_andamento,
			$orc_observacoes,
			$orc_usuario_responsavel,
			$orc_gerente_responsavel,
			$orc_prazo,
			$orc_status
		]);
		$ultimo_id = (int) $pdo->lastInsertId();

		$sql_status = "INSERT INTO cadastro_status_orcamento (sto_orcamento, sto_status, sto_observacao) VALUES (?, ?, NULL)";
		$stmt_status = $pdo->prepare($sql_status);
		$stmt_status->execute([$ultimo_id, $orc_status]);

		// Fornecedores
		$orc_fornecedor = $_POST['orc_fornecedor'] ?? [];
		$orc_valor = $_POST['orc_valor'] ?? [];
		$orc_obs = $_POST['orc_obs'] ?? [];
		$orc_anexo = $_FILES['orc_anexo']['name'] ?? [];
		$tmp_anexo = $_FILES['orc_anexo']['tmp_name'] ?? [];
		$caminho_anexo = "../admin/anexos/$ultimo_id/";
		if (!is_dir($caminho_anexo)) {
			mkdir($caminho_anexo, 0755, true);
		}

		foreach ($orc_fornecedor as $key => $value) {
			$arquivo = '';
			if (!empty($orc_anexo[$key])) {
				$extensao = pathinfo($orc_anexo[$key], PATHINFO_EXTENSION);
				$arquivo = $caminho_anexo . md5(uniqid((string) mt_rand(), true)) . '.' . $extensao;
				move_uploaded_file($tmp_anexo[$key], $arquivo);
			}
			$sql = "INSERT INTO orcamento_fornecedor (orf_orcamento, orf_fornecedor, orf_valor, orf_obs, orf_anexo) 
					VALUES (?, ?, ?, ?, ?)";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([
				$ultimo_id,
				$orc_fornecedor[$key],
				str_replace(",", ".", str_replace(".", "", $orc_valor[$key])),
				$orc_obs[$key],
				$arquivo
			]);
		}

		// Planilha
		$orc_planilha = $_FILES['orc_planilha']['name'] ?? [];
		$tmp_planilha = $_FILES['orc_planilha']['tmp_name'] ?? [];
		$caminho_planilha = "../admin/planilha/$ultimo_id/";
		if (!is_dir($caminho_planilha)) {
			mkdir($caminho_planilha, 0755, true);
		}

		foreach ($orc_planilha as $key => $value) {
			if (!empty($orc_planilha[$key])) {
				$extensao = pathinfo($orc_planilha[$key], PATHINFO_EXTENSION);
				$arquivo = $caminho_planilha . md5(uniqid((string) mt_rand(), true)) . '.' . $extensao;
				move_uploaded_file($tmp_planilha[$key], $arquivo);
				$sql = "UPDATE orcamento_gerenciar SET orc_planilha = ? WHERE orc_id = ?";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([$arquivo, $ultimo_id]);
			}
		}

		abreMask("<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br>
			<input value=' Ok ' type='button' class='close_janela'>");
	} catch (Exception $e) {
		abreMask("<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>
			<input value=' Ok ' type='button' onclick=javascript:window.history.back();>");
	}
}

// Excluir orçamento
if ($action === 'excluir') {
	$orc_id = $_GET['orc_id'] ?? '';
	if ($orc_id) {
		$sql = "DELETE FROM orcamento_gerenciar WHERE orc_id = ?";
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute([$orc_id])) {
			abreMask("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br>
				<input value=' OK ' type='button' class='close_janela'>");
		} else {
			abreMask("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br>
				<input value=' Ok ' type='button' onclick=javascript:window.history.back(); >");
		}
	}
}

// Excluir anexo
if ($action === 'excluir_anexo') {
	$orc_id = $_GET['orc_id'] ?? '';
	if ($orc_id) {
		$sql = "SELECT orc_planilha FROM orcamento_gerenciar WHERE orc_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$orc_id]);
		$orc_planilha = $stmt->fetchColumn();
		$sql = "UPDATE orcamento_gerenciar SET orc_planilha = NULL WHERE orc_id = ?";
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute([$orc_id])) {
			if ($orc_planilha && file_exists($orc_planilha)) {
				unlink($orc_planilha);
			}
			abreMask("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br>
				<input value=' OK ' type='button' class='close_janela'>");
		} else {
			abreMask("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br>
				<input value=' Ok ' type='button' onclick=javascript:window.history.back(); >");
		}
	}
}

// Ativar/desativar orçamento
if ($action === 'ativar' || $action === 'desativar') {
	$orc_id = $_GET['orc_id'] ?? '';
	$status = ($action === 'ativar') ? 1 : 0;
	$sql = "UPDATE orcamento_gerenciar SET orc_status = ? WHERE orc_id = ?";
	$stmt = $pdo->prepare($sql);
	if ($stmt->execute([$status, $orc_id])) {
		$msg = $status ? "Ativação realizada com sucesso" : "Desativação realizada com sucesso";
		abreMask("<img src=../imagens/ok.png> $msg<br><br>
			<input value=' OK ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>
			<input value=' Ok ' type='button' onclick=javascript:window.history.back(); >");
	}
}

// Filtros
$num_por_pagina = 10;
$primeiro_registro = ($pag - 1) * $num_por_pagina;

$fil_orc = $_REQUEST['fil_orc'] ?? '';
$fil_nome = $_REQUEST['fil_nome'] ?? '';
$fil_tipo_servico = $_REQUEST['fil_tipo_servico'] ?? '';
$fil_data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
$fil_data_fim = $_REQUEST['fil_data_fim'] ?? '';
$fil_status = $_REQUEST['fil_status'] ?? '';
$fil_usuario_responsavel = $_REQUEST['fil_usuario_responsavel'] ?? '';
$fil_gerente_responsavel = $_REQUEST['fil_gerente_responsavel'] ?? '';
$fil_prazo = $_REQUEST['fil_prazo'] ?? '';

$orc_query = $fil_orc ? " (orc_id LIKE :fil_orc) " : " 1 = 1 ";
$nome_query = $fil_nome ? " (cli_nome_razao LIKE :fil_nome) " : " 1 = 1 ";
$tipo_servico_query = $fil_tipo_servico ? " orc_tipo_servico = :fil_tipo_servico " : " 1 = 1 ";

$data_query = " 1 = 1 ";
if ($fil_data_inicio || $fil_data_fim) {
	$data_inicio = $fil_data_inicio ? DateTime::createFromFormat('d/m/Y', $fil_data_inicio)->format('Y-m-d') : '';
	$data_fim = $fil_data_fim ? DateTime::createFromFormat('d/m/Y', $fil_data_fim)->format('Y-m-d') : '';
	if ($data_inicio && $data_fim) {
		$data_query = " orc_data_cadastro BETWEEN :data_inicio AND :data_fim ";
	} elseif ($data_inicio) {
		$data_query = " orc_data_cadastro >= :data_inicio ";
	} elseif ($data_fim) {
		$data_query = " orc_data_cadastro <= :data_fim ";
	}
}

$status_query = $fil_status !== '' ? " (sto_status = :fil_status) " : " 1 = 1 ";
$usuario_responsavel_query = $fil_usuario_responsavel ? " orc_usuario_responsavel = :fil_usuario_responsavel " : " 1 = 1 ";
$gerente_responsavel_query = $fil_gerente_responsavel ? " orc_gerente_responsavel = :fil_gerente_responsavel " : " 1 = 1 ";

$hoje = date("Y-m-d");
$dois_dias = date("Y-m-d", strtotime('+2 days'));
if ($fil_prazo === 'Vencido') {
	$prazo_query = " orc_prazo < :hoje ";
} elseif ($fil_prazo === 'A vencer') {
	$prazo_query = " orc_prazo >= :hoje AND orc_prazo <= :dois_dias ";
} else {
	$prazo_query = " 1 = 1 ";
}

$sql = "SELECT * FROM orcamento_gerenciar 
	LEFT JOIN admin_usuarios ON admin_usuarios.usu_id = orcamento_gerenciar.orc_usuario_responsavel
	LEFT JOIN cadastro_gerentes ON cadastro_gerentes.ger_id = orcamento_gerenciar.orc_gerente_responsavel
	LEFT JOIN ( cadastro_clientes 
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
	ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
	LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
	LEFT JOIN (cadastro_status_orcamento h1 
		LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = h1.sto_fornecedor_aprovado)
	ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
	WHERE cli_deletado = 1 and cli_status = 1 
		and h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) 
		AND ucl_usuario = :usuario_id
		AND $orc_query AND $nome_query AND $tipo_servico_query AND $data_query AND $status_query AND $usuario_responsavel_query AND $gerente_responsavel_query AND $prazo_query
	ORDER BY orc_data_cadastro DESC
	LIMIT $primeiro_registro, $num_por_pagina";

$params = [
	':usuario_id' => $_SESSION['usuario_id']
];
if ($fil_orc)
	$params[':fil_orc'] = "%$fil_orc%";
if ($fil_nome)
	$params[':fil_nome'] = "%$fil_nome%";
if ($fil_tipo_servico)
	$params[':fil_tipo_servico'] = $fil_tipo_servico;
if ($fil_status !== '')
	$params[':fil_status'] = $fil_status;
if ($fil_usuario_responsavel)
	$params[':fil_usuario_responsavel'] = $fil_usuario_responsavel;
if ($fil_gerente_responsavel)
	$params[':fil_gerente_responsavel'] = $fil_gerente_responsavel;
if ($fil_prazo === 'Vencido' || $fil_prazo === 'A vencer') {
	$params[':hoje'] = $hoje;
	if ($fil_prazo === 'A vencer')
		$params[':dois_dias'] = $dois_dias;
}
if ($fil_data_inicio)
	$params[':data_inicio'] = $data_inicio . " 00:00:00";
if ($fil_data_fim)
	$params[':data_fim'] = $data_fim . " 23:59:59";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Gerenciar Orçamentos</title>
    <meta charset="utf-8">
    <style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 5px;
    }

    th {
        background: #eee;
    }
    </style>
</head>

<body>
    <h2>Gerenciar Orçamentos</h2>

    <form method="get" action="">
        <input type="text" name="fil_orc" placeholder="ID Orçamento" value="<?= htmlspecialchars($fil_orc) ?>">
        <input type="text" name="fil_nome" placeholder="Nome Cliente" value="<?= htmlspecialchars($fil_nome) ?>">
        <input type="text" name="fil_data_inicio" placeholder="Data início (dd/mm/yyyy)"
            value="<?= htmlspecialchars($fil_data_inicio) ?>">
        <input type="text" name="fil_data_fim" placeholder="Data fim (dd/mm/yyyy)"
            value="<?= htmlspecialchars($fil_data_fim) ?>">
        <select name="fil_status">
            <option value="">Status</option>
            <option value="1" <?= ($fil_status === '1' ? 'selected' : '') ?>>Ativo</option>
            <option value="0" <?= ($fil_status === '0' ? 'selected' : '') ?>>Inativo</option>
        </select>
        <button type="submit">Filtrar</button>
    </form>

    <table>
        <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Tipo Serviço</th>
            <th>Andamento</th>
            <th>Prazo</th>
            <th>Status</th>
            <th>Ações</th>
        </tr>
        <?php foreach ($resultados as $row): ?>
        <tr>
            <td><?= htmlspecialchars((string) $row['orc_id']) ?></td>
            <td><?= htmlspecialchars($row['cli_nome_razao'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['tps_nome'] ?? '') ?></td>
            <td><?= htmlspecialchars($row['orc_andamento'] ?? '') ?></td>
            <td><?= !empty($row['orc_prazo']) ? htmlspecialchars(date('d/m/Y', strtotime($row['orc_prazo']))) : '' ?>
            </td>
            <td><?= ($row['orc_status'] ? 'Ativo' : 'Inativo') ?></td>
            <td>
                <a href="?action=excluir&orc_id=<?= $row['orc_id'] ?>"
                    onclick="return confirm('Excluir este orçamento?')">Excluir</a>
                <?php if ($row['orc_status']): ?>
                <a href="?action=desativar&orc_id=<?= $row['orc_id'] ?>">Desativar</a>
                <?php else: ?>
                <a href="?action=ativar&orc_id=<?= $row['orc_id'] ?>">Ativar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h3>Adicionar Orçamento</h3>
    <form method="post" enctype="multipart/form-data" action="?action=adicionar">
        <input type="hidden" name="pagina" value="orcamento_gerenciar">
        Cliente: <input type="text" name="orc_cliente_id" required><br>
        Tipo Serviço: <input type="text" name="orc_tipo_servico" required><br>
        Andamento: <input type="text" name="orc_andamento"><br>
        Observações: <textarea name="orc_observacoes"></textarea><br>
        Usuário Responsável: <input type="text" name="orc_usuario_responsavel"><br>
        Gerente Responsável: <input type="text" name="orc_gerente_responsavel"><br>
        Prazo: <input type="text" name="orc_prazo" placeholder="dd/mm/yyyy"><br>
        Status: <select name="orc_status">
            <option value="1">Ativo</option>
            <option value="0">Inativo</option>
        </select><br>
        <h4>Fornecedores</h4>
        <div id="fornecedores">
            <div>
                Fornecedor: <input type="text" name="orc_fornecedor[]">
                Valor: <input type="text" name="orc_valor[]">
                Obs: <input type="text" name="orc_obs[]">
                Anexo: <input type="file" name="orc_anexo[]">
            </div>
        </div>
        <button type="button" onclick="adicionarFornecedor()">Adicionar Fornecedor</button>
        <h4>Planilha</h4>
        <input type="file" name="orc_planilha[]"><br>
        <button type="submit">Cadastrar</button>
    </form>
    <script>
    function adicionarFornecedor() {
        var div = document.createElement('div');
        div.innerHTML =
            'Fornecedor: <input type="text" name="orc_fornecedor[]"> Valor: <input type="text" name="orc_valor[]"> Obs: <input type="text" name="orc_obs[]"> Anexo: <input type="file" name="orc_anexo[]">';
        document.getElementById('fornecedores').appendChild(div);
    }
    </script>
</body>

</html>
<?php
include('../mod_rodape/rodape.php');
?>