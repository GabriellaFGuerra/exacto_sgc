<?php
session_start();
$pagina_link = 'relatorio_documentos';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Funções utilitárias padronizadas
function formatarData($data)
{
	if (!$data)
		return '';
	return implode("/", array_reverse(explode("-", substr($data, 0, 10))));
}
function obterNomePeriodicidade($codigo)
{
	$periodicidades = [
		6 => "Semestral",
		12 => "Anual",
		24 => "Bienal",
		36 => "Trienal",
		48 => "Quadrienal",
		60 => "Quinquenal"
	];
	return $periodicidades[$codigo] ?? '';
}

// Variáveis de controle
$autenticacao = $_GET['autenticacao'] ?? '';
$pagina = $_GET['pagina'] ?? 'relatorio_documentos';
$tituloPagina = "Relatórios &raquo; <a href='relatorio_documentos.php?pagina=relatorio_documentos$autenticacao'>Documentos</a>";
$paginaAtual = isset($_GET['pagina_atual']) ? max(1, intval($_GET['pagina_atual'])) : 1;
$registrosPorPagina = 20;
$offset = ($paginaAtual - 1) * $registrosPorPagina;

// Filtros
$nomeCliente = $_REQUEST['fil_nome'] ?? '';
$tipoDocumento = $_REQUEST['fil_tipo_documento'] ?? '';
$dataInicio = $_REQUEST['fil_data_inicio'] ?? '';
$dataFim = $_REQUEST['fil_data_fim'] ?? '';
$periodicidade = $_REQUEST['fil_periodicidade'] ?? '';
$vencido = $_REQUEST['fil_vencido'] ?? '';
$filtroAtivo = $_REQUEST['filtro'] ?? '';

// Montagem dos filtros SQL
$condicoes = [];
$params = [];

// Filtro por nome do cliente
if ($nomeCliente !== '') {
	$condicoes[] = "cli_nome_razao LIKE :nomeCliente";
	$params['nomeCliente'] = "%$nomeCliente%";
} else {
	$condicoes[] = "1=1";
}

// Filtro por tipo de documento
if ($tipoDocumento !== '') {
	$condicoes[] = "doc_tipo = :tipoDocumento";
	$params['tipoDocumento'] = $tipoDocumento;
	$stmtTipoDoc = $pdo->prepare("SELECT tpd_nome FROM cadastro_tipos_docs WHERE tpd_id = :tpd_id");
	$stmtTipoDoc->execute(['tpd_id' => $tipoDocumento]);
	$nomeTipoDocumento = $stmtTipoDoc->fetchColumn() ?: "Tipo de Documento";
} else {
	$condicoes[] = "1=1";
	$nomeTipoDocumento = "Tipo de Documento";
}

// Filtro por data de vencimento
$dataInicioFormatada = $dataInicio ? implode('-', array_reverse(explode('/', $dataInicio))) : '';
$dataFimFormatada = $dataFim ? implode('-', array_reverse(explode('/', $dataFim))) : '';

if ($dataInicioFormatada === '' && $dataFimFormatada === '') {
	$condicoes[] = "1=1";
} elseif ($dataInicioFormatada !== '' && $dataFimFormatada === '') {
	$condicoes[] = "doc_data_vencimento >= :dataInicio";
	$params['dataInicio'] = $dataInicioFormatada;
} elseif ($dataInicioFormatada === '' && $dataFimFormatada !== '') {
	$condicoes[] = "doc_data_vencimento <= :dataFim";
	$params['dataFim'] = $dataFimFormatada;
} else {
	$condicoes[] = "doc_data_vencimento BETWEEN :dataInicio AND :dataFim";
	$params['dataInicio'] = $dataInicioFormatada;
	$params['dataFim'] = $dataFimFormatada;
}

// Filtro por periodicidade
if ($periodicidade !== '') {
	$condicoes[] = "doc_periodicidade = :periodicidade";
	$params['periodicidade'] = $periodicidade;
	$nomePeriodicidade = obterNomePeriodicidade($periodicidade);
} else {
	$condicoes[] = "1=1";
	$nomePeriodicidade = "Periodicidade";
}

// Filtro por vencido
if ($vencido === 'Sim') {
	$condicoes[] = "doc_data_vencimento <= :hoje";
	$params['hoje'] = date("Y-m-d");
	$nomeVencido = "Sim";
} elseif ($vencido === 'Não') {
	$condicoes[] = "doc_data_vencimento > :hoje";
	$params['hoje'] = date("Y-m-d");
	$nomeVencido = "Não";
} else {
	$condicoes[] = "1=1";
	$nomeVencido = "Vencido";
}

// Filtro ativo
if ($filtroAtivo === '') {
	$condicoes[] = "1=0";
} else {
	$condicoes[] = "1=1";
}

// Consulta principal com paginação
$whereSQL = implode(' AND ', $condicoes);

$sql = "
    SELECT documento_gerenciar.*, 
           cadastro_clientes.cli_nome_razao, 
           cadastro_tipos_docs.tpd_nome, 
           cadastro_tipos_servicos.tps_nome
    FROM documento_gerenciar
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
    LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
    LEFT JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
    LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
    WHERE $whereSQL
    ORDER BY doc_data_cadastro DESC
    LIMIT :limit OFFSET :offset
";
$params['limit'] = $registrosPorPagina;
$params['offset'] = $offset;

$stmt = $pdo->prepare($sql);
foreach ($params as $chave => $valor) {
	if ($chave === 'limit' || $chave === 'offset') {
		$stmt->bindValue(":$chave", $valor, PDO::PARAM_INT);
	} else {
		$stmt->bindValue(":$chave", $valor);
	}
}
$stmt->execute();
$documentos = $stmt->fetchAll();

// Consulta para total de registros (para paginação)
$sqlTotal = "
    SELECT COUNT(*) 
    FROM documento_gerenciar
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
    LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
    LEFT JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
    LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
    WHERE $whereSQL
";
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $chave => $valor) {
	if ($chave !== 'limit' && $chave !== 'offset') {
		$stmtTotal->bindValue(":$chave", $valor);
	}
}
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Logo do sistema (ajuste conforme necessário)
$logo = '../imagens/logo.png';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include "../css/style.php"; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
    <?php include '../mod_topo/topo.php'; ?>
</head>

<body>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <div class='centro'>
        <div class='titulo'><?= $tituloPagina ?></div>
        <div class='filtro'>
            <form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post'
                action='relatorio_documentos.php?pagina=relatorio_documentos<?= $autenticacao ?>&filtro=1'>
                <select name='fil_tipo_documento' id='fil_tipo_documento'>
                    <option value='<?= htmlspecialchars($tipoDocumento) ?>'><?= htmlspecialchars($nomeTipoDocumento) ?>
                    </option>
                    <?php
					$stmtTiposDocs = $pdo->query("SELECT tpd_id, tpd_nome FROM cadastro_tipos_docs ORDER BY tpd_nome");
					foreach ($stmtTiposDocs as $tipoDoc) {
						echo "<option value='{$tipoDoc['tpd_id']}'>{$tipoDoc['tpd_nome']}</option>";
					}
					?>
                    <option value=''>Todos</option>
                </select>
                <input name='fil_nome' id='fil_nome' value='<?= htmlspecialchars($nomeCliente) ?>'
                    placeholder='Cliente'>
                <select name='fil_periodicidade' id='fil_periodicidade'>
                    <option value='<?= htmlspecialchars($periodicidade) ?>'><?= htmlspecialchars($nomePeriodicidade) ?>
                    </option>
                    <option value='6'>Semestral</option>
                    <option value='12'>Anual</option>
                    <option value='24'>Bienal</option>
                    <option value='36'>Trienal</option>
                    <option value=''>Todos</option>
                </select>
                <input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início'
                    value='<?= htmlspecialchars($dataInicio) ?>' onkeypress='return mascaraData(this,event);'>
                <input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim'
                    value='<?= htmlspecialchars($dataFim) ?>' onkeypress='return mascaraData(this,event);'>
                <select name='fil_vencido' id='fil_vencido' style='width:150px;'>
                    <option value='<?= htmlspecialchars($vencido) ?>'><?= htmlspecialchars($nomeVencido) ?></option>
                    <option value='Sim'>Sim</option>
                    <option value='Não'>Não</option>
                    <option value=''>Todos</option>
                </select>
                <input type='submit' value='Filtrar'>
                <input type='button' onclick="PrintDiv('imprimir');" value='Imprimir' />
            </form>
        </div>
        <div class='contentPrint' id='imprimir'>
            <?php if ($totalRegistros > 0): ?>
            <br>
            <img src='<?= $logo ?>' border='0' class='logo' />
            <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
                <tr>
                    <td class='titulo_tabela'>Tipo de Doc</td>
                    <td class='titulo_tabela'>Cliente</td>
                    <td class='titulo_tabela'>Orçamento</td>
                    <td class='titulo_tabela' align='center'>Data Emissão</td>
                    <td class='titulo_tabela' align='center'>Periodicidade</td>
                    <td class='titulo_tabela' align='center'>Data Vencimento</td>
                    <td class='titulo_tabela' align='center'>Data Cadastro</td>
                </tr>
                <?php
					$contador = 0;
					foreach ($documentos as $doc):
						$classeLinha = $contador % 2 == 0 ? "linhaimpar" : "linhapar";
						$contador++;
						?>
                <tr class='<?= $classeLinha ?>'>
                    <td><?= htmlspecialchars($doc['tpd_nome'] ?? '') ?></td>
                    <td><?= htmlspecialchars($doc['cli_nome_razao'] ?? '') ?></td>
                    <td><?= htmlspecialchars($doc['doc_id']) ?> (<?= htmlspecialchars($doc['tps_nome'] ?? '') ?>)</td>
                    <td align='center'><?= formatarData($doc['doc_data_emissao']) ?></td>
                    <td align='center'><?= obterNomePeriodicidade($doc['doc_periodicidade']) ?></td>
                    <td align='center'><?= formatarData($doc['doc_data_vencimento']) ?></td>
                    <td align='center'>
                        <?= formatarData($doc['doc_data_cadastro']) ?>
                        <br>
                        <span class='detalhe'><?= substr($doc['doc_data_cadastro'], 11, 5) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            <!-- Paginação -->
            <div style="text-align:center; margin:20px 0;">
                <?php if ($totalPaginas > 1): ?>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <?php if ($i == $paginaAtual): ?>
                <strong><?= $i ?></strong>
                <?php else: ?>
                <a href="?pagina=relatorio_documentos<?= $autenticacao ?>&filtro=1&pagina_atual=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
                &nbsp;
                <?php endfor; ?>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <br><br><br>Selecione acima os filtros que deseja para gerar o relatório.
            <?php endif; ?>
            <div class='titulo'></div>
        </div>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
    <script src="../mod_includes/js/jquery-1.3.2.min.js"></script>
    <script src="../mod_includes/js/elementPrint.js"></script>
</body>

</html>