<?php
session_start();
$pagina_link = 'relatorio_malotes';
include '../mod_includes/php/connect.php';

// Função para montar filtros SQL
function montarFiltros(&$parametros)
{
	$filtros = [];

	// Filtro por malote
	$malote = $_REQUEST['fil_malote'] ?? '';
	if ($malote !== '') {
		$filtros[] = "mal_id = :malote";
		$parametros[':malote'] = $malote;
	} else {
		$filtros[] = "1=1";
	}

	// Filtro por lacre
	$lacre = $_REQUEST['fil_lacre'] ?? '';
	if ($lacre !== '') {
		$filtros[] = "mal_lacre LIKE :lacre";
		$parametros[':lacre'] = "%$lacre%";
	} else {
		$filtros[] = "1=1";
	}

	// Filtro por nome do cliente
	$nome = $_REQUEST['fil_nome'] ?? '';
	if ($nome !== '') {
		$filtros[] = "cli_nome_razao LIKE :nome";
		$parametros[':nome'] = "%$nome%";
	} else {
		$filtros[] = "1=1";
	}

	// Filtro por baixado
	$baixado = $_REQUEST['fil_baixado'] ?? '';
	if ($baixado === '1') {
		$filtros[] = "mai_baixado = 1";
	} elseif ($baixado === '0') {
		$filtros[] = "mai_baixado IS NULL";
	} else {
		$filtros[] = "1=1";
	}

	// Filtro por data
	$data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
	$data_fim = $_REQUEST['fil_data_fim'] ?? '';
	if ($data_inicio !== '' && $data_fim !== '') {
		$data_inicio_sql = implode('-', array_reverse(explode('/', $data_inicio)));
		$data_fim_sql = implode('-', array_reverse(explode('/', $data_fim))) . " 23:59:59";
		$filtros[] = "mai_data_vencimento BETWEEN :data_inicio AND :data_fim";
		$parametros[':data_inicio'] = $data_inicio_sql;
		$parametros[':data_fim'] = $data_fim_sql;
	} elseif ($data_inicio !== '') {
		$data_inicio_sql = implode('-', array_reverse(explode('/', $data_inicio)));
		$filtros[] = "mai_data_vencimento >= :data_inicio";
		$parametros[':data_inicio'] = $data_inicio_sql;
	} elseif ($data_fim !== '') {
		$data_fim_sql = implode('-', array_reverse(explode('/', $data_fim))) . " 23:59:59";
		$filtros[] = "mai_data_vencimento <= :data_fim";
		$parametros[':data_fim'] = $data_fim_sql;
	} else {
		$filtros[] = "1=1";
	}

	// Filtro de ativação do filtro
	$filtro = $_REQUEST['filtro'] ?? '';
	$filtros[] = $filtro === '' ? "1=0" : "1=1";

	return implode(' AND ', $filtros);
}

// Função para paginação
function paginacao($total_registros, $pagina_atual, $limite)
{
	$total_paginas = ceil($total_registros / $limite);
	$html = "<div class='paginacao'>";
	for ($i = 1; $i <= $total_paginas; $i++) {
		if ($i == $pagina_atual) {
			$html .= "<span class='pagina-atual'>$i</span> ";
		} else {
			$query = http_build_query(array_merge($_GET, ['pagina_atual' => $i]));
			$html .= "<a href='?{$query}'>$i</a> ";
		}
	}
	$html .= "</div>";
	return $html;
}

// Parâmetros e filtros
$parametros = [];
$filtros_sql = montarFiltros($parametros);

// Paginação
$limite = 20;
$pagina_atual = isset($_GET['pagina_atual']) ? max(1, intval($_GET['pagina_atual'])) : 1;
$offset = ($pagina_atual - 1) * $limite;

// Consulta para contar total de registros
$sql_total = "
	SELECT COUNT(DISTINCT malote_gerenciar.mal_id) AS total
	FROM malote_gerenciar
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
	LEFT JOIN malote_itens ON malote_itens.mai_malote = malote_gerenciar.mal_id
	WHERE $filtros_sql
";
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($parametros);
$total_registros = $stmt_total->fetchColumn();

// Consulta principal com paginação
$sql = "
	SELECT DISTINCT malote_gerenciar.*
	FROM malote_gerenciar
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
	LEFT JOIN malote_itens ON malote_itens.mai_malote = malote_gerenciar.mal_id
	WHERE $filtros_sql
	ORDER BY mal_data_cadastro DESC
	LIMIT :limite OFFSET :offset
";
$stmt = $pdo->prepare($sql);
foreach ($parametros as $chave => $valor) {
	$stmt->bindValue($chave, $valor);
}
$stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$malotes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<title>Relatório de Malotes</title>
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include '../css/style.php'; ?>
	<script src="../mod_includes/js/funcoes.js"></script>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
	<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
	<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
	<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
	<script>
		jQuery(document).ready(function () {
			jQuery(".toggle_container").show();
			jQuery(".toggle_container_info").hide();
			jQuery("h2.trigger").click(function () {
				jQuery(this).toggleClass("active").next().slideToggle("slow");
				return false;
			});
		});
	</script>
</head>

<body>
	<?php
	include '../mod_includes/php/funcoes-jquery.php';
	require_once '../mod_includes/php/verificalogin.php';
	include '../mod_topo/topo.php';
	require_once '../mod_includes/php/verificapermissao.php';

	$page = "Relatórios &raquo; <a href='relatorio_malotes.php?pagina=relatorio_malotes{$autenticacao}'>Malotes</a>";
	?>
	<div class='container'>
		<div class='centro'>
			<div class='titulo'><?= $page ?></div>
			<div class='filtro'>
				<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post'
					action='relatorio_malotes.php?pagina=relatorio_malotes<?= $autenticacao ?>&filtro=1'>
					<input name='fil_malote' id='fil_malote'
						value='<?= htmlspecialchars($_REQUEST['fil_malote'] ?? '') ?>' placeholder='N° malote'>
					<input name='fil_lacre' id='fil_lacre' value='<?= htmlspecialchars($_REQUEST['fil_lacre'] ?? '') ?>'
						placeholder='N° lacre'>
					<input name='fil_nome' id='fil_nome' value='<?= htmlspecialchars($_REQUEST['fil_nome'] ?? '') ?>'
						placeholder='Cliente'>
					<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início'
						value='<?= htmlspecialchars($_REQUEST['fil_data_inicio'] ?? '') ?>'
						onkeypress='return mascaraData(this,event);'>
					<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim'
						value='<?= htmlspecialchars($_REQUEST['fil_data_fim'] ?? '') ?>'
						onkeypress='return mascaraData(this,event);'>
					<select name='fil_baixado' id='fil_baixado'>
						<?php
						$opcoes_baixado = [
							'' => 'Todos',
							'1' => 'Sim',
							'0' => 'Não'
						];
						$fil_baixado = $_REQUEST['fil_baixado'] ?? '';
						foreach ($opcoes_baixado as $valor => $texto) {
							$selected = ($fil_baixado === $valor) ? 'selected' : '';
							echo "<option value='$valor' $selected>$texto</option>";
						}
						?>
					</select>
					<input type='submit' value='Filtrar'>
					<input type='button' onclick="PrintDiv('imprimir');" value='Imprimir' />
				</form>
			</div>
			<div class='contentPrint' id='imprimir'>
				<?php if ($total_registros > 0): ?>
					<img src='<?= $logo ?>' border='0' class='logo' />
					<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
						<tr>
							<td class='titulo_tabela'>N° Malote</td>
							<td class='titulo_tabela'>N° Lacre</td>
							<td class='titulo_tabela'>Cliente</td>
							<td class='titulo_tabela'>Observação</td>
							<td class='titulo_tabela' align='center'>Data Cadastro</td>
						</tr>
						<?php
						$linha = 0;
						foreach ($malotes as $malote):
							$classe_linha = $linha % 2 == 0 ? "linhaimpar" : "linhapar";
							$linha++;
							$mal_id = $malote['mal_id'];
							$mal_lacre = $malote['mal_lacre'];
							$cli_nome_razao = $malote['mal_cliente'] ?? '';
							$mal_observacoes = $malote['mal_observacoes'];
							$mal_data_cadastro = date('d/m/Y', strtotime($malote['mal_data_cadastro']));
							$mal_hora_cadastro = date('H:i', strtotime($malote['mal_data_cadastro']));
							?>
							<tr class='<?= $classe_linha ?>'>
								<td style='border-top:1px solid #DADADA'><?= $mal_id ?></td>
								<td style='border-top:1px solid #DADADA'><?= htmlspecialchars($mal_lacre) ?></td>
								<td style='border-top:1px solid #DADADA'><?= htmlspecialchars($cli_nome_razao) ?></td>
								<td style='border-top:1px solid #DADADA'><?= htmlspecialchars($mal_observacoes) ?></td>
								<td style='border-top:1px solid #DADADA' align='center'><?= $mal_data_cadastro ?><br><span
										class='detalhe'><?= $mal_hora_cadastro ?></span></td>
							</tr>
							<?php
							// Itens do malote
							$sql_itens = "SELECT * FROM malote_itens WHERE mai_malote = :mal_id";
							$stmt_itens = $pdo->prepare($sql_itens);
							$stmt_itens->execute([':mal_id' => $mal_id]);
							$itens = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);
							if ($itens):
								?>
								<tr class='<?= $classe_linha ?>'>
									<td colspan='5'>
										<h2 class='trigger'><a href='#'> &nbsp;&nbsp;&nbsp;&nbsp; Documentos do malote:</a></h2>
										<div class='toggle_container'>
											<div class='block'>
												<table align='center' width='100%' border='0' cellspacing='0' cellpadding='3'
													class='bordatabela2'>
													<tr>
														<td class='titulo_tabela2'>Fornecedor</td>
														<td class='titulo_tabela2'>Tipo Documento</td>
														<td class='titulo_tabela2'>N° Cheque</td>
														<td class='titulo_tabela2'>Valor</td>
														<td class='titulo_tabela2' align='center'>Data Vencimento</td>
														<td class='titulo_tabela2' align='center'>Baixado?</td>
														<td class='titulo_tabela2' align='center'>Data Baixa</td>
													</tr>
													<?php foreach ($itens as $item): ?>
														<tr>
															<td><?= htmlspecialchars($item['mai_fornecedor']) ?></td>
															<td><?= htmlspecialchars($item['mai_tipo_documento']) ?></td>
															<td><?= htmlspecialchars($item['mai_num_cheque']) ?></td>
															<td>R$ <?= number_format($item['mai_valor'], 2, ',', '.') ?></td>
															<td align='center'>
																<?= date('d/m/Y', strtotime($item['mai_data_vencimento'])) ?></td>
															<td align='center'>
																<?= $item['mai_baixado'] == 1 ? "<span class='verde'>Sim</span>" : "<span class='vermelho'>Não</span>" ?>
															</td>
															<td align='center'>
																<?php
																if ($item['mai_data_baixa']) {
																	echo date('d/m/Y', strtotime($item['mai_data_baixa'])) . "<br><span class='detalhe'>" . date('H:i', strtotime($item['mai_data_baixa'])) . "</span>";
																}
																?>
															</td>
														</tr>
													<?php endforeach; ?>
												</table>
											</div>
										</div>
									</td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					</table>
					<?= paginacao($total_registros, $pagina_atual, $limite) ?>
				<?php else: ?>
					<br><br><br>Selecione acima os filtros que deseja para gerar o relatório.
				<?php endif; ?>
				<div class='titulo'></div>
			</div>
		</div>
	</div>
	<?php include '../mod_rodape/rodape.php'; ?>
	<script src="../mod_includes/js/jquery-1.3.2.min.js"></script>
	<script src="../mod_includes/js/elementPrint.js"></script>
</body>

</html>