<?php
session_start();
require_once '../mod_includes/php/connect.php';

function obterParametro($chave, $padrao = '')
{
    return $_REQUEST[$chave] ?? $padrao;
}

$nomeCliente = obterParametro('fil_nome');
$proprietario = obterParametro('fil_proprietario');
$assunto = obterParametro('fil_assunto');
$bloco = obterParametro('fil_bloco');
$apartamento = obterParametro('fil_apto');
$tipoInfracao = obterParametro('fil_inf_tipo');
$dataInicioFiltro = obterParametro('fil_data_inicio');
$dataFimFiltro = obterParametro('fil_data_fim');

$registrosPorPagina = 10;
$paginaAtual = max(1, intval($_GET['pag'] ?? 1));
$offset = ($paginaAtual - 1) * $registrosPorPagina;

$filtros = [];
$parametros = [];

if ($nomeCliente !== '') {
    $filtros[] = 'cli_nome_razao LIKE :nomeCliente';
    $parametros[':nomeCliente'] = "%$nomeCliente%";
}
if ($proprietario !== '') {
    $filtros[] = 'inf_proprietario LIKE :proprietario';
    $parametros[':proprietario'] = "%$proprietario%";
}
if ($assunto !== '') {
    $filtros[] = 'inf_assunto LIKE :assunto';
    $parametros[':assunto'] = "%$assunto%";
}
if ($bloco !== '') {
    $filtros[] = 'inf_bloco LIKE :bloco';
    $parametros[':bloco'] = "%$bloco%";
}
if ($apartamento !== '') {
    $filtros[] = 'inf_apto LIKE :apartamento';
    $parametros[':apartamento'] = "%$apartamento%";
}
if ($tipoInfracao !== '') {
    $filtros[] = 'inf_tipo = :tipoInfracao';
    $parametros[':tipoInfracao'] = $tipoInfracao;
    $tipoInfracaoLabel = $tipoInfracao;
} else {
    $tipoInfracaoLabel = 'Tipo de infrações';
}

$dataInicio = '';
$dataFim = '';
if ($dataInicioFiltro !== '') {
    $partes = explode('/', $dataInicioFiltro);
    if (count($partes) === 3) {
        $dataInicio = "{$partes[2]}-{$partes[1]}-{$partes[0]}";
    }
}
if ($dataFimFiltro !== '') {
    $partes = explode('/', $dataFimFiltro);
    if (count($partes) === 3) {
        $dataFim = "{$partes[2]}-{$partes[1]}-{$partes[0]}";
    }
}
if ($dataInicio !== '' && $dataFim !== '') {
    $filtros[] = 'inf_data BETWEEN :dataInicio AND :dataFim';
    $parametros[':dataInicio'] = $dataInicio;
    $parametros[':dataFim'] = $dataFim;
} elseif ($dataInicio !== '') {
    $filtros[] = 'inf_data >= :dataInicio';
    $parametros[':dataInicio'] = $dataInicio;
} elseif ($dataFim !== '') {
    $filtros[] = 'inf_data <= :dataFim';
    $parametros[':dataFim'] = $dataFim;
}

$whereSql = $filtros ? implode(' AND ', $filtros) : '1=1';

$sqlTotal = "
    SELECT COUNT(*) AS total
    FROM infracoes_gerenciar
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    WHERE $whereSql
";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($parametros);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

$sql = "
    SELECT infracoes_gerenciar.*, cadastro_clientes.cli_nome_razao
    FROM infracoes_gerenciar
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    WHERE $whereSql
    ORDER BY inf_data DESC
    LIMIT :offset, :limite
";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limite', $registrosPorPagina, PDO::PARAM_INT);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tituloPagina = "Relatórios &raquo; <a href='relatorio_infracoes.php?pagina=relatorio_infracoes'>Infrações</a>";
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Relatório de Infrações</title>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php include '../mod_topo/topo.php'; ?>
    <div class="centro">
        <div class="titulo"><?php echo $tituloPagina; ?></div>
        <div class="filtro">
            <form name="form_filtro" id="form_filtro" enctype="multipart/form-data" method="get"
                action="relatorio_infracoes.php">
                <input name="fil_nome" id="fil_nome" value="<?php echo htmlspecialchars($nomeCliente); ?>"
                    placeholder="Cliente">
                <input name="fil_proprietario" id="fil_proprietario"
                    value="<?php echo htmlspecialchars($proprietario); ?>" placeholder="Proprietário">
                <input name="fil_assunto" id="fil_assunto" value="<?php echo htmlspecialchars($assunto); ?>"
                    placeholder="Assunto">
                <input name="fil_bloco" id="fil_bloco" value="<?php echo htmlspecialchars($bloco); ?>"
                    placeholder="Bloco">
                <input name="fil_apto" id="fil_apto" value="<?php echo htmlspecialchars($apartamento); ?>"
                    placeholder="Apto.">
                <select name="fil_inf_tipo" id="fil_inf_tipo">
                    <option value="<?php echo htmlspecialchars($tipoInfracao); ?>"><?php echo $tipoInfracaoLabel; ?>
                    </option>
                    <option value="Notificação de advertência por infração disciplinar">Notificação de advertência por
                        infração disciplinar</option>
                    <option value="Multa por Infração Interna">Multa por Infração Interna</option>
                    <option value="Notificação de ressarcimento">Notificação de ressarcimento</option>
                    <option value="Comunicação interna">Comunicação interna</option>
                    <option value="">Todos</option>
                </select>
                <input type="text" name="fil_data_inicio" id="fil_data_inicio" placeholder="Data Início"
                    value="<?php echo htmlspecialchars($dataInicioFiltro); ?>"
                    onkeypress="return mascaraData(this,event);">
                <input type="text" name="fil_data_fim" id="fil_data_fim" placeholder="Data Fim"
                    value="<?php echo htmlspecialchars($dataFimFiltro); ?>"
                    onkeypress="return mascaraData(this,event);">
                <input type="submit" value="Filtrar">
                <input type="button" onclick="elementPrint('imprimir');" value="Imprimir" />
            </form>
        </div>
        <div class="contentPrint" id="imprimir">
            <?php if ($totalRegistros > 0): ?>
                <br>
                <table align="center" width="100%" border="0" cellspacing="0" cellpadding="10" class="bordatabela">
                    <tr>
                        <td class="titulo_tabela">N.</td>
                        <td class="titulo_tabela">Tipo</td>
                        <td class="titulo_tabela">Assunto</td>
                        <td class="titulo_tabela">Cliente</td>
                        <td class="titulo_tabela">Proprietário</td>
                        <td class="titulo_tabela" align="center">Bloco/Apto</td>
                        <td class="titulo_tabela" align="center">Data</td>
                    </tr>
                    <?php foreach ($registros as $indice => $registro): ?>
                        <?php
                        $classeLinha = $indice % 2 == 0 ? 'linhaimpar' : 'linhapar';
                        $dataFormatada = $registro['inf_data'] ? implode('/', array_reverse(explode('-', $registro['inf_data']))) : '';
                        ?>
                        <tr class="<?php echo $classeLinha; ?>">
                            <td><?php echo str_pad($registro['inf_id'], 3, '0', STR_PAD_LEFT) . '/' . $registro['inf_ano']; ?>
                            </td>
                            <td><?php echo htmlspecialchars($registro['inf_tipo']); ?></td>
                            <td><?php echo htmlspecialchars($registro['inf_assunto']); ?></td>
                            <td><?php echo htmlspecialchars($registro['cli_nome_razao']); ?></td>
                            <td><?php echo htmlspecialchars($registro['inf_proprietario']); ?></td>
                            <td align="center">
                                <?php echo htmlspecialchars($registro['inf_bloco']) . '/' . htmlspecialchars($registro['inf_apto']); ?>
                            </td>
                            <td align="center"><?php echo $dataFormatada; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <div style="text-align:center; margin-top:20px;">
                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                        <?php if ($i == $paginaAtual): ?>
                            <strong><?php echo $i; ?></strong>
                        <?php else: ?>
                            <a href="?pagina=relatorio_infracoes&pag=<?php echo $i; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                        <?php if ($i < $totalPaginas)
                            echo ' | '; ?>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <br><br><br>Não há infrações para os filtros selecionados.
            <?php endif; ?>
            <div class="titulo"></div>
        </div>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
    <script src="../mod_includes/js/elementPrint.js"></script>
    <script>
        if (typeof elementPrint !== 'function') {
            function elementPrint(elementId) {
                var printContent = document.getElementById(elementId).innerHTML;
                var originalContent = document.body.innerHTML;
                document.body.innerHTML = printContent;
                window.print();
                document.body.innerHTML = originalContent;
                location.reload();
            }
        }
    </script>
</body>

</html>