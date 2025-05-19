<?php
session_start();
$pagina_link = 'orcamento_gerenciar';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Funções utilitárias padronizadas
function exibirMensagem($mensagem, $url = 'orcamento_gerenciar.php?pagina=orcamento_gerenciar')
{
	$msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
	echo "<script>alert('$msg'); window.location.href = '$url';</script>";
	exit;
}
function formatarValor($valor)
{
	return str_replace(",", ".", str_replace(".", "", $valor));
}
function dataParaBanco($data)
{
	if (empty($data))
		return null;
	$partes = explode('/', $data);
	return (count($partes) === 3) ? "{$partes[2]}-{$partes[1]}-{$partes[0]}" : $data;
}
function dataParaBR($data)
{
	if (empty($data))
		return '';
	$partes = explode('-', $data);
	return (count($partes) === 3) ? "{$partes[2]}/{$partes[1]}/{$partes[0]}" : $data;
}
function criarDiretorio($caminho)
{
	if (!is_dir($caminho)) {
		mkdir($caminho, 0755, true);
	}
}

// Variáveis de controle padronizadas
$acao = $_GET['acao'] ?? $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? ($_POST['pagina'] ?? 'orcamento_gerenciar');
$autenticacao = $_GET['autenticacao'] ?? '';
$paginaAtual = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
$itensPorPagina = 10;
$primeiroRegistro = ($paginaAtual - 1) * $itensPorPagina;
$tituloPagina = "Orçamentos &raquo; <a href='orcamento_gerenciar.php?pagina=orcamento_gerenciar$autenticacao'>Gerenciar</a>";

// CRUD - Adicionar orçamento
if ($acao === "adicionar" && $_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		$orcamento = [
			'cliente' => $_POST['orc_cliente_id'] ?? '',
			'tipo_servico' => $_POST['orc_tipo_servico'] ?? '',
			'andamento' => $_POST['orc_andamento'] ?? '',
			'observacoes' => $_POST['orc_observacoes'] ?? '',
			'usuario_responsavel' => $_POST['orc_usuario_responsavel'] ?? '',
			'gerente_responsavel' => $_POST['orc_gerente_responsavel'] ?? '',
			'prazo' => dataParaBanco($_POST['orc_prazo'] ?? ''),
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

		exibirMensagem('Cadastro efetuado com sucesso.');
	} catch (Exception $e) {
		exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
	}
}

// Excluir orçamento
if ($acao === 'excluir') {
	$orcamentoId = $_GET['orc_id'] ?? '';
	if ($orcamentoId) {
		$sql = "DELETE FROM orcamento_gerenciar WHERE orc_id = ?";
		$stmt = $pdo->prepare($sql);
		if ($stmt->execute([$orcamentoId])) {
			exibirMensagem('Exclusão realizada com sucesso.');
		} else {
			exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
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
			exibirMensagem('Exclusão realizada com sucesso.');
		} else {
			exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
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
		exibirMensagem($mensagem);
	} else {
		exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
	}
}

// Filtros
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
	$dataInicio = $filtros['data_inicio'] ? dataParaBanco($filtros['data_inicio']) : '';
	$dataFim = $filtros['data_fim'] ? dataParaBanco($filtros['data_fim']) : '';
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
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include "../css/style.php"; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <?php include '../mod_topo/topo.php'; ?>
</head>

<body>
    <div class='centro'>
        <div class='titulo'> <?= $tituloPagina ?> </div>
        <div id='botoes'>
            <input value='Novo Orçamento' type='button'
                onclick="window.location.href='orcamento_gerenciar.php?pagina=adicionar_orcamento_gerenciar<?= $autenticacao; ?>';" />
        </div>
        <div class='filtro'>
            <form method="get" action="orcamento_gerenciar.php">
                <input type="hidden" name="pagina" value="orcamento_gerenciar">
                <input type="text" name="fil_orc" placeholder="ID Orçamento"
                    value="<?= htmlspecialchars($filtros['orc']) ?>">
                <input type="text" name="fil_nome" placeholder="Nome Cliente"
                    value="<?= htmlspecialchars($filtros['nome']) ?>">
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
        </div>
        <?php if ($orcamentos): ?>
        <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
            <tr>
                <td class='titulo_tabela'>ID</td>
                <td class='titulo_tabela'>Cliente</td>
                <td class='titulo_tabela'>Tipo Serviço</td>
                <td class='titulo_tabela'>Andamento</td>
                <td class='titulo_tabela'>Prazo</td>
                <td class='titulo_tabela'>Status</td>
                <td class='titulo_tabela'>Gerenciar</td>
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
                    <a href="orcamento_gerenciar.php?pagina=orcamento_gerenciar&acao=excluir&orc_id=<?= $orcamento['orc_id'] ?>"
                        onclick="return confirm('Excluir este orçamento?')">Excluir</a>
                    <?php if ($orcamento['orc_status']): ?>
                    <a
                        href="orcamento_gerenciar.php?pagina=orcamento_gerenciar&acao=desativar&orc_id=<?= $orcamento['orc_id'] ?>">Desativar</a>
                    <?php else: ?>
                    <a
                        href="orcamento_gerenciar.php?pagina=orcamento_gerenciar&acao=ativar&orc_id=<?= $orcamento['orc_id'] ?>">Ativar</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php if ($totalPaginas > 1): ?>
        <div class="paginacao" style="text-align:center; margin:20px 0;">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <?php if ($i == $paginaAtual): ?>
            <span class="pagina-ativa"><?= $i ?></span>
            <?php else: ?>
            <a href="orcamento_gerenciar.php?pagina=orcamento_gerenciar&pag=<?= $i ?><?= $autenticacao ?>"><?= $i ?></a>
            <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <br><br><br>Não há nenhum orçamento cadastrado.
        <?php endif; ?>
        <div class='titulo'></div>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>