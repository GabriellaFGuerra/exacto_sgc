<?php
session_start();

require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogincliente.php';

// Funções utilitárias
function obterStatus($status)
{
	$mapa = [
		1 => "<span class='laranja'>Pendente</span>",
		2 => "<span class='azul'>Calculado</span>",
		3 => "<span class='verde'>Aprovado</span>",
		4 => "<span class='vermelho'>Reprovado</span>",
	];
	return $mapa[$status] ?? '';
}

// Configurações
$titulo = 'Consultar Orçamentos';
$itensPorPagina = 10;
$paginaAtual = isset($_REQUEST['pag']) ? max(1, intval($_REQUEST['pag'])) : 1;
$primeiroRegistro = ($paginaAtual - 1) * $itensPorPagina;

// Aprovação/Reprovação de orçamento
if (isset($_GET['action'], $_GET['orc_id'])) {
	$orcId = (int) $_GET['orc_id'];
	$dataAcao = date('Y-m-d');
	$observacao = $_POST['sto_observacao'] ?? '';

	if ($_GET['action'] === 'aprovar') {
		$fornecedorAprovado = $_POST['sto_fornecedor_aprovado'] ?? null;
		$pdo->prepare("UPDATE orcamento_gerenciar SET orc_data_aprovacao = :data WHERE orc_id = :id")
			->execute([':data' => $dataAcao, ':id' => $orcId]);
		$pdo->prepare("INSERT INTO cadastro_status_orcamento (sto_orcamento, sto_status, sto_fornecedor_aprovado, sto_observacao)
                       VALUES (:orc_id, 3, :fornecedor, :obs)")
			->execute([
				':orc_id' => $orcId,
				':fornecedor' => $fornecedorAprovado,
				':obs' => $observacao
			]);
		include '../mail/envia_email_orcamento_aprovado.php';
		echo "<script>abreMask('<img src=../imagens/ok.png> Orçamento aprovado com sucesso.<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );</script>";
	}

	if ($_GET['action'] === 'reprovar') {
		$pdo->prepare("UPDATE orcamento_gerenciar SET orc_data_aprovacao = :data WHERE orc_id = :id")
			->execute([':data' => $dataAcao, ':id' => $orcId]);
		$pdo->prepare("INSERT INTO cadastro_status_orcamento (sto_orcamento, sto_status, sto_observacao)
                       VALUES (:orc_id, 4, :obs)")
			->execute([
				':orc_id' => $orcId,
				':obs' => $observacao
			]);
		include '../mail/envia_email_orcamento_reprovado.php';
		echo "<script>abreMask('<img src=../imagens/ok.png> Orçamento reprovado com sucesso.<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );</script>";
	}
}

// Filtros
$filtroOrcamento = $_REQUEST['fil_orcamento'] ?? '';
$filtroDataInicio = $_REQUEST['fil_data_inicio'] ?? '';
$filtroDataFim = $_REQUEST['fil_data_fim'] ?? '';
$filtroStatus = $_REQUEST['fil_status'] ?? '';

$condicoes = [];
$parametros = [];

if ($filtroOrcamento) {
	$condicoes[] = "orc_id LIKE :orcamento";
	$parametros[':orcamento'] = "%$filtroOrcamento%";
}
if ($filtroDataInicio && !$filtroDataFim) {
	$condicoes[] = "orc_data_cadastro >= :data_inicio";
	$parametros[':data_inicio'] = $filtroDataInicio;
}
if (!$filtroDataInicio && $filtroDataFim) {
	$condicoes[] = "orc_data_cadastro <= :data_fim";
	$parametros[':data_fim'] = $filtroDataFim . " 23:59:59";
}
if ($filtroDataInicio && $filtroDataFim) {
	$condicoes[] = "orc_data_cadastro BETWEEN :data_inicio AND :data_fim";
	$parametros[':data_inicio'] = $filtroDataInicio;
	$parametros[':data_fim'] = $filtroDataFim . " 23:59:59";
}
if ($filtroStatus) {
	$condicoes[] = "h1.sto_status = :status";
	$parametros[':status'] = $filtroStatus;
}

// Consulta principal
$sql = "SELECT * FROM orcamento_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente 
        LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico 
        LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
        WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento) 
        AND cli_id = :cliente_id";
if ($condicoes) {
	$sql .= ' AND ' . implode(' AND ', $condicoes);
}
$sql .= " ORDER BY orc_data_cadastro DESC LIMIT :primeiro, :quantidade";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
foreach ($parametros as $param => $valor) {
	$stmt->bindValue($param, $valor, PDO::PARAM_STR);
}
$stmt->bindValue(':primeiro', $primeiroRegistro, PDO::PARAM_INT);
$stmt->bindValue(':quantidade', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Paginação
$sqlTotal = "SELECT COUNT(*) FROM orcamento_gerenciar 
    LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
    WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento) 
    AND orcamento_gerenciar.orc_cliente = :cliente_id";
if ($condicoes) {
	$sqlTotal .= ' AND ' . implode(' AND ', $condicoes);
}
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->bindValue(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
foreach ($parametros as $param => $valor) {
	$stmtTotal->bindValue($param, $valor, PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Fornecedores para aprovação
$fornecedores = $pdo->query("SELECT for_id, for_nome_razao FROM cadastro_fornecedores ORDER BY for_nome_razao")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="author" content="MogiComp">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include "../css/style.php"; ?>
    <script src="../mod_includes/js/funcoes.js"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <?php include '../mod_topo_cliente/topo.php'; ?>
</head>

<body>
    <div class="centro">
        <div class="titulo"><?= $titulo ?></div>
        <div class="filtro">
            <form method="post" action="consultar_orcamento.php?pagina=consultar_orcamento">
                <input name="fil_orcamento" placeholder="Nº Orçamento"
                    value="<?= htmlspecialchars($filtroOrcamento) ?>">
                <input type="text" name="fil_data_inicio" placeholder="Data Início"
                    value="<?= htmlspecialchars($filtroDataInicio) ?>">
                <input type="text" name="fil_data_fim" placeholder="Data Fim"
                    value="<?= htmlspecialchars($filtroDataFim) ?>">
                <select name="fil_status">
                    <option value="">Status</option>
                    <option value="1" <?= $filtroStatus == 1 ? 'selected' : '' ?>>Pendente</option>
                    <option value="2" <?= $filtroStatus == 2 ? 'selected' : '' ?>>Calculado</option>
                    <option value="3" <?= $filtroStatus == 3 ? 'selected' : '' ?>>Aprovado</option>
                    <option value="4" <?= $filtroStatus == 4 ? 'selected' : '' ?>>Reprovado</option>
                </select>
                <input type="submit" value="Filtrar">
            </form>
        </div>
        <?php if ($orcamentos): ?>
        <table class="bordatabela" width="100%" cellspacing="0" cellpadding="10">
            <tr>
                <td class="titulo_tabela">N. Orçamento</td>
                <td class="titulo_tabela">Tipo de Serviço</td>
                <td class="titulo_tabela">Observações</td>
                <td class="titulo_tabela" align="center">Data de Abertura</td>
                <td class="titulo_tabela" align="center">Status</td>
                <td class="titulo_tabela" align="center">Visualizar</td>
                <td class="titulo_tabela" align="center">Aprovar/Reprovar</td>
            </tr>
            <?php $par = false; ?>
            <?php foreach ($orcamentos as $orcamento): ?>
            <tr class="<?= $par ? 'linhapar' : 'linhaimpar' ?>">
                <td><?= htmlspecialchars($orcamento['orc_id']) ?></td>
                <td><?= htmlspecialchars($orcamento['tps_nome'] ?? $orcamento['orc_tipo_servico_cliente']) ?></td>
                <td><?= htmlspecialchars($orcamento['orc_observacoes']) ?></td>
                <td align="center"><?= date('d/m/Y', strtotime($orcamento['orc_data_cadastro'])) ?></td>
                <td align="center"><?= obterStatus($orcamento['sto_status']) ?></td>
                <td align="center">
                    <img class="mouse" src="../imagens/icon-pdf.png"
                        onclick="window.open('orcamento_imprimir.php?orc_id=<?= $orcamento['orc_id'] ?>');">
                </td>
                <td align="center">
                    <?php if ($orcamento['sto_status'] == 2): ?>
                    <a href="#"
                        onclick="abreMaskAcao('<form method=\'post\' action=\'consultar_orcamento.php?pagina=consultar_orcamento&action=aprovar&orc_id=<?= $orcamento['orc_id'] ?>\'>Selecione o fornecedor aprovado:<select name=\'sto_fornecedor_aprovado\'><?php foreach ($fornecedores as $fornecedor)
												 echo '<option value=\'' . htmlspecialchars($fornecedor['for_id']) . '\'>' . htmlspecialchars($fornecedor['for_nome_razao']) . '</option>'; ?></select><input type=\'text\' name=\'sto_observacao\' placeholder=\'Observação\'><input type=\'submit\' value=\'Aprovar\'></form>');">
                        <img border="0" src="../imagens/icon-aprovar.png">
                    </a>
                    &nbsp;&nbsp;
                    <a href="#"
                        onclick="abreMaskAcao('<form method=\'post\' action=\'consultar_orcamento.php?pagina=consultar_orcamento&action=reprovar&orc_id=<?= $orcamento['orc_id'] ?>\'><input type=\'text\' name=\'sto_observacao\' placeholder=\'Observação\'><input type=\'submit\' value=\'Reprovar\'></form>');">
                        <img border="0" src="../imagens/icon-reprovar.png">
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php $par = !$par; ?>
            <?php endforeach; ?>
        </table>
        <?php if ($totalPaginas > 1): ?>
        <div class="paginacao">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <a href="?pag=<?= $i ?><?= $filtroOrcamento ? '&fil_orcamento=' . urlencode($filtroOrcamento) : '' ?><?= $filtroDataInicio ? '&fil_data_inicio=' . urlencode($filtroDataInicio) : '' ?><?= $filtroDataFim ? '&fil_data_fim=' . urlencode($filtroDataFim) : '' ?><?= $filtroStatus ? '&fil_status=' . urlencode($filtroStatus) : '' ?>"
                class="<?= $i == $paginaAtual ? 'ativo' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <br><br><br>Não há nenhum orçamento cadastrado.
        <?php endif; ?>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>