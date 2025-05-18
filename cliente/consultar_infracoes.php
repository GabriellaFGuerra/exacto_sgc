<?php
session_start();
include('../mod_includes/php/connect.php');

$num_por_pagina = 10;
$pag = $_REQUEST['pag'] ?? 1;
$primeiro_registro = ($pag - 1) * $num_por_pagina;

$fil_bloco = $_REQUEST['fil_bloco'] ?? '';
$fil_assunto = $_REQUEST['fil_assunto'] ?? '';
$fil_apto = $_REQUEST['fil_apto'] ?? '';
$fil_proprietario = $_REQUEST['fil_proprietario'] ?? '';
$fil_inf_tipo = $_REQUEST['fil_inf_tipo'] ?? '';

// Construção segura da consulta SQL
$condicoes = [];
if (!empty($fil_bloco))
	$condicoes[] = "inf_bloco LIKE :fil_bloco";
if (!empty($fil_assunto))
	$condicoes[] = "inf_assunto LIKE :fil_assunto";
if (!empty($fil_apto))
	$condicoes[] = "inf_apto LIKE :fil_apto";
if (!empty($fil_proprietario))
	$condicoes[] = "inf_proprietario LIKE :fil_proprietario";
if (!empty($fil_inf_tipo))
	$condicoes[] = "inf_tipo = :fil_inf_tipo";

$condicaoSQL = $condicoes ? implode(" AND ", $condicoes) : "1=1";

$sql = "SELECT * FROM infracoes_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
        LEFT JOIN recurso_gerenciar ON recurso_gerenciar.rec_infracao = infracoes_gerenciar.inf_id
        WHERE cli_id = :cliente_id AND $condicaoSQL
        ORDER BY inf_data DESC
        LIMIT :primeiro_registro, :num_por_pagina";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
$stmt->bindParam(':primeiro_registro', $primeiro_registro, PDO::PARAM_INT);
$stmt->bindParam(':num_por_pagina', $num_por_pagina, PDO::PARAM_INT);

foreach ($condicoes as $condicao) {
	if (strpos($condicao, "LIKE") !== false) {
		$valor = "%" . ${explode(" ", $condicao)[2]} . "%";
	} else {
		$valor = ${explode(" ", $condicao)[2]};
	}
	$stmt->bindParam(explode(" ", $condicao)[2], $valor, PDO::PARAM_STR);
}

$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class='centro'>
	<div class='titulo'>Consultar Infrações</div>
	<div class='filtro'>
		<form method='post' action='consultar_infracoes.php'>
			<input name='fil_bloco' placeholder='Bloco/Quadra' value='<?php echo htmlspecialchars($fil_bloco); ?>'>
			<input name='fil_apto' placeholder='Unidade' value='<?php echo htmlspecialchars($fil_apto); ?>'>
			<input name='fil_proprietario' placeholder='Proprietário'
				value='<?php echo htmlspecialchars($fil_proprietario); ?>'>
			<input name='fil_assunto' placeholder='Assunto' value='<?php echo htmlspecialchars($fil_assunto); ?>'>
			<select name='fil_inf_tipo'>
				<option value='<?php echo htmlspecialchars($fil_inf_tipo); ?>'>Tipo de Infração</option>
				<option value='Notificação de advertência por infração disciplinar'>Advertência</option>
				<option value='Multa por Infração Interna'>Multa</option>
				<option value='Notificação de ressarcimento'>Ressarcimento</option>
				<option value='Comunicação interna'>Comunicação</option>
				<option value=''>Todos</option>
			</select>
			<input type='submit' value='Filtrar'>
		</form>
	</div>

	<?php if ($resultados): ?>
		<table class='bordatabela'>
			<tr>
				<td>N.</td>
				<td>Tipo</td>
				<td>Assunto</td>
				<td>Proprietário</td>
				<td>Bloco/Quadra/Ap</td>
				<td align='center'>Data</td>
				<td align='center'>Advertência/Multa</td>
				<td align='center'>Comprovante</td>
				<td align='center'>Recurso</td>
			</tr>
			<?php foreach ($resultados as $row): ?>
				<tr class='<?php echo $c = ($c ?? 0) ? "linhapar" : "linhaimpar";
				$c = !$c; ?>'>
					<td><?php echo str_pad($row['inf_id'], 3, "0", STR_PAD_LEFT) . "/" . htmlspecialchars($row['inf_ano']); ?>
					</td>
					<td><?php echo htmlspecialchars($row['inf_tipo']); ?></td>
					<td><?php echo htmlspecialchars($row['inf_assunto']); ?></td>
					<td><?php echo htmlspecialchars($row['inf_proprietario']); ?></td>
					<td><?php echo htmlspecialchars($row['inf_bloco']) . "/" . htmlspecialchars($row['inf_apto']); ?></td>
					<td align='center'><?php echo date("d/m/Y", strtotime($row['inf_data'])); ?></td>
					<td align='center'><a href='infracoes_imprimir.php?inf_id=<?php echo $row['inf_id']; ?>'><img
								src='../imagens/icon-pdf.png'></a></td>
					<td align='center'>
						<?php if (!empty($row['inf_comprovante'])): ?>
							<a href='<?php echo htmlspecialchars($row['inf_comprovante']); ?>' target='_blank'><img
									src='../imagens/icon-pdf.png'></a>
						<?php endif; ?>
					</td>
					<td align='right'>
						<?php if (!empty($row['rec_id'])): ?>
							<?php echo htmlspecialchars($row['rec_status']); ?> <a
								href='consultar_recurso.php?rec_id=<?php echo $row['rec_id']; ?>'><img
									src='../imagens/icon-exibir.png'></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php else: ?>
		<br><br><br>Não há nenhuma infração cadastrada.
	<?php endif; ?>
</div>

<?php include('../mod_rodape/rodape.php'); ?>