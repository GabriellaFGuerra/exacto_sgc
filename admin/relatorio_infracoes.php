<?php
session_start();
$pagina_link = 'relatorio_infracoes';
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
	<script src="../mod_includes/js/jquery-1.8.3.min.js" type="text/javascript"></script>
	<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
	<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
	<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
	<?php
	include '../mod_includes/php/funcoes-jquery.php';
	require_once '../mod_includes/php/verificalogin.php';
	include '../mod_topo/topo.php';
	require_once '../mod_includes/php/verificapermissao.php';

	$page = "Relatórios &raquo; <a href='relatorio_infracoes.php?pagina=relatorio_infracoes" . ($autenticacao ?? '') . "'>Infrações</a>";

	function getRequest($key, $default = '')
	{
		return $_REQUEST[$key] ?? $default;
	}

	$fil_nome = getRequest('fil_nome');
	$fil_proprietario = getRequest('fil_proprietario');
	$fil_assunto = getRequest('fil_assunto');
	$fil_bloco = getRequest('fil_bloco');
	$fil_apto = getRequest('fil_apto');
	$fil_inf_tipo = getRequest('fil_inf_tipo');
	$fil_data_inicio = getRequest('fil_data_inicio');
	$fil_data_fim = getRequest('fil_data_fim');
	$filtro = getRequest('filtro');

	$where = [];
	$params = [];

	if ($fil_nome !== '') {
		$where[] = 'cli_nome_razao LIKE :fil_nome';
		$params[':fil_nome'] = "%$fil_nome%";
	}
	if ($fil_proprietario !== '') {
		$where[] = 'inf_proprietario LIKE :fil_proprietario';
		$params[':fil_proprietario'] = "%$fil_proprietario%";
	}
	if ($fil_assunto !== '') {
		$where[] = 'inf_assunto LIKE :fil_assunto';
		$params[':fil_assunto'] = "%$fil_assunto%";
	}
	if ($fil_bloco !== '') {
		$where[] = 'inf_bloco LIKE :fil_bloco';
		$params[':fil_bloco'] = "%$fil_bloco%";
	}
	if ($fil_apto !== '') {
		$where[] = 'inf_apto LIKE :fil_apto';
		$params[':fil_apto'] = "%$fil_apto%";
	}
	if ($fil_inf_tipo !== '') {
		$where[] = 'inf_tipo = :fil_inf_tipo';
		$params[':fil_inf_tipo'] = $fil_inf_tipo;
		$fil_inf_tipo_n = $fil_inf_tipo;
	} else {
		$fil_inf_tipo_n = 'Tipo de infracoes';
	}

	$data_inicio = '';
	$data_fim = '';
	if ($fil_data_inicio !== '') {
		$data_inicio = implode('-', array_reverse(explode('/', $fil_data_inicio)));
	}
	if ($fil_data_fim !== '') {
		$data_fim = implode('-', array_reverse(explode('/', $fil_data_fim)));
	}
	if ($data_inicio !== '' && $data_fim !== '') {
		$where[] = 'inf_data BETWEEN :data_inicio AND :data_fim';
		$params[':data_inicio'] = $data_inicio;
		$params[':data_fim'] = $data_fim;
	} elseif ($data_inicio !== '') {
		$where[] = 'inf_data >= :data_inicio';
		$params[':data_inicio'] = $data_inicio;
	} elseif ($data_fim !== '') {
		$where[] = 'inf_data <= :data_fim';
		$params[':data_fim'] = $data_fim;
	}

	if ($filtro === '') {
		$where[] = '1 = 0';
	}

	$where_sql = $where ? implode(' AND ', $where) : '1=1';

	$sql = "SELECT infracoes_gerenciar.*, cadastro_clientes.cli_nome_razao 
		FROM infracoes_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
		WHERE $where_sql
		ORDER BY inf_data DESC";

	$stmt = $conexao->prepare($sql);
	$stmt->execute($params);
	$rows = $stmt->rowCount();

	if (($pagina ?? '') == 'relatorio_infracoes') {
		echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='relatorio_infracoes.php?pagina=relatorio_infracoes" . ($autenticacao ?? '') . "&filtro=1'>
			<input name='fil_nome' id='fil_nome' value='" . htmlspecialchars($fil_nome) . "' placeholder='Cliente'>
			<input name='fil_proprietario' id='fil_proprietario' value='" . htmlspecialchars($fil_proprietario) . "' placeholder='Proprietário'>
			<input name='fil_assunto' id='fil_assunto' value='" . htmlspecialchars($fil_assunto) . "' placeholder='Assunto'>
			<input name='fil_bloco' id='fil_bloco' value='" . htmlspecialchars($fil_bloco) . "' placeholder='Bloco'>
			<input name='fil_apto' id='fil_apto' value='" . htmlspecialchars($fil_apto) . "' placeholder='Apto.'>
			<select name='fil_inf_tipo' id='fil_inf_tipo'>
				<option value='" . htmlspecialchars($fil_inf_tipo) . "'>$fil_inf_tipo_n</option>
				<option value='Notificação de advertência por infração disciplinar'>Notificação de advertência por infração disciplinar</option>
				<option value='Multa por Infração Interna'>Multa por Infração Interna</option>
				<option value='Notificação de ressarcimento'>Notificação de ressarcimento</option>
				<option value='Comunicação interna'>Comunicação interna</option>
				<option value=''>Todos</option>
			</select>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início' value='" . $fil_data_inicio . "' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim' value='" . $fil_data_fim . "' onkeypress='return mascaraData(this,event);'>
			<input type='submit' value='Filtrar'> 
			<input type='button' onclick=\"PrintDiv('imprimir');\" value='Imprimir' />
			</form>
		</div>
		<div class='contentPrint' id='imprimir'>
	";
		if ($rows > 0) {
			echo "
		<br>
		<img src='" . ($logo ?? '') . "' border='0' valign='middle' class='logo' /> 
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>N.</td>
				<td class='titulo_tabela'>Tipo</td>
				<td class='titulo_tabela'>Assunto</td>
				<td class='titulo_tabela'>Cliente</td>
				<td class='titulo_tabela'>Proprietário</td>
				<td class='titulo_tabela' align='center'>Bloco/Apto</td>
				<td class='titulo_tabela' align='center'>Data</td>					
			</tr>";
			$c = 0;
			foreach ($stmt as $row) {
				$inf_id = $row['inf_id'];
				$cli_nome_razao = $row['cli_nome_razao'];
				$inf_ano = $row['inf_ano'];
				$inf_tipo = $row['inf_tipo'];
				$inf_proprietario = $row['inf_proprietario'];
				$inf_data = $row['inf_data'] ? implode('/', array_reverse(explode('-', $row['inf_data']))) : '';
				$inf_bloco = $row['inf_bloco'];
				$inf_apto = $row['inf_apto'];
				$inf_assunto = $row['inf_assunto'];
				$c1 = $c++ % 2 == 0 ? 'linhaimpar' : 'linhapar';
				echo "<tr class='$c1'>
					  <td>" . str_pad($inf_id, 3, '0', STR_PAD_LEFT) . "/$inf_ano</td>
					  <td>$inf_tipo</td>
					  <td>$inf_assunto</td>
					  <td>$cli_nome_razao</td>
					  <td>$inf_proprietario</td>
					  <td align='center'>$inf_bloco/$inf_apto</td>
					  <td align=center>$inf_data</td>
				  </tr>";
			}
			echo '</table>';
		} else {
			echo '<br><br><br>Selecione acima os filtros que deseja para gerar o relatório.';
		}
		echo "
		<div class='titulo'>  </div>				
		</div>
	</div>";
	}

	include '../mod_rodape/rodape.php';
	?>
		<script src="../mod_includes/js/jquery-1.3.2.min.js" type="text/javascript"></script>
			<script src="../mod_includes/js/elementPrint.js" type="text/javascript"></script>
</body>

</html>