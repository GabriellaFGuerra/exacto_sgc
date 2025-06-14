<?php
session_start();
$pagina_link = 'orcamento_gerenciar';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Funções utilitárias
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

// Variáveis de controle
$acao = $_GET['acao'] ?? $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? ($_POST['pagina'] ?? 'orcamento_gerenciar');
$autenticacao = $_GET['autenticacao'] ?? '';
$paginaAtual = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
$itensPorPagina = 10;
$primeiroRegistro = ($paginaAtual - 1) * $itensPorPagina;
$tituloPagina = "Orçamentos &raquo; <a href='orcamento_gerenciar.php?pagina=orcamento_gerenciar$autenticacao'>Gerenciar</a>";

// CRUD - Adicionar orçamento (mantido, mas remova/ajuste campos de status se não existir no banco)
if ($acao === "adicionar" && $_SERVER['REQUEST_METHOD'] === 'POST') {
	try {
		$orcamento = [
			'cliente' => $_POST['orc_cliente_id'] ?? '',
			'tipo_servico' => $_POST['orc_tipo_servico'] ?? '',
			'andamento' => $_POST['orc_andamento'] ?? '',
			'observacoes' => $_POST['orc_observacoes'] ?? '',
			'usuario_responsavel' => $_POST['orc_usuario_responsavel'] ?? '',
			'gerente_responsavel' => $_POST['orc_gerente_responsavel'] ?? '',
			'prazo' => dataParaBanco($_POST['orc_prazo'] ?? '')
		];

		$sql = "INSERT INTO orcamento_gerenciar 
            (orc_cliente, orc_tipo_servico, orc_andamento, orc_observacoes, orc_usuario_responsavel, orc_gerente_responsavel, orc_prazo) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
		$stmt = $pdo->prepare($sql);
		$stmt->execute([
			$orcamento['cliente'],
			$orcamento['tipo_servico'],
			$orcamento['andamento'],
			$orcamento['observacoes'],
			$orcamento['usuario_responsavel'],
			$orcamento['gerente_responsavel'],
			$orcamento['prazo']
		]);
		$orcamentoId = (int) $pdo->lastInsertId();

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

// Filtros dinâmicos
$params = [];
$filtros = [];
$filtrosAplicados = false;

if (!empty($_REQUEST['fil_orc'])) {
	$filtros[] = "orc_id LIKE :fil_orc";
	$params[':fil_orc'] = "%" . $_REQUEST['fil_orc'] . "%";
	$filtrosAplicados = true;
}
if (!empty($_REQUEST['fil_nome'])) {
	$filtros[] = "cli_nome_razao LIKE :fil_nome";
	$params[':fil_nome'] = "%" . $_REQUEST['fil_nome'] . "%";
	$filtrosAplicados = true;
}
if (!empty($_REQUEST['fil_tipo_servico'])) {
	$filtros[] = "orc_tipo_servico = :fil_tipo_servico";
	$params[':fil_tipo_servico'] = $_REQUEST['fil_tipo_servico'];
	$filtrosAplicados = true;
}
if (!empty($_REQUEST['fil_data_inicio']) && !empty($_REQUEST['fil_data_fim'])) {
	$filtros[] = "orc_data_cadastro BETWEEN :data_inicio AND :data_fim";
	$params[':data_inicio'] = dataParaBanco($_REQUEST['fil_data_inicio']) . " 00:00:00";
	$params[':data_fim'] = dataParaBanco($_REQUEST['fil_data_fim']) . " 23:59:59";
	$filtrosAplicados = true;
} elseif (!empty($_REQUEST['fil_data_inicio'])) {
	$filtros[] = "orc_data_cadastro >= :data_inicio";
	$params[':data_inicio'] = dataParaBanco($_REQUEST['fil_data_inicio']) . " 00:00:00";
	$filtrosAplicados = true;
} elseif (!empty($_REQUEST['fil_data_fim'])) {
	$filtros[] = "orc_data_cadastro <= :data_fim";
	$params[':data_fim'] = dataParaBanco($_REQUEST['fil_data_fim']) . " 23:59:59";
	$filtrosAplicados = true;
}

// Se nenhum filtro foi aplicado, não adiciona nenhum filtro extra (mostra todos)
$where = $filtros ? implode(' AND ', $filtros) : '1=1';

$sql = "SELECT SQL_CALC_FOUND_ROWS o.*, c.cli_nome_razao, t.tps_nome
    FROM orcamento_gerenciar o
    LEFT JOIN cadastro_clientes c ON c.cli_id = o.orc_cliente
    LEFT JOIN cadastro_tipos_servicos t ON t.tps_id = o.orc_tipo_servico
    WHERE $where
    ORDER BY o.orc_data_cadastro DESC
    LIMIT $primeiroRegistro, $itensPorPagina";

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
    <title>Orçamentos</title>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include "../css/style.php"; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
</head>

<body>
    <?php include '../mod_topo/topo.php'; ?>
    <div class='centro'>
        <div class='titulo'>
            <?= $tituloPagina ?>
        </div>
        <div id='botoes'>
            <input value='Novo Orçamento' type='button'
                onclick="window.location.href='orcamento_gerenciar.php?pagina=adicionar_orcamento_gerenciar'">
            <?= $autenticacao; ?>';" />
        </div>
        <div class='filtro'>
            <form method="get" action="orcamento_gerenciar.php">
                <input type="hidden" name="pagina" value="orcamento_gerenciar">
                <input type="text" name="fil_orc" placeholder="ID Orçamento" value="
				<?= htmlspecialchars($_REQUEST['fil_orc'] ?? '') ?>">
                <input type="text" name="fil_nome" placeholder="Nome Cliente"
                    value="<?= htmlspecialchars($_REQUEST['fil_nome'] ?? '') ?>">
                <input type="text" name="fil_data_inicio" placeholder="Data início (dd/mm/yyyy)" value="
				<?= htmlspecialchars($_REQUEST['fil_data_inicio'] ?? '') ?>">
                <input type="text" name="fil_data_fim" placeholder="Data fim (dd/mm/yyyy)"
                    value="<?= htmlspecialchars($_REQUEST['fil_data_fim'] ?? '') ?>">
                <button type="submit">Filtrar</button>
            </form>
        </div>
        <?php if ($orcamentos): ?>
        <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
            <tr>
                <td class='titulo_tabela'>ID</td>
                <td class='titulo_tabela'>Cliente</td>
                <td class=' titulo_tabela'>Tipo Serviço</td>
                <td class='titulo_tabela'>Andamento</td>
                <td class=' titulo_tabela'>Prazo</td>
                <td class='titulo_tabela'>Gerenciar</td>
            </tr>
            <?php foreach ($orcamentos as $orcamento): ?>
            <tr>
                <td><?= htmlspecialchars((string) $orcamento['orc_id']) ?></td>
                <td>
                    <?= htmlspecialchars($orcamento['cli_nome_razao'] ?? '') ?>
                </td>
                <td><?= htmlspecialchars($orcamento['tps_nome'] ?? '') ?></td>
                <td>
                    <?= htmlspecialchars($orcamento['orc_andamento'] ?? '') ?>
                </td>
                <td>
                    <?= !empty($orcamento['orc_prazo']) ? htmlspecialchars(date('d/m/Y', strtotime($orcamento['orc_prazo']))) : '' ?>
                </td>
                <td>
                    <a href="orcamento_gerenciar.php?pagina=orcamento_gerenciar&acao=excluir&orc_id=<?= $orcamento['orc_id'] ?>"
                        onclick="return confirm('Excluir este orçamento?')">Excluir</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table> <?php if ($totalPaginas > 1): ?>
        <div class="paginacao" style="text-align:center; margin:20px 0;">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <?php if ($i == $paginaAtual): ?>
            <span class="pagina-ativa"><?= $i ?></span>
            <?php else: ?>
            <a href=" orcamento_gerenciar.php?pagina=orcamento_gerenciar&pag=<?= $i ?>
				<?= $autenticacao ?>">
                <?= $i ?></a>
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