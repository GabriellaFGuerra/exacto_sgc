<?php
session_start();
require_once '../mod_includes/php/connect.php';

// Configurações de paginação
$itensPorPagina = 10;
$paginaAtual = isset($_REQUEST['pag']) ? (int) $_REQUEST['pag'] : 1;
$primeiroRegistro = ($paginaAtual - 1) * $itensPorPagina;

// Filtros
$tipoDocumentoFiltro = $_REQUEST['fil_doc_tipo'] ?? '';
$dataInicioFiltro = $_REQUEST['fil_data_inicio'] ?? '';
$dataFimFiltro = $_REQUEST['fil_data_fim'] ?? '';

// Montagem do filtro de tipo de documento
if ($tipoDocumentoFiltro === '') {
	$condicaoTipoDocumento = "1 = 1";
	$nomeTipoDocumento = "Tipo de documento";
} else {
	$condicaoTipoDocumento = "doc_tipo = :tipoDocumentoFiltro";
	$stmtTipoDoc = $pdo->prepare("SELECT tpd_nome FROM cadastro_tipos_docs WHERE tpd_id = :tipoDocumentoFiltro");
	$stmtTipoDoc->bindParam(':tipoDocumentoFiltro', $tipoDocumentoFiltro, PDO::PARAM_INT);
	$stmtTipoDoc->execute();
	$nomeTipoDocumento = $stmtTipoDoc->fetchColumn() ?: "Tipo de documento";
}

// Montagem do filtro de data
$condicaoData = "1 = 1";
if ($dataInicioFiltro && !$dataFimFiltro) {
	$condicaoData = "doc_data_cadastro >= :dataInicioFiltro";
} elseif (!$dataInicioFiltro && $dataFimFiltro) {
	$condicaoData = "doc_data_cadastro <= :dataFimFiltro";
} elseif ($dataInicioFiltro && $dataFimFiltro) {
	$condicaoData = "doc_data_cadastro BETWEEN :dataInicioFiltro AND :dataFimFiltro";
}

// Consulta total de registros para paginação
$sqlTotal = "SELECT COUNT(*) FROM documento_gerenciar 
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
	WHERE cli_id = :clienteId AND $condicaoTipoDocumento AND $condicaoData";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->bindValue(':clienteId', $_SESSION['cliente_id'], PDO::PARAM_INT);
if ($tipoDocumentoFiltro)
	$stmtTotal->bindValue(':tipoDocumentoFiltro', $tipoDocumentoFiltro, PDO::PARAM_INT);
if ($dataInicioFiltro)
	$stmtTotal->bindValue(':dataInicioFiltro', $dataInicioFiltro, PDO::PARAM_STR);
if ($dataFimFiltro)
	$stmtTotal->bindValue(':dataFimFiltro', $dataFimFiltro, PDO::PARAM_STR);
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Consulta dos documentos
$sql = "SELECT * FROM documento_gerenciar 
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
	LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
	LEFT JOIN (orcamento_gerenciar 
		LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
	ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
	WHERE cli_id = :clienteId AND $condicaoTipoDocumento AND $condicaoData
	ORDER BY doc_data_cadastro DESC
	LIMIT :primeiroRegistro, :itensPorPagina";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':clienteId', $_SESSION['cliente_id'], PDO::PARAM_INT);
if ($tipoDocumentoFiltro)
	$stmt->bindValue(':tipoDocumentoFiltro', $tipoDocumentoFiltro, PDO::PARAM_INT);
if ($dataInicioFiltro)
	$stmt->bindValue(':dataInicioFiltro', $dataInicioFiltro, PDO::PARAM_STR);
if ($dataFimFiltro)
	$stmt->bindValue(':dataFimFiltro', $dataFimFiltro, PDO::PARAM_STR);
$stmt->bindValue(':primeiroRegistro', $primeiroRegistro, PDO::PARAM_INT);
$stmt->bindValue(':itensPorPagina', $itensPorPagina, PDO::PARAM_INT);
$stmt->execute();
$documentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para manter o filtro selecionado
function selected($valor, $comparacao)
{
	return $valor == $comparacao ? 'selected' : '';
}
?>

<div class="centro">
	<div class="titulo">Consultar Documentos</div>
	<div class="filtro">
		<form name="form_filtro" id="form_filtro" method="post"
			action="consultar_documento.php?pagina=consultar_documento">
			<select name="fil_doc_tipo" id="fil_doc_tipo">
				<option value=""><?php echo htmlspecialchars($nomeTipoDocumento); ?></option>
				<?php
				$stmtTipos = $pdo->query("SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC");
				while ($tipo = $stmtTipos->fetch(PDO::FETCH_ASSOC)) {
					echo "<option value='" . htmlspecialchars($tipo['tpd_id']) . "' " . selected($tipoDocumentoFiltro, $tipo['tpd_id']) . ">" . htmlspecialchars($tipo['tpd_nome']) . "</option>";
				}
				?>
				<option value="" <?php echo $tipoDocumentoFiltro === '' ? 'selected' : ''; ?>>Todos</option>
			</select>
			<input type="text" name="fil_data_inicio" id="fil_data_inicio" placeholder="Data Início"
				value="<?php echo htmlspecialchars($dataInicioFiltro); ?>">
			<input type="text" name="fil_data_fim" id="fil_data_fim" placeholder="Data Fim"
				value="<?php echo htmlspecialchars($dataFimFiltro); ?>">
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
				<tr class="<?php echo $classeLinha; ?>">
					<td><?php echo htmlspecialchars($doc['tpd_nome']); ?></td>
					<td><?php echo htmlspecialchars($doc['orc_id']) . " (" . htmlspecialchars($doc['tps_nome']) . ")"; ?></td>
					<td align="center"><?php echo date("d/m/Y", strtotime($doc['doc_data_emissao'])); ?></td>
					<td align="center"><?php echo htmlspecialchars($doc['doc_periodicidade']); ?></td>
					<td align="center"><?php echo date("d/m/Y", strtotime($doc['doc_data_vencimento'])); ?></td>
					<td align="center"><?php echo date("d/m/Y H:i", strtotime($doc['doc_data_cadastro'])); ?></td>
					<td align="center">
						<?php if (!empty($doc['doc_anexo'])): ?>
							<a href="<?php echo htmlspecialchars($doc['doc_anexo']); ?>" target="_blank">
								<img src="../imagens/icon-pdf.png" alt="Anexo PDF">
							</a>
						<?php endif; ?>
					</td>
				</tr>
				<?php
				$classeLinha = $classeLinha === 'linhaimpar' ? 'linhapar' : 'linhaimpar';
			endforeach;
			?>
		</table>
		<!-- Paginação -->
		<div class="paginacao" style="text-align:center; margin-top:20px;">
			<?php if ($totalPaginas > 1): ?>
				<?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
					<?php if ($i == $paginaAtual): ?>
						<strong><?php echo $i; ?></strong>
					<?php else: ?>
						<a
							href="?pag=<?php echo $i; ?>&fil_doc_tipo=<?php echo urlencode($tipoDocumentoFiltro); ?>&fil_data_inicio=<?php echo urlencode($dataInicioFiltro); ?>&fil_data_fim=<?php echo urlencode($dataFimFiltro); ?>"><?php echo $i; ?></a>
					<?php endif; ?>
				<?php endfor; ?>
			<?php endif; ?>
		</div>
	<?php else: ?>
		<br><br><br>Não há nenhum documento cadastrado.
	<?php endif; ?>
</div>

<?php include('../mod_rodape/rodape.php'); ?>