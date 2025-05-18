<?php
session_start();
include('../mod_includes/php/connect.php');

$num_por_pagina = 10;
$pag = $_REQUEST['pag'] ?? 1;
$primeiro_registro = ($pag - 1) * $num_por_pagina;

$fil_doc_tipo = $_REQUEST['fil_doc_tipo'] ?? '';
$fil_data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
$fil_data_fim = $_REQUEST['fil_data_fim'] ?? '';

// Montagem da query de tipo de documento
if ($fil_doc_tipo == '') {
	$tipo_doc_query = "1 = 1";
	$fil_doc_tipo_n = "Tipo de documento";
} else {
	$tipo_doc_query = "doc_tipo = :fil_doc_tipo";
	$stmt_tipo_doc = $pdo->prepare("SELECT tpd_nome FROM cadastro_tipos_docs WHERE tpd_id = :fil_doc_tipo");
	$stmt_tipo_doc->bindParam(':fil_doc_tipo', $fil_doc_tipo, PDO::PARAM_INT);
	$stmt_tipo_doc->execute();
	$fil_doc_tipo_n = $stmt_tipo_doc->fetchColumn() ?? "Tipo de documento";
}

// Montagem da query de data
$data_query = "1 = 1";
if ($fil_data_inicio && !$fil_data_fim) {
	$data_query = "doc_data_cadastro >= :fil_data_inicio";
} elseif (!$fil_data_inicio && $fil_data_fim) {
	$data_query = "doc_data_cadastro <= :fil_data_fim";
} elseif ($fil_data_inicio && $fil_data_fim) {
	$data_query = "doc_data_cadastro BETWEEN :fil_data_inicio AND :fil_data_fim";
}

// Query de listagem de documentos
$sql = "SELECT * FROM documento_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
        LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
        LEFT JOIN (orcamento_gerenciar 
            LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
        ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
        WHERE cli_id = :cliente_id AND $tipo_doc_query AND $data_query
        ORDER BY doc_data_cadastro DESC
        LIMIT :primeiro_registro, :num_por_pagina";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
if ($fil_doc_tipo)
	$stmt->bindParam(':fil_doc_tipo', $fil_doc_tipo, PDO::PARAM_INT);
if ($fil_data_inicio)
	$stmt->bindParam(':fil_data_inicio', $fil_data_inicio, PDO::PARAM_STR);
if ($fil_data_fim)
	$stmt->bindParam(':fil_data_fim', $fil_data_fim, PDO::PARAM_STR);
$stmt->bindParam(':primeiro_registro', $primeiro_registro, PDO::PARAM_INT);
$stmt->bindParam(':num_por_pagina', $num_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class='centro'>
	<div class='titulo'>Consultar Documentos</div>
	<div class='filtro'>
		<form name='form_filtro' id='form_filtro' method='post'
			action='consultar_documento.php?pagina=consultar_documento'>
			<select name='fil_doc_tipo' id='fil_doc_tipo'>
				<option value='<?php echo htmlspecialchars($fil_doc_tipo); ?>'>
					<?php echo htmlspecialchars($fil_doc_tipo_n); ?></option>
				<?php
				$stmt_tpd = $pdo->query("SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC");
				while ($row_tpd = $stmt_tpd->fetch(PDO::FETCH_ASSOC)) {
					echo "<option value='" . htmlspecialchars($row_tpd['tpd_id']) . "'>" . htmlspecialchars($row_tpd['tpd_nome']) . "</option>";
				}
				?>
				<option value=''>Todos</option>
			</select>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início'
				value='<?php echo htmlspecialchars($fil_data_inicio); ?>'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim'
				value='<?php echo htmlspecialchars($fil_data_fim); ?>'>
			<input type='submit' value='Filtrar'>
		</form>
	</div>
	<?php if ($resultados): ?>
		<table class='bordatabela' width='100%' border='0' cellspacing='0' cellpadding='10'>
			<tr>
				<td class='titulo_tabela'>Tipo de Documento</td>
				<td class='titulo_tabela'>Orçamento</td>
				<td class='titulo_tabela' align='center'>Data Emissão</td>
				<td class='titulo_tabela' align='center'>Periodicidade</td>
				<td class='titulo_tabela' align='center'>Data Vencimento</td>
				<td class='titulo_tabela' align='center'>Data Cadastro</td>
				<td class='titulo_tabela' align='center'>Anexo</td>
			</tr>
			<?php foreach ($resultados as $row): ?>
				<tr class='<?php echo $c = ($c ?? 0) ? "linhapar" : "linhaimpar";
				$c = !$c; ?>'>
					<td><?php echo htmlspecialchars($row['tpd_nome']); ?></td>
					<td><?php echo htmlspecialchars($row['orc_id']) . " (" . htmlspecialchars($row['tps_nome']) . ")"; ?></td>
					<td align='center'><?php echo date("d/m/Y", strtotime($row['doc_data_emissao'])); ?></td>
					<td align='center'><?php echo htmlspecialchars($row['doc_periodicidade']); ?></td>
					<td align='center'><?php echo date("d/m/Y", strtotime($row['doc_data_vencimento'])); ?></td>
					<td align='center'><?php echo date("d/m/Y H:i", strtotime($row['doc_data_cadastro'])); ?></td>
					<td align='center'>
						<?php if (!empty($row['doc_anexo'])): ?>
							<a href="<?php echo htmlspecialchars($row['doc_anexo']); ?>" target='_blank'>
								<img src='../imagens/icon-pdf.png' valign='middle'>
							</a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php else: ?>
		<br><br><br>Não há nenhum documento cadastrado.
	<?php endif; ?>
</div>

<?php include('../mod_rodape/rodape.php'); ?>