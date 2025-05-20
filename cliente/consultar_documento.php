<?php
session_start();

require_once '../mod_includes/php/connect.php';

// Configurações de paginação
$itensPorPagina = 10;
$paginaAtual = isset($_REQUEST['pag']) ? max(1, intval($_REQUEST['pag'])) : 1;
$primeiroRegistro = ($paginaAtual - 1) * $itensPorPagina;

// Filtros
$tipoDocumentoFiltro = $_REQUEST['fil_doc_tipo'] ?? '';
$dataInicioFiltro = $_REQUEST['fil_data_inicio'] ?? '';
$dataFimFiltro = $_REQUEST['fil_data_fim'] ?? '';

// Função utilitária para manter o filtro selecionado
function selected($valor, $comparacao)
{
	return $valor == $comparacao ? 'selected' : '';
}

// Função para formatar datas
function formatarData($data, $formato = 'd/m/Y')
{
	if (!$data)
		return '';
	return date($formato, strtotime($data));
}

// Montagem dos filtros SQL
$where = ["cli_id = :clienteId"];
$params = [':clienteId' => $_SESSION['cliente_id']];

// Filtro por tipo de documento
if ($tipoDocumentoFiltro !== '') {
	$where[] = "doc_tipo = :tipoDocumentoFiltro";
	$params[':tipoDocumentoFiltro'] = $tipoDocumentoFiltro;
	$stmtTipoDoc = $pdo->prepare("SELECT tpd_nome FROM cadastro_tipos_docs WHERE tpd_id = :tipoDocumentoFiltro");
	$stmtTipoDoc->bindValue(':tipoDocumentoFiltro', $tipoDocumentoFiltro, PDO::PARAM_INT);
	$stmtTipoDoc->execute();
	$nomeTipoDocumento = $stmtTipoDoc->fetchColumn() ?: "Tipo de documento";
} else {
	$nomeTipoDocumento = "Tipo de documento";
}

// Filtro por data
if ($dataInicioFiltro && $dataFimFiltro) {
	$where[] = "doc_data_cadastro BETWEEN :dataInicioFiltro AND :dataFimFiltro";
	$params[':dataInicioFiltro'] = $dataInicioFiltro;
	$params[':dataFimFiltro'] = $dataFimFiltro;
} elseif ($dataInicioFiltro) {
	$where[] = "doc_data_cadastro >= :dataInicioFiltro";
	$params[':dataInicioFiltro'] = $dataInicioFiltro;
} elseif ($dataFimFiltro) {
	$where[] = "doc_data_cadastro <= :dataFimFiltro";
	$params[':dataFimFiltro'] = $dataFimFiltro;
}

$whereSql = implode(' AND ', $where);

// Consulta total de registros para paginação
$sqlTotal = "
    SELECT COUNT(*) 
    FROM documento_gerenciar 
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
    WHERE $whereSql
";
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $chave => $valor) {
	$stmtTotal->bindValue($chave, $valor);
}
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Consulta dos documentos
$sql = "
    SELECT documento_gerenciar.*, cadastro_clientes.cli_nome_razao, cadastro_tipos_docs.tpd_nome, 
           orcamento_gerenciar.orc_id, cadastro_tipos_servicos.tps_nome
    FROM documento_gerenciar 
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
    LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
    LEFT JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
    LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
    WHERE $whereSql
    ORDER BY doc_data_cadastro DESC
    LIMIT :primeiroRegistro, :itensPorPagina
";
$stmt = $pdo->prepare($sql);
foreach ($params as $chave => $valor) {
	$stmt->bindValue($chave, $valor);
}
$stmt->bindValue(':primeiroRegistro', $primeiroRegistro, PDO::PARAM_INT);
$stmt->bindValue(':itensPorPagina', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Consultar Documentos</title>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <div class="centro">
        <div class="titulo">Consultar Documentos</div>
        <div class="filtro">
            <form name="form_filtro" id="form_filtro" method="post"
                action="consultar_documento.php?pagina=consultar_documento">
                <select name="fil_doc_tipo" id="fil_doc_tipo">
                    <option value=""><?= htmlspecialchars($nomeTipoDocumento) ?></option>
                    <?php
					$stmtTipos = $pdo->query("SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC");
					while ($tipo = $stmtTipos->fetch(PDO::FETCH_ASSOC)) {
						echo "<option value='" . htmlspecialchars($tipo['tpd_id']) . "' " . selected($tipoDocumentoFiltro, $tipo['tpd_id']) . ">" . htmlspecialchars($tipo['tpd_nome']) . "</option>";
					}
					?>
                    <option value="" <?= $tipoDocumentoFiltro === '' ? 'selected' : '' ?>>Todos</option>
                </select>
                <input type="text" name="fil_data_inicio" id="fil_data_inicio" placeholder="Data Início"
                    value="<?= htmlspecialchars($dataInicioFiltro) ?>">
                <input type="text" name="fil_data_fim" id="fil_data_fim" placeholder="Data Fim"
                    value="<?= htmlspecialchars($dataFimFiltro) ?>">
                <input type="submit" value="Filtrar">
            </form>
        </div>
        <?php if ($documentos): ?>
        <table class="bordatabela" width="100%" border="0" cellspacing="0" cellpadding="10">
            <tr>
                <td class="titulo_tabela">Tipo de Documento</td>
                <td class="titulo_tabela">Orçamento</td>
                <td class="titulo_tabela" align="center">Data Emissão</td>
                <td class="titulo_tabela" align="center">Periodicidade</td>
                <td class="titulo_tabela" align="center">Data Vencimento</td>
                <td class="titulo_tabela" align="center">Data Cadastro</td>
                <td class="titulo_tabela" align="center">Anexo</td>
            </tr>
            <?php
				$classeLinha = 'linhaimpar';
				foreach ($documentos as $doc):
					?>
            <tr class="<?= $classeLinha ?>">
                <td><?= htmlspecialchars($doc['tpd_nome']) ?></td>
                <td><?= htmlspecialchars($doc['orc_id']) . " (" . htmlspecialchars($doc['tps_nome']) . ")" ?></td>
                <td align="center"><?= formatarData($doc['doc_data_emissao']) ?></td>
                <td align="center"><?= htmlspecialchars($doc['doc_periodicidade']) ?></td>
                <td align="center"><?= formatarData($doc['doc_data_vencimento']) ?></td>
                <td align="center"><?= formatarData($doc['doc_data_cadastro'], 'd/m/Y H:i') ?></td>
                <td align="center">
                    <?php if (!empty($doc['doc_anexo'])): ?>
                    <a href="<?= htmlspecialchars($doc['doc_anexo']) ?>" target="_blank">
                        <img src="../imagens/icon-pdf.png" alt="Anexo PDF">
                    </a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php $classeLinha = $classeLinha === 'linhaimpar' ? 'linhapar' : 'linhaimpar'; ?>
            <?php endforeach; ?>
        </table>
        <!-- Paginação -->
        <?php if ($totalPaginas > 1): ?>
        <div class="paginacao" style="text-align:center; margin-top:20px;">
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
            <?php if ($i == $paginaAtual): ?>
            <strong><?= $i ?></strong>
            <?php else: ?>
            <a
                href="?pag=<?= $i ?>&fil_doc_tipo=<?= urlencode($tipoDocumentoFiltro) ?>&fil_data_inicio=<?= urlencode($dataInicioFiltro) ?>&fil_data_fim=<?= urlencode($dataFimFiltro) ?>"><?= $i ?></a>
            <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <br><br><br>Não há nenhum documento cadastrado.
        <?php endif; ?>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>