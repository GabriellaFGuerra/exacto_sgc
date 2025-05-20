<?php
session_start();
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogincliente.php';

// Validação do parâmetro
$orcamentoId = isset($_GET['orc_id']) ? (int)$_GET['orc_id'] : 0;
$clienteId = $_SESSION['cliente_id'] ?? 0;

if (!$orcamentoId || !$clienteId) {
    echo "<div class='centro'><br><br><br>Parâmetros inválidos.</div>";
    exit;
}

// Consulta principal do orçamento
$sql = "
    SELECT *
    FROM cadastro_orcamentos
    LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_orcamentos.orc_equipamento
    LEFT JOIN cadastro_tecnicos ON cadastro_tecnicos.tec_id = cadastro_orcamentos.orc_tecnico
    LEFT JOIN cadastro_unidades ON cadastro_unidades.uni_id = cadastro_orcamentos.orc_unidade
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente
    LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = cadastro_orcamentos.orc_id
    WHERE h1.sto_id = (
        SELECT MAX(h2.sto_id)
        FROM cadastro_status_orcamento h2
        WHERE h2.sto_orcamento = h1.sto_orcamento
    )
    AND cli_id = :cliente_id
    AND orc_id = :orcamento_id
    GROUP BY orc_id
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
$stmt->bindValue(':orcamento_id', $orcamentoId, PDO::PARAM_INT);
$stmt->execute();
$orcamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orcamento) {
    echo "<div class='centro'><br><br><br>Nenhum orçamento encontrado.</div>";
    exit;
}

// Função para formatar status
function formatarStatus($status)
{
    return match ($status) {
        1 => "<span class='preto'>Em análise</span>",
        2 => "<span class='azul'>Aberto</span>",
        3 => "<span class='laranja'>Pendente</span>",
        4 => "<span class='verde'>Finalizado</span>",
        5 => "<span class='vermelho'>Cancelado</span>",
        default => "Não especificado"
    };
}

$orcamentoDataCadastro = $orcamento['orc_data'] ? date('d/m/Y H:i', strtotime($orcamento['orc_data'])) : '';
$statusAtual = formatarStatus($orcamento['sto_status'] ?? null);

// Paginação do histórico
$itensPorPagina = 5;
$paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Conta total de eventos
$sqlTotal = "SELECT COUNT(*) FROM cadastro_status_orcamento WHERE sto_orcamento = :orcamento_id";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->bindValue(':orcamento_id', $orcamentoId, PDO::PARAM_INT);
$stmtTotal->execute();
$totalEventos = $stmtTotal->fetchColumn();

// Busca eventos paginados
$sqlHistorico = "
    SELECT *
    FROM cadastro_status_orcamento
    WHERE sto_orcamento = :orcamento_id
    ORDER BY sto_data ASC
    LIMIT :offset, :limite
";
$stmtHistorico = $pdo->prepare($sqlHistorico);
$stmtHistorico->bindValue(':orcamento_id', $orcamentoId, PDO::PARAM_INT);
$stmtHistorico->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtHistorico->bindValue(':limite', $itensPorPagina, PDO::PARAM_INT);
$stmtHistorico->execute();
$historico = $stmtHistorico->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <title>Visualizar Orçamento</title>
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/funcoes.js"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <?php include '../mod_topo_cliente/topo.php'; ?>
    <div class="centro">
        <div class="titulo">Visualizar Orçamento</div>
        <div class="quadro">
            <div style="width:90%; margin:0 auto; line-height:25px;">
                <div class="formtitulo">Dados do Orçamento</div>
                <b>Nº Protocolo:</b> <?= htmlspecialchars($orcamento['orc_ano'] . $orcamento['orc_id']) ?><br>
                <b>Situação atual:</b> <?= $statusAtual ?><br>
                <b>Data de abertura:</b> <?= $orcamentoDataCadastro ?>
                <p>
                    <b>Unidade Solicitante:</b> <?= htmlspecialchars($orcamento['uni_nome_razao'] ?? '') ?><br>
                    <b>Equipamento:</b>
                <ul>
                    <li><b>Tipo:</b> <?= htmlspecialchars($orcamento['equ_tipo'] ?? '') ?></li>
                    <li><b>Marca:</b> <?= htmlspecialchars($orcamento['equ_marca'] ?? '') ?></li>
                    <li><b>Modelo:</b> <?= htmlspecialchars($orcamento['equ_modelo'] ?? '') ?></li>
                    <li><b>Nº Série:</b> <?= htmlspecialchars($orcamento['equ_num_serie'] ?? '') ?></li>
                </ul>
                <b>Responsável:</b> <?= htmlspecialchars($orcamento['orc_responsavel'] ?? '') ?><br>
                <b>Telefone:</b> <?= htmlspecialchars($orcamento['orc_telefone'] ?? '') ?><br>
                <b>Descrição do orçamento/problema:</b><br>
                <?= nl2br(htmlspecialchars($orcamento['orc_descricao'] ?? '')) ?>
                </p>
            </div>
        </div>

        <div style="width:90%; margin:0 auto; line-height:25px;">
            <div class="formtitulo">Histórico do Orçamento</div>
            <?php if ($historico): ?>
            <section id="cd-timeline" class="cd-container">
                <?php foreach ($historico as $evento): ?>
                <?php
                    $dataEvento = $evento['sto_data'] ? date('d/m/Y H:i', strtotime($evento['sto_data'])) : '';
                    $observacao = htmlspecialchars($evento['sto_observacao'] ?? '');
                    $status = formatarStatus($evento['sto_status'] ?? null);
                    ?>
                <div class="cd-timeline-block">
                    <div class="cd-timeline-img cd-location">
                        <img src="../imagens/cd-icon-location.svg" alt="Localização">
                    </div>
                    <div class="cd-timeline-content">
                        <p><b>Status:</b> <?= $status ?></p>
                        <p><b>Observações:</b> <?= $observacao ?></p>
                        <span class="cd-date"><?= $dataEvento ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </section>
            <?php
            $totalPaginas = ceil($totalEventos / $itensPorPagina);
            if ($totalPaginas > 1): ?>
            <div class="paginacao">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <?php $classe = $i == $paginaAtual ? "pagina-atual" : ""; ?>
                <a class="<?= $classe ?>" href="?orc_id=<?= urlencode($orcamentoId) ?>&pagina=<?= $i ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <br><br><br>Nenhum histórico encontrado.
            <?php endif; ?>
        </div>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>