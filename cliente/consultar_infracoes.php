<?php
session_start();
require_once '../mod_includes/php/connect.php';

// Configurações de paginação
$registrosPorPagina = 10;
$paginaAtual = isset($_REQUEST['pag']) ? (int) $_REQUEST['pag'] : 1;
$primeiroRegistro = ($paginaAtual - 1) * $registrosPorPagina;

// Filtros
$filtros = [
	'inf_bloco' => $_REQUEST['fil_bloco'] ?? '',
	'inf_assunto' => $_REQUEST['fil_assunto'] ?? '',
	'inf_apto' => $_REQUEST['fil_apto'] ?? '',
	'inf_proprietario' => $_REQUEST['fil_proprietario'] ?? '',
	'inf_tipo' => $_REQUEST['fil_inf_tipo'] ?? ''
];

// Montagem dinâmica das condições
$condicoes = [];
$params = [':cliente_id' => $_SESSION['cliente_id']];
foreach ($filtros as $campo => $valor) {
	if ($valor !== '' && $campo !== 'inf_tipo') {
		$condicoes[] = "$campo LIKE :$campo";
		$params[":$campo"] = "%$valor%";
	} elseif ($campo === 'inf_tipo' && $valor !== '') {
		$condicoes[] = "$campo = :$campo";
		$params[":$campo"] = $valor;
	}
}
$whereSQL = $condicoes ? implode(' AND ', $condicoes) : '1=1';

// Consulta principal com paginação
$sql = "
	SELECT * FROM infracoes_gerenciar
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
	LEFT JOIN recurso_gerenciar ON recurso_gerenciar.rec_infracao = infracoes_gerenciar.inf_id
	WHERE cli_id = :cliente_id AND $whereSQL
	ORDER BY inf_data DESC
	LIMIT :primeiroRegistro, :registrosPorPagina
";
$stmt = $pdo->prepare($sql);
foreach ($params as $chave => $valor) {
	$stmt->bindValue($chave, $valor, PDO::PARAM_STR);
}
$stmt->bindValue(':primeiroRegistro', $primeiroRegistro, PDO::PARAM_INT);
$stmt->bindValue(':registrosPorPagina', $registrosPorPagina, PDO::PARAM_INT);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para total de registros (para paginação)
$sqlTotal = "
	SELECT COUNT(*) FROM infracoes_gerenciar
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
	WHERE cli_id = :cliente_id AND $whereSQL
";
$stmtTotal = $pdo->prepare($sqlTotal);
foreach ($params as $chave => $valor) {
	$stmtTotal->bindValue($chave, $valor, PDO::PARAM_STR);
}
$stmtTotal->execute();
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);
?>

<div class="centro">
	<div class="titulo">Consultar Infrações</div>
	<div class="filtro">
		<form method="post" action="consultar_infracoes.php">
			<input name="fil_bloco" placeholder="Bloco/Quadra" value="<?= htmlspecialchars($filtros['inf_bloco']) ?>">
			<input name="fil_apto" placeholder="Unidade" value="<?= htmlspecialchars($filtros['inf_apto']) ?>">
			<input name="fil_proprietario" placeholder="Proprietário"
				value="<?= htmlspecialchars($filtros['inf_proprietario']) ?>">
			<input name="fil_assunto" placeholder="Assunto" value="<?= htmlspecialchars($filtros['inf_assunto']) ?>">
			<select name="fil_inf_tipo">
				<option value="">Tipo de Infração</option>
				<option value="Notificação de advertência por infração disciplinar"
					<?= $filtros['inf_tipo'] == 'Notificação de advertência por infração disciplinar' ? 'selected' : '' ?>>
					Advertência</option>
				<option value="Multa por Infração Interna" <?= $filtros['inf_tipo'] == 'Multa por Infração Interna' ? 'selected' : '' ?>>Multa</option>
				<option value="Notificação de ressarcimento" <?= $filtros['inf_tipo'] == 'Notificação de ressarcimento' ? 'selected' : '' ?>>Ressarcimento
				</option>
				<option value="Comunicação interna" <?= $filtros['inf_tipo'] == 'Comunicação interna' ? 'selected' : '' ?>>
					Comunicação</option>
				<option value="" <?= $filtros['inf_tipo'] == '' ? 'selected' : '' ?>>Todos</option>
			</select>
			<input type="submit" value="Filtrar">
		</form>
	</div>

	<?php if ($resultados): ?>
		<table class="bordatabela">
			<tr>
				<td>N.</td>
				<td>Tipo</td>
				<td>Assunto</td>
				<td>Proprietário</td>
				<td>Bloco/Quadra/Ap</td>
				<td align="center">Data</td>
				<td align="center">Advertência/Multa</td>
				<td align="center">Comprovante</td>
				<td align="center">Recurso</td>
			</tr>
			<?php $par = false; ?>
			<?php foreach ($resultados as $row): ?>
				<tr class="<?= $par ? 'linhapar' : 'linhaimpar' ?>">
					<td><?= str_pad($row['inf_id'], 3, "0", STR_PAD_LEFT) . "/" . htmlspecialchars($row['inf_ano']) ?></td>
					<td><?= htmlspecialchars($row['inf_tipo']) ?></td>
					<td><?= htmlspecialchars($row['inf_assunto']) ?></td>
					<td><?= htmlspecialchars($row['inf_proprietario']) ?></td>
					<td><?= htmlspecialchars($row['inf_bloco']) . "/" . htmlspecialchars($row['inf_apto']) ?></td>
					<td align="center"><?= date("d/m/Y", strtotime($row['inf_data'])) ?></td>
					<td align="center">
						<a href="infracoes_imprimir.php?inf_id=<?= $row['inf_id'] ?>">
							<img src="../imagens/icon-pdf.png" alt="PDF">
						</a>
					</td>
					<td align="center">
						<?php if (!empty($row['inf_comprovante'])): ?>
							<a href="<?= htmlspecialchars($row['inf_comprovante']) ?>" target="_blank">
								<img src="../imagens/icon-pdf.png" alt="Comprovante">
							</a>
						<?php endif; ?>
					</td>
					<td align="right">
						<?php if (!empty($row['rec_id'])): ?>
							<?= htmlspecialchars($row['rec_status']) ?>
							<a href="consultar_recurso.php?rec_id=<?= $row['rec_id'] ?>">
								<img src="../imagens/icon-exibir.png" alt="Exibir">
							</a>
						<?php endif; ?>
					</td>
				</tr>
				<?php $par = !$par; ?>
			<?php endforeach; ?>
		</table>

		<!-- Paginação -->
		<div class="paginacao">
			<?php if ($totalPaginas > 1): ?>
				<?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
					<?php if ($i == $paginaAtual): ?>
						<strong><?= $i ?></strong>
					<?php else: ?>
						<a href="?pag=<?= $i ?><?= http_build_query(array_filter([
						  	'fil_bloco' => $filtros['inf_bloco'],
						  	'fil_apto' => $filtros['inf_apto'],
						  	'fil_proprietario' => $filtros['inf_proprietario'],
						  	'fil_assunto' => $filtros['inf_assunto'],
						  	'fil_inf_tipo' => $filtros['inf_tipo']
						  ]), '', '&', PHP_QUERY_RFC3986) ?>">
							<?= $i ?>
						</a>
					<?php endif; ?>
				<?php endfor; ?>
			<?php endif; ?>
		</div>
	<?php else: ?>
		<br><br><br>Não há nenhuma infração cadastrada.
	<?php endif; ?>
</div>

<?php include '../mod_rodape/rodape.php'; ?>