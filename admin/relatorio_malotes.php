<?php
session_start();
$pagina_link = 'relatorio_malotes';
include '../mod_includes/php/connect.php';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php echo $titulo ?? ''; ?></title>
	<meta name="author" content="MogiComp">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include '../css/style.php'; ?>
	<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
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

	$fil_malote = $_REQUEST['fil_malote'] ?? '';
	$malote_query = $fil_malote === '' ? "1=1" : "mal_id = :fil_malote";

	$fil_lacre = $_REQUEST['fil_lacre'] ?? '';
	$lacre_query = $fil_lacre === '' ? "1=1" : "mal_lacre LIKE :fil_lacre";

	$fil_nome = $_REQUEST['fil_nome'] ?? '';
	$nome_query = $fil_nome === '' ? "1=1" : "cli_nome_razao LIKE :fil_nome";

	$fil_data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
	$fil_data_fim = $_REQUEST['fil_data_fim'] ?? '';
	$data_query = "1=1";
	$params_data = [];

	if ($fil_data_inicio !== '') {
		$fil_data_inicio_sql = implode('-', array_reverse(explode('/', $fil_data_inicio)));
		$params_data[':fil_data_inicio'] = $fil_data_inicio_sql;
	}
	if ($fil_data_fim !== '') {
		$fil_data_fim_sql = implode('-', array_reverse(explode('/', $fil_data_fim)));
		$params_data[':fil_data_fim'] = $fil_data_fim_sql;
	}

	if ($fil_data_inicio !== '' && $fil_data_fim === '') {
		$data_query = "mai_data_vencimento >= :fil_data_inicio";
	} elseif ($fil_data_inicio === '' && $fil_data_fim !== '') {
		$data_query = "mai_data_vencimento <= :fil_data_fim_end";
		$params_data[':fil_data_fim_end'] = $fil_data_fim_sql . " 23:59:59";
	} elseif ($fil_data_inicio !== '' && $fil_data_fim !== '') {
		$data_query = "mai_data_vencimento BETWEEN :fil_data_inicio AND :fil_data_fim_end";
		$params_data[':fil_data_fim_end'] = $fil_data_fim_sql . " 23:59:59";
	}

	$fil_baixado = $_REQUEST['fil_baixado'] ?? '';
	$baixado_query = "1=1";
	$fil_baixado_n = "Baixado?";
	if ($fil_baixado !== '') {
		if ($fil_baixado === '1') {
			$baixado_query = "mai_baixado = 1";
			$fil_baixado_n = "Sim";
		} elseif ($fil_baixado === '0') {
			$baixado_query = "mai_baixado IS NULL";
			$fil_baixado_n = "Não";
		}
	} else {
		$fil_baixado_n = "Todos";
	}

	$filtro = $_REQUEST['filtro'] ?? '';
	$filtro_query = $filtro === '' ? "1=0" : "1=1";

	$sql = "
SELECT * FROM malote_gerenciar 
LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
WHERE $malote_query AND $lacre_query AND $nome_query AND $filtro_query
  AND mal_id IN (
	SELECT mal_id FROM malote_itens
	LEFT JOIN malote_gerenciar mg2 ON mg2.mal_id = malote_itens.mai_malote
	WHERE $baixado_query AND $data_query
  )
ORDER BY mal_data_cadastro DESC
";

	$params = [];
	if ($fil_malote !== '')
		$params[':fil_malote'] = $fil_malote;
	if ($fil_lacre !== '')
		$params[':fil_lacre'] = "%$fil_lacre%";
	if ($fil_nome !== '')
		$params[':fil_nome'] = "%$fil_nome%";
	$params = array_merge($params, $params_data);

	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	$rows = $stmt->rowCount();

	if (isset($pagina) && $pagina == "relatorio_malotes") {
		echo "
	<div class='container'>
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_malotes.php?pagina=relatorio_malotes{$autenticacao}&filtro=1'>
			<input name='fil_malote' id='fil_malote' value='$fil_malote' placeholder='N° malote'>
			<input name='fil_lacre' id='fil_lacre' value='$fil_lacre' placeholder='N° lacre'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='$fil_data_inicio' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='$fil_data_fim' onkeypress='return mascaraData(this,event);'>
			<select name='fil_baixado' id='fil_baixado'>
				<option value='$fil_baixado'>$fil_baixado_n</option>
				<option value='1'>Sim</option>
				<option value='0'>Não</option>
				<option value=''>Todos</option>
			</select>
			<input type='submit' value='Filtrar'> 
			<input type='button' onclick=\"PrintDiv('imprimir');\" value='Imprimir' />
			</form>
		</div>
		<div class='contentPrint' id='imprimir'>
	";
		if ($rows > 0) {
			echo "
		<img src='$logo' border='0' valign='middle' class='logo' /> 
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>N° Malote</td>
				<td class='titulo_tabela'>N° Lacre</td>
				<td class='titulo_tabela'>Cliente</td>
				<td class='titulo_tabela'>Observação</td>
				<td class='titulo_tabela' align='center'>Data Cadastro</td>
			</tr>";
			$c = 0;
			while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				$mal_id = $row['mal_id'];
				$mal_lacre = $row['mal_lacre'];
				$cli_nome_razao = $row['cli_nome_razao'];
				$mal_observacoes = $row['mal_observacoes'];
				$mal_data_cadastro = date('d/m/Y', strtotime($row['mal_data_cadastro']));
				$mal_hora_cadastro = date('H:i', strtotime($row['mal_data_cadastro']));
				$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
				$c++;

				echo "<tr class='$c1'>
				<td style='border-top:1px solid #DADADA'>$mal_id</td>
				<td style='border-top:1px solid #DADADA'>$mal_lacre</td>
				<td style='border-top:1px solid #DADADA'>$cli_nome_razao</td>
				<td style='border-top:1px solid #DADADA'>$mal_observacoes</td>
				<td style='border-top:1px solid #DADADA' align='center'>$mal_data_cadastro<br><span class='detalhe'>$mal_hora_cadastro</span></td>
			</tr>";

				// Itens do malote
				$sql_itens = "SELECT * FROM malote_itens WHERE mai_malote = :mal_id AND $baixado_query AND $data_query";
				$stmt_itens = $pdo->prepare($sql_itens);
				$params_itens = [':mal_id' => $mal_id] + $params_data;
				$stmt_itens->execute($params_itens);
				$rows_itens = $stmt_itens->rowCount();

				if ($rows_itens > 0) {
					$y = 0;
					while ($item = $stmt_itens->fetch(PDO::FETCH_ASSOC)) {
						$mai_fornecedor = $item['mai_fornecedor'];
						$mai_tipo_documento = $item['mai_tipo_documento'];
						$mai_num_cheque = $item['mai_num_cheque'];
						$mai_valor = number_format($item['mai_valor'], 2, ',', '.');
						$mai_data_vencimento = date('d/m/Y', strtotime($item['mai_data_vencimento']));
						$mai_baixado = $item['mai_baixado'];
						$mai_data_baixa = $item['mai_data_baixa'] ? date('d/m/Y', strtotime($item['mai_data_baixa'])) : '';
						$mai_hora_baixa = $item['mai_data_baixa'] ? date('H:i', strtotime($item['mai_data_baixa'])) : '';
						$mai_baixado_n = $mai_baixado == 1 ? "<span class='verde'>Sim</span>" : "<span class='vermelho'>Não</span>";

						if ($y == 0) {
							echo "
						<tr class='$c1'>
						<td colspan='5'>
						<h2 class='trigger'><a href='#'> &nbsp;&nbsp;&nbsp;&nbsp; Documentos do malote:</a></h2>
						<div class='toggle_container'>
						<div class='block'>
						<table align='center' width='100%' border='0' cellspacing='0' cellpadding='3' class='bordatabela2'>
						<tr>
							<td class='titulo_tabela2'>Fornecedor</td>
							<td class='titulo_tabela2'>Tipo Documento</td>
							<td class='titulo_tabela2'>N° Cheque</td>
							<td class='titulo_tabela2'>Valor</td>
							<td class='titulo_tabela2' align='center'>Data Vencimento</td>
							<td class='titulo_tabela2' align='center'>Baixado?</td>
							<td class='titulo_tabela2' align='center'>Data Baixa</td>
						</tr>
						";
						}
						echo "
					<tr>
						<td>$mai_fornecedor</td>
						<td>$mai_tipo_documento</td>
						<td>$mai_num_cheque</td>
						<td>R$ $mai_valor</td>
						<td align='center'>$mai_data_vencimento</td>
						<td align='center'>$mai_baixado_n</td>
						<td align='center'>$mai_data_baixa<br><span class='detalhe'>$mai_hora_baixa</span></td>
					</tr>
					";
						$y++;
					}
					echo "</table></div></div></td></tr>";
				}
			}
			echo "</table>";
		} else {
			echo "<br><br><br>Selecione acima os filtros que deseja para gerar o relatório.";
		}
		echo "
		<div class='titulo'>  </div>				
		</div>
	</div>
	</div>";
	}

	include '../mod_rodape/rodape.php';
	?>
	<script src="../mod_includes/js/jquery-1.3.2.min.js"></script>
	<script src="../mod_includes/js/elementPrint.js"></script>
</body>

</html>