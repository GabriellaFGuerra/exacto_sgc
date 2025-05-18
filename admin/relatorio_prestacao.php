<?php
session_start();
$pagina_link = 'relatorio_prestacao';
include '../mod_includes/php/connect.php';

// Função para formatar datas do formato brasileiro para o formato SQL
function formatarData($data)
{
	if (!$data) return '';
	$partes = explode('/', $data);
	if (count($partes) === 3) {
		return "{$partes[2]}-{$partes[1]}-{$partes[0]}";
	}
	return $data;
}

// Função para montar o filtro SQL e os parâmetros
function montarFiltros(&$parametros)
{
	$filtros = [];

	$nome = $_REQUEST['fil_nome'] ?? '';
	$referencia = $_REQUEST['fil_referencia'] ?? '';
	$data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
	$data_fim = $_REQUEST['fil_data_fim'] ?? '';
	$filtro = $_REQUEST['filtro'] ?? '';

	if ($nome !== '') {
		$filtros[] = "cli_nome_razao LIKE :fil_nome";
		$parametros[':fil_nome'] = "%$nome%";
	}

	if ($referencia !== '') {
		$filtros[] = "pre_referencia = :fil_referencia";
		$parametros[':fil_referencia'] = $referencia;
	}

	$data_inicio_sql = formatarData($data_inicio);
	$data_fim_sql = formatarData($data_fim);

	if ($data_inicio_sql !== '' && $data_fim_sql !== '') {
		$filtros[] = "pre_data_cadastro BETWEEN :fil_data_inicio AND :fil_data_fim";
		$parametros[':fil_data_inicio'] = $data_inicio_sql;
		$parametros[':fil_data_fim'] = $data_fim_sql;
	} elseif ($data_inicio_sql !== '') {
		$filtros[] = "pre_data_cadastro >= :fil_data_inicio";
		$parametros[':fil_data_inicio'] = $data_inicio_sql;
	} elseif ($data_fim_sql !== '') {
		$filtros[] = "pre_data_cadastro <= :fil_data_fim";
		$parametros[':fil_data_fim'] = $data_fim_sql;
	}

	// Só mostra resultados se filtro foi enviado
	$filtros[] = $filtro !== '' ? "1=1" : "1=0";

	return implode(' AND ', $filtros);
}

// Função para exibir paginação
function exibirPaginacao($pagina_atual, $total_paginas, $query_string)
{
	if ($total_paginas <= 1) return;
	echo "<div class='paginacao'>";
	for ($i = 1; $i <= $total_paginas; $i++) {
		if ($i == $pagina_atual) {
			echo "<strong>$i</strong> ";
		} else {
			echo "<a href='relatorio_prestacao.php?$query_string&pagina_atual=$i'>$i</a> ";
		}
	}
	echo "</div>";
}

// Parâmetros de paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina_atual']) ? max(1, intval($_GET['pagina_atual'])) : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Monta filtros e parâmetros
$parametros = [':usuario_id' => $_SESSION['usuario_id']];
$filtros_sql = montarFiltros($parametros);

// Consulta para contar total de registros
$sql_total = "
	SELECT COUNT(*) as total
	FROM prestacao_gerenciar
	LEFT JOIN (
		cadastro_clientes
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
	) ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
	WHERE ucl_usuario = :usuario_id
	  AND $filtros_sql
";
$stmt_total = $conexao->prepare($sql_total);
foreach ($parametros as $chave => $valor) {
	$stmt_total->bindValue($chave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt_total->execute();
$total_registros = $stmt_total->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Consulta principal com paginação
$sql = "
	SELECT prestacao_gerenciar.*, cadastro_clientes.cli_nome_razao
	FROM prestacao_gerenciar
	LEFT JOIN (
		cadastro_clientes
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
	) ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
	WHERE ucl_usuario = :usuario_id
	  AND $filtros_sql
	ORDER BY pre_data_cadastro DESC
	LIMIT :limit OFFSET :offset
";
$stmt = $conexao->prepare($sql);
foreach ($parametros as $chave => $valor) {
	$stmt->bindValue($chave, $valor, is_int($valor) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $registros_por_pagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

$pagina = "Relatórios &raquo; <a href='relatorio_prestacao.php?pagina=relatorio_prestacao" . ($autenticacao ?? '') . "'>Prestação de Contas</a>";

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
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/funcoes.js"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php
if (($_GET['pagina'] ?? '') === "relatorio_prestacao") {
	// Monta query string para manter filtros na paginação
	$query_string = http_build_query([
		'pagina' => 'relatorio_prestacao',
		'fil_nome' => $_REQUEST['fil_nome'] ?? '',
		'fil_referencia' => $_REQUEST['fil_referencia'] ?? '',
		'fil_data_inicio' => $_REQUEST['fil_data_inicio'] ?? '',
		'fil_data_fim' => $_REQUEST['fil_data_fim'] ?? '',
		'filtro' => $_REQUEST['filtro'] ?? ''
	]);
	?>
    <div class='centro'>
        <div class='titulo'> <?php echo $pagina; ?> </div>
        <div class='filtro'>
            <form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post'
                action='relatorio_prestacao.php?pagina=relatorio_prestacao<?php echo ($autenticacao ?? '') ?>&filtro=1'>
                <input name='fil_nome' id='fil_nome'
                    value='<?php echo htmlspecialchars($_REQUEST['fil_nome'] ?? ''); ?>' placeholder='Cliente'>
                <input name='fil_referencia' id='fil_referencia'
                    value='<?php echo htmlspecialchars($_REQUEST['fil_referencia'] ?? ''); ?>' placeholder='Referência'>
                <input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início'
                    value='<?php echo htmlspecialchars($_REQUEST['fil_data_inicio'] ?? ''); ?>'
                    onkeypress='return mascaraData(this,event);'>
                <input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim'
                    value='<?php echo htmlspecialchars($_REQUEST['fil_data_fim'] ?? ''); ?>'
                    onkeypress='return mascaraData(this,event);'>
                <input type='submit' value='Filtrar'>
                <input type='button' onclick="PrintDiv('imprimir');" value='Imprimir' />
            </form>
        </div>
        <div class='contentPrint' id='imprimir'>
            <?php
			if ($total_registros > 0) {
				echo "<br>
				<img src='" . ($logo ?? '') . "' border='0' valign='middle' class='logo' />
				<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
					<tr>
						<td class='titulo_tabela'>N° Prestação de Conta</td>
						<td class='titulo_tabela'>Cliente</td>
						<td class='titulo_tabela'>Referência</td>
						<td class='titulo_tabela'>Data Envio</td>
						<td class='titulo_tabela'>Por</td>
						<td class='titulo_tabela'>Observação</td>
						<td class='titulo_tabela' align='center'>Data Cadastro</td>
					</tr>";
				$c = 0;
				while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
					$pre_id = $row['pre_id'];
					$cli_nome_razao = htmlspecialchars($row['cli_nome_razao']);
					$pre_referencia = htmlspecialchars($row['pre_referencia']);
					$pre_data_envio = $row['pre_data_envio'] ? date('d/m/Y', strtotime($row['pre_data_envio'])) : '';
					$pre_enviado_por = htmlspecialchars($row['pre_enviado_por']);
					$pre_observacoes = htmlspecialchars($row['pre_observacoes']);
					$pre_data_cadastro = $row['pre_data_cadastro'] ? date('d/m/Y', strtotime($row['pre_data_cadastro'])) : '';
					$pre_hora_cadastro = $row['pre_data_cadastro'] ? date('H:i', strtotime($row['pre_data_cadastro'])) : '';
					$classe_linha = $c % 2 == 0 ? "linhaimpar" : "linhapar";
					$c++;
					echo "<tr class='$classe_linha'>
						<td>$pre_id</td>
						<td>$cli_nome_razao</td>
						<td>$pre_referencia</td>
						<td>$pre_data_envio</td>
						<td>$pre_enviado_por</td>
						<td>$pre_observacoes</td>
						<td align='center'>$pre_data_cadastro<br><span class='detalhe'>$pre_hora_cadastro</span></td>
					</tr>";
				}
				echo "</table>";
				// Exibe paginação
				exibirPaginacao($pagina_atual, $total_paginas, $query_string);
			} else {
				echo "<br><br><br>Selecione acima os filtros que deseja para gerar o relatório.";
			}
			?>
            <div class='titulo'></div>
        </div>
    </div>
    <?php
}
include '../mod_rodape/rodape.php';
?>
    <script src="../mod_includes/js/jquery-1.3.2.min.js"></script>
    <script src="../mod_includes/js/elementPrint.js"></script>
</body>

</html>