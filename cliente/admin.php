<?php
session_start();

require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogincliente.php';

$titulo = 'SGO - Sistema de Gerenciamento de Orçamentos';

// Função utilitária para status
function obterStatusOrcamento($status)
{
    $statusNomes = [
        1 => "<span class='laranja'>Pendente</span>",
        2 => "<span class='azul'>Calculado</span>",
        3 => "<span class='verde'>Aprovado</span>",
        4 => "<span class='vermelho'>Reprovado</span>"
    ];
    return $statusNomes[$status] ?? '';
}

// Paginação
$itensPorPagina = 10;
$paginaAtual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($paginaAtual - 1) * $itensPorPagina;

// Consulta total de registros
$sqlTotal = "
    SELECT COUNT(*) 
    FROM orcamento_gerenciar 
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
    LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
    LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
    WHERE h1.sto_id = (
        SELECT MAX(h2.sto_id) 
        FROM cadastro_status_orcamento h2 
        WHERE h2.sto_orcamento = h1.sto_orcamento
    )
    AND orc_cliente = :cliente_id
";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->bindValue(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Consulta dos orçamentos
$sql = "
    SELECT * 
    FROM orcamento_gerenciar 
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
    LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
    LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
    WHERE h1.sto_id = (
        SELECT MAX(h2.sto_id) 
        FROM cadastro_status_orcamento h2 
        WHERE h2.sto_orcamento = h1.sto_orcamento
    )
    AND orc_cliente = :cliente_id
    ORDER BY orc_data_cadastro DESC
    LIMIT :offset, :itensPorPagina
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':itensPorPagina', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <title>Admin - SGO</title>
    <meta name="author" content="MogiComp">
    <meta charset="UTF-8">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
    <?php
    include '../mod_includes/php/funcoes-jquery.php';
    include '../mod_topo_cliente/topo.php';
    ?>
    <div class="centro">
        <div class="titulo">Bem-vindo ao SGO - Sistema de Gerenciamento de Orçamentos</div>
        <table width="100%">
            <tr>
                <td align="justify" valign="top">
                    <div class="quadro_home">
                        <div class="formtitulo">Últimos orçamentos realizados</div>
                        <?php if ($orcamentos): ?>
                            <table class="bordatabela" width="100%" border="0" cellspacing="0" cellpadding="5">
                                <tr>
                                    <td class="titulo_tabela">N. Orçamento</td>
                                    <td class="titulo_tabela" width="150">Tipo de Serviço</td>
                                    <td class="titulo_tabela">Observações</td>
                                    <td class="titulo_tabela" align="center">Data de Abertura</td>
                                    <td class="titulo_tabela" align="center">Status</td>
                                    <td class="titulo_tabela" align="center">Visualizar</td>
                                </tr>
                                <?php
                                $alternarLinha = false;
                                foreach ($orcamentos as $orcamento):
                                    $classeLinha = $alternarLinha ? 'linhapar' : 'linhaimpar';
                                    $alternarLinha = !$alternarLinha;
                                    $orc_id = htmlspecialchars($orcamento['orc_id']);
                                    $tipoServico = htmlspecialchars($orcamento['tps_nome'] ?? $orcamento['orc_tipo_servico_cliente']);
                                    $dataCadastro = date('d/m/Y', strtotime($orcamento['orc_data_cadastro']));
                                    $horaCadastro = date('H:i', strtotime($orcamento['orc_data_cadastro']));
                                    $observacoes = htmlspecialchars($orcamento['orc_observacoes']);
                                    $status = obterStatusOrcamento($orcamento['sto_status']);
                                    ?>
                                    <tr class="<?= $classeLinha ?>">
                                        <td><?= $orc_id ?></td>
                                        <td><?= $tipoServico ?></td>
                                        <td><?= $observacoes ?></td>
                                        <td align="center"><?= $dataCadastro ?><br><span
                                                class="detalhe"><?= $horaCadastro ?></span></td>
                                        <td align="center"><?= $status ?></td>
                                        <td align="center">
                                            <img class="mouse" src="../imagens/icon-pdf.png"
                                                onclick="window.open('orcamento_imprimir.php?orc_id=<?= $orc_id ?>');">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                            <?php if ($totalPaginas > 1): ?>
                                <div class="paginacao">
                                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                        <?php if ($i == $paginaAtual): ?>
                                            <strong><?= $i ?></strong>
                                        <?php else: ?>
                                            <a href="?pagina=<?= $i ?>"><?= $i ?></a>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <br><br><br>Não há nenhum orçamento cadastrado.
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>