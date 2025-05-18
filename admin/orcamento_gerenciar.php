<?php
declare(strict_types=1);
session_start();

require_once '../mod_includes/php/connect.php';

/**
 * Exibe uma mensagem e encerra o script.
 */
function exibirMensagem(string $mensagem): void
{
	echo "<script>abreMask(`$mensagem`);</script>";
	exit;
}

/**
 * Função para criar diretórios se não existirem.
 */
function criarDiretorio(string $caminho): void
{
	if (!is_dir($caminho)) {
		mkdir($caminho, 0755, true);
	}
}

/**
 * Função para formatar valor monetário.
 */
function formatarValor(string $valor): string
{
	return str_replace(",", ".", str_replace(".", "", $valor));
}

/**
 * Função para converter data do formato brasileiro para o formato do banco.
 */
function converterData(string $data): ?string
{
	if (empty($data))
		return null;
	$dataObj = DateTime::createFromFormat('d/m/Y', $data);
	return $dataObj ? $dataObj->format('Y-m-d') : null;
}

$acao = $_GET['acao'] ?? $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? ($_POST['pagina'] ?? 'orcamento_gerenciar');
$paginaAtual = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;

// Adicionar orçamento
if ($acao === "adicionar" && $_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		$orcamento = [
			'cliente' => $_POST['orc_cliente_id'] ?? '',
			'tipo_servico' => $_POST['orc_tipo_servico'] ?? '',
			'andamento' => $_POST['orc_andamento'] ?? '',
			'observacoes' => $_POST['orc_observacoes'] ?? '',
			'usuario_responsavel' => $_POST['orc_usuario_responsavel'] ?? '',
			'gerente_responsavel' => $_POST['orc_gerente_responsavel'] ?? '',
			'prazo' => converterData($_POST['orc_prazo'] ?? ''),
			'status' => $_POST['orc_status'] ?? ''
		];

		$sql = "INSERT INTO orcamento_gerenciar 
			(orc_cliente, orc_tipo_servico, orc_andamento, orc_observacoes, orc_usuario_responsavel, orc_gerente_responsavel, orc_prazo, orc_status) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			$orcamento['cliente'],
			$orcamento['tipo_servico'],
			$orcamento['andamento'],
			$orcamento['observacoes'],
			$orcamento['usuario_responsavel'],
			$orcamento['gerente_responsavel'],
			$orcamento['prazo'],
			$orcamento['status']
		]);
		$orcamentoId = (int) $pdo->lastInsertId();

		// Status do orçamento
		$sqlStatus = "INSERT INTO cadastro_status_orcamento (sto_orcamento, sto_status, sto_observacao) VALUES (?, ?, NULL)";
		$stmtStatus = $pdo->prepare($sqlStatus);
		$stmtStatus->execute([$orcamentoId, $orcamento['status']]);

		// Fornecedores
		$fornecedores = $_POST['orc_fornecedor'] ?? [];
		$valores = $_POST['orc_valor'] ?? [];
		$observacoes = $_POST['orc_obs'] ?? [];
		$anexos = $_FILES['orc_anexo']['name'] ?? [];
		$tmpAnexos = $_FILES['orc_anexo']['tmp_name'] ?? [];
		$caminhoAnexo = "../admin/anexos/$orcamentoId/";
		criarDiretorio($caminhoAnexo);

		foreach ($fornecedores as $indice => $fornecedor) {
			$arquivo = '';
			if (!empty($anexos[$indice])) {
				$extensao = pathinfo($anexos[$indice], PATHINFO_EXTENSION);
				$arquivo = $caminhoAnexo . md5(uniqid((string) mt_rand(), true)) . '.' . $extensao;
				move_uploaded_file($tmpAnexos[$indice], $arquivo);
			}
			$sql = "INSERT INTO orcamento_fornecedor (orf_orcamento, orf_fornecedor, orf_valor, orf_obs, orf_anexo) 
					VALUES (?, ?, ?, ?, ?)";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([
				$orcamentoId,
				$fornecedor,
				formatarValor($valores[$indice] ?? ''),
				$observacoes[$indice] ?? '',
				$arquivo
			]);
		}

		// Planilha
		$planilhas = $_FILES['orc_planilha']['name'] ?? [];
		$tmpPlanilhas = $_FILES['orc_planilha']['tmp_name'] ?? [];
		$caminhoPlanilha = "../admin/planilha/$orcamentoId/";
		criarDiretorio($caminhoPlanilha);

		foreach ($planilhas as $indice => $planilha) {
			if (!empty($planilhas[$indice])) {
				$extensao = pathinfo($planilhas[$indice], PATHINFO_EXTENSION);
				$arquivo = $caminhoPlanilha . md5(uniqid((string) mt_rand(), true)) . '.' . $extensao;
				move_uploaded_file($tmpPlanilhas[$indice], $arquivo);
				$sql = "UPDATE orcamento_gerenciar SET orc_planilha = ? WHERE orc_id = ?";
				$stmt = $pdo->prepare($sql);
				$stmt->execute([$arquivo, $orcamentoId]);
			}
		}

		exibirMensagem("<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br>
			<input value=' Ok ' type='button' class='close_janela'>");
	} catch (Exception $e) {
		exibirMensagem("<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>
			<input value=' Ok ' type='button' onclick=javascript:window.history.back();>");
	}
}

// Excluir orçamento
if ($acao === 'excluir') {
	$orcamentoId = $_GET['orc_id'] ?? '';
	if ($orcamentoId) {
		$sql = "DELETE FROM orcamento_gerenciar WHERE orc_id = ?";
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute([$orcamentoId])) {
			exibirMensagem("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br>
				<input value=' OK ' type='button' class='close_janela'>");
		} else {
			exibirMensagem("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br>
				<input value=' Ok ' type='button' onclick=javascript:window.history.back(); >");
		}
	}
}

// Excluir anexo
if ($acao === 'excluir_anexo') {
	$orcamentoId = $_GET['orc_id'] ?? '';
	if ($orcamentoId) {
		$sql = "SELECT orc_planilha FROM orcamento_gerenciar WHERE orc_id = ?";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([$orcamentoId]);
		$planilha = $stmt->fetchColumn();
		$sql = "UPDATE orcamento_gerenciar SET orc_planilha = NULL WHERE orc_id = ?";
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute([$orcamentoId])) {
			if ($planilha && file_exists($planilha)) {
				unlink($planilha);
			}
			exibirMensagem("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br>
				<input value=' OK ' type='button' class='close_janela'>");
		} else {
			exibirMensagem("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br>
				<input value=' Ok ' type='button' onclick=javascript:window.history.back(); >");
		}
	}
}

// Ativar/desativar orçamento
if ($acao === 'ativar' || $acao === 'desativar') {
	$orcamentoId = $_GET['orc_id'] ?? '';
	$status = $acao === 'ativar' ? 1 : 0;
	$sql = "UPDATE orcamento_gerenciar SET orc_status = ? WHERE orc_id = ?";
	$stmt = $pdo->prepare($sql);
	if ($stmt->execute([$status, $orcamentoId])) {
		$mensagem = $status ? "Ativação realizada com sucesso" : "Desativação realizada com sucesso";
		exibirMensagem("<img src=../imagens/ok.png> $mensagem<br><br>
			<input value=' OK ' type='button' class='close_janela'>");
	} else {
		exibirMensagem("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>
			<input value=' Ok ' type='button' onclick=javascript:window.history.back(); >");
	}
}

// Filtros
$itensPorPagina = 10;
$primeiroRegistro = ($paginaAtual - 1) * $itensPorPagina;

$filtros = [
	'orc' => $_REQUEST['fil_orc'] ?? '',
	'nome' => $_REQUEST['fil_nome'] ?? '',
	'tipo_servico' => $_REQUEST['fil_tipo_servico'] ?? '',
	'data_inicio' => $_REQUEST['fil_data_inicio'] ?? '',
	'data_fim' => $_REQUEST['fil_data_fim'] ?? '',
	'status' => $_REQUEST['fil_status'] ?? '',
	'usuario_responsavel' => $_REQUEST['fil_usuario_responsavel'] ?? '',
	'gerente_responsavel' => $_REQUEST['fil_gerente_responsavel'] ?? '',
	'prazo' => $_REQUEST['fil_prazo'] ?? ''
];

$condicoes = [
	$filtros['orc'] ? "orc_id LIKE :fil_orc" : "1=1",
	$filtros['nome'] ? "cli_nome_razao LIKE :fil_nome" : "1=1",
	$filtros['tipo_servico'] ? "orc_tipo_servico = :fil_tipo_servico" : "1=1"
];

$dataQuery = "1=1";
if ($filtros['data_inicio'] || $filtros['data_fim']) {
	$dataInicio = $filtros['data_inicio'] ? converterData($filtros['data_inicio']) : '';
	$dataFim = $filtros['data_fim'] ? converterData($filtros['data_fim']) : '';
	if ($dataInicio && $dataFim) {
		$dataQuery = "orc_data_cadastro BETWEEN :data_inicio AND :data_fim";
	} elseif ($dataInicio) {
		$dataQuery = "orc_data_cadastro >= :data_inicio";
	} elseif ($dataFim) {
		$dataQuery = "orc_data_cadastro <= :data_fim";
	}
}
$condicoes[] = $dataQuery;
$condicoes[] = $filtros['status'] !== '' ? "sto_status = :fil_status" : "1=1";
$condicoes[] = $filtros['usuario_responsavel'] ? "orc_usuario_responsavel = :fil_usuario_responsavel" : "1=1";
$condicoes[] = $filtros['gerente_responsavel'] ? "orc_gerente_responsavel = :fil_gerente_responsavel" : "1=1";

$hoje = date("Y-m-d");
$doisDias = date("Y-m-d", strtotime('+2 days'));
if ($filtros['prazo'] === 'Vencido') {
	$condicoes[] = "orc_prazo < :hoje";
} elseif ($filtros['prazo'] === 'A vencer') {
	$condicoes[] = "orc_prazo >= :hoje AND orc_prazo <= :dois_dias";
} else {
	$condicoes[] = "1=1";
}

$where = implode(' AND ', $condicoes);

$sql = "SELECT SQL_CALC_FOUND_ROWS * FROM orcamento_gerenciar 
	LEFT JOIN admin_usuarios ON admin_usuarios.usu_id = orcamento_gerenciar.orc_usuario_responsavel
	LEFT JOIN cadastro_gerentes ON cadastro_gerentes.ger_id = orcamento_gerenciar.orc_gerente_responsavel
	LEFT JOIN (cadastro_clientes 
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id )
	ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
	LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
	LEFT JOIN (cadastro_status_orcamento h1 
		LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = h1.sto_fornecedor_aprovado)
	ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
	WHERE cli_deletado = 1 and cli_status = 1 
		and h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) 
		AND ucl_usuario = :usuario_id
		AND $where
	ORDER BY orc_data_cadastro DESC
	LIMIT $primeiroRegistro, $itensPorPagina";

$params = [
	':usuario_id' => $_SESSION['usuario_id']
];
if ($filtros['orc'])
	$params[':fil_orc'] = "%{$filtros['orc']}%";
if ($filtros['nome'])
	$params[':fil_nome'] = "%{$filtros['nome']}%";
if ($filtros['tipo_servico'])
	$params[':fil_tipo_servico'] = $filtros['tipo_servico'];
if ($filtros['status'] !== '')
	$params[':fil_status'] = $filtros['status'];
if ($filtros['usuario_responsavel'])
	$params[':fil_usuario_responsavel'] = $filtros['usuario_responsavel'];
if ($filtros['gerente_responsavel'])
	$params[':fil_gerente_responsavel'] = $filtros['gerente_responsavel'];
if ($filtros['prazo'] === 'Vencido' || $filtros['prazo'] === 'A vencer') {
	$params[':hoje'] = $hoje;
	if ($filtros['prazo'] === 'A vencer')
		$params[':dois_dias'] = $doisDias;
}
if ($filtros['data_inicio'])
	$params[':data_inicio'] = $dataInicio . " 00:00:00";
if ($filtros['data_fim'])
	$params[':data_fim'] = $dataFim . " 23:59:59";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Paginação
$totalRegistros = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);
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

    .paginacao {
        margin: 10px 0;
    }

    .paginacao a,
    .paginacao span {
        margin: 0 2px;
        padding: 3px 8px;
        border: 1px solid #ccc;
        text-decoration: none;
    }

    .paginacao .atual {
        background: #eee;
        font-weight: bold;
    }
    </style>
</head>

<body>
    <h2>Gerenciar Orçamentos</h2>

    <form method="get" action="">
        <input type="text" name="fil_orc" placeholder="ID Orçamento" value="<?= htmlspecialchars($filtros['orc']) ?>">
        <input type="text" name="fil_nome" placeholder="Nome Cliente" value="<?= htmlspecialchars($filtros['nome']) ?>">
        <input type="text" name="fil_data_inicio" placeholder="Data início (dd/mm/yyyy)"
            value="<?= htmlspecialchars($filtros['data_inicio']) ?>">
        <input type="text" name="fil_data_fim" placeholder="Data fim (dd/mm/yyyy)"
            value="<?= htmlspecialchars($filtros['data_fim']) ?>">
        <select name="fil_status">
            <option value="">Status</option>
            <option value="1" <?= $filtros['status'] === '1' ? 'selected' : '' ?>>Ativo</option>
            <option value="0" <?= $filtros['status'] === '0' ? 'selected' : '' ?>>Inativo</option>
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
        <?php foreach ($orcamentos as $orcamento): ?>
        <tr>
            <td><?= htmlspecialchars((string) $orcamento['orc_id']) ?></td>
            <td><?= htmlspecialchars($orcamento['cli_nome_razao'] ?? '') ?></td>
            <td><?= htmlspecialchars($orcamento['tps_nome'] ?? '') ?></td>
            <td><?= htmlspecialchars($orcamento['orc_andamento'] ?? '') ?></td>
            <td><?= !empty($orcamento['orc_prazo']) ? htmlspecialchars(date('d/m/Y', strtotime($orcamento['orc_prazo']))) : '' ?>
            </td>
            <td><?= $orcamento['orc_status'] ? 'Ativo' : 'Inativo' ?></td>
            <td>
                <a href="?acao=excluir&orc_id=<?= $orcamento['orc_id'] ?>"
                    onclick="return confirm('Excluir este orçamento?')">Excluir</a>
                <?php if ($orcamento['orc_status']): ?>
                <a href="?acao=desativar&orc_id=<?= $orcamento['orc_id'] ?>">Desativar</a>
                <?php else: ?>
                <a href="?acao=ativar&orc_id=<?= $orcamento['orc_id'] ?>">Ativar</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($totalPaginas > 1): ?>
    <div class="paginacao">
        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
        <?php if ($i == $paginaAtual): ?>
        <span class="atual"><?= $i ?></span>
        <?php else: ?>
        <a href="?<?= http_build_query(array_merge($_GET, ['pag' => $i])) ?>"><?= $i ?></a>
        <?php endif; ?>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <h3>Adicionar Orçamento</h3>
    <form method="post" enctype="multipart/form-data" action="?acao=adicionar">
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
include '../mod_rodape/rodape.php';
?>