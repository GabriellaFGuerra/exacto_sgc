<?php
session_start();
$pagina_link = 'relatorio_infracoes';

require_once '../mod_includes/php/connect.php';

// Função para obter parâmetros da requisição
function obterParametro($chave, $padrao = '')
{
    return $_REQUEST[$chave] ?? $padrao;
}

// Parâmetros de filtro
$nomeCliente = obterParametro('fil_nome');
$proprietario = obterParametro('fil_proprietario');
$assunto = obterParametro('fil_assunto');
$bloco = obterParametro('fil_bloco');
$apartamento = obterParametro('fil_apto');
$tipoInfracao = obterParametro('fil_inf_tipo');
$dataInicioFiltro = obterParametro('fil_data_inicio');
$dataFimFiltro = obterParametro('fil_data_fim');
$filtroAtivo = obterParametro('filtro');

// Parâmetros de paginação
$registrosPorPagina = 10;
$paginaAtual = max(1, intval(obterParametro('pagina', 1)));
$offset = ($paginaAtual - 1) * $registrosPorPagina;

// Montagem dos filtros SQL
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

// Conversão das datas para o formato do banco
$dataInicio = '';
$dataFim = '';
if ($dataInicioFiltro !== '') {
    $dataInicio = implode('-', array_reverse(explode('/', $dataInicioFiltro)));
}
if ($dataFimFiltro !== '') {
    $dataFim = implode('-', array_reverse(explode('/', $dataFimFiltro)));
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

// Se filtro não foi enviado, não retorna nada
if ($filtroAtivo === '') {
    $filtros[] = '1 = 0';
}

$whereSql = $filtros ? implode(' AND ', $filtros) : '1=1';

// Consulta para contar total de registros (para paginação)
$sqlTotal = "
	SELECT COUNT(*) AS total
	FROM infracoes_gerenciar
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
	WHERE $whereSql
";
$stmtTotal = $conexao->prepare($sqlTotal);
$stmtTotal->execute($parametros);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Consulta principal com paginação
$sql = "
	SELECT infracoes_gerenciar.*, cadastro_clientes.cli_nome_razao
	FROM infracoes_gerenciar
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
	WHERE $whereSql
	ORDER BY inf_data DESC
	LIMIT :offset, :limite
";
$stmt = $conexao->prepare($sql);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limite', $registrosPorPagina, PDO::PARAM_INT);
$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$tituloPagina = "Relatórios &raquo; <a href='relatorio_infracoes.php?pagina=relatorio_infracoes" . ($autenticacao ?? '') . "'>Infrações</a>";

include '../mod_includes/php/funcoes-jquery.php';
require_once '../mod_includes/php/verificalogin.php';
include '../mod_topo/topo.php';
require_once '../mod_includes/php/verificapermissao.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?php echo $titulo ?? ''; ?></title>
    <meta name="author" content="MogiComp">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js" type="text/javascript"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php include '../mod_topo/topo.php'; ?>
    <div class="centro">
        <div class="titulo"><?php echo $tituloPagina; ?></div>
        <div class="filtro">
            <form name="form_filtro" id="form_filtro" enctype="multipart/form-data" method="post"
                action="relatorio_infracoes.php?pagina=relatorio_infracoes<?php echo ($autenticacao ?? ''); ?>&filtro=1">
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
                    value="<?php echo $dataInicioFiltro; ?>" onkeypress="return mascaraData(this,event);">
                <input type="text" name="fil_data_fim" id="fil_data_fim" placeholder="Data Fim"
                    value="<?php echo $dataFimFiltro; ?>" onkeypress="return mascaraData(this,event);">
                <input type="submit" value="Filtrar">
                <input type="button" onclick="PrintDiv('imprimir');" value="Imprimir" />
            </form>
        </div>
        <div class="contentPrint" id="imprimir">
            <?php if ($totalRegistros > 0): ?>
            <br>
            <img src="<?php echo $logo ?? ''; ?>" border="0" valign="middle" class="logo" />
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
                    <td><?php echo $registro['inf_tipo']; ?></td>
                    <td><?php echo $registro['inf_assunto']; ?></td>
                    <td><?php echo $registro['cli_nome_razao']; ?></td>
                    <td><?php echo $registro['inf_proprietario']; ?></td>
                    <td align="center"><?php echo $registro['inf_bloco'] . '/' . $registro['inf_apto']; ?></td>
                    <td align="center"><?php echo $dataFormatada; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <!-- Paginação -->
            <div style="text-align:center; margin-top:20px;">
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <?php if ($i == $paginaAtual): ?>
                <strong><?php echo $i; ?></strong>
                <?php else: ?>
                <a
                    href="?pagina=relatorio_infracoes<?php echo ($autenticacao ?? ''); ?>&filtro=1&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
                <?php if ($i < $totalPaginas)
                            echo ' | '; ?>
                <?php endfor; ?>
            </div>
            <?php else: ?>
            <br><br><br>Selecione acima os filtros que deseja para gerar o relatório.
            <?php endif; ?>
            <div class="titulo"></div>
        </div>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
    <script src="../mod_includes/js/jquery-1.3.2.min.js" type="text/javascript"></script>
    <script src="../mod_includes/js/elementPrint.js" type="text/javascript"></script>
</body>

</html>