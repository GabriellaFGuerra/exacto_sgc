<?php
session_start();
$pagina_link = 'malote_gerenciar';
require_once '../mod_includes/php/connect.php';

// Função utilitária para obter POST/GET com fallback
function request($key, $default = '')
{
	return $_POST[$key] ?? $_GET[$key] ?? $default;
}

// Função para inverter datas (dd/mm/yyyy <-> yyyy-mm-dd)
function invertDate($date, $toDb = true)
{
	if (!$date)
		return '';
	if ($toDb) {
		$parts = explode('/', $date);
		return count($parts) === 3 ? "$parts[2]-$parts[1]-$parts[0]" : $date;
	}
	$parts = explode('-', $date);
	return count($parts) === 3 ? "$parts[2]/$parts[1]/$parts[0]" : $date;
}

// Função para formatar valores monetários
function formatValue($value)
{
	return str_replace(',', '.', str_replace('.', '', $value));
}

// Função para upload de arquivos
function uploadFiles($files, $destino)
{
	$result = [];
	if (!file_exists($destino)) {
		mkdir($destino, 0755, true);
	}
	foreach ($files['name'] as $k => $name) {
		if ($name) {
			$ext = pathinfo($name, PATHINFO_EXTENSION);
			$filename = $destino . md5(mt_rand(1, 10000) . $name) . '.' . $ext;
			if (move_uploaded_file($files['tmp_name'][$k], $filename)) {
				$result[] = $filename;
			}
		}
	}
	return $result;
}

// Ações
$action = request('action');
$pagina = request('pagina');
$autenticacao = ''; // Defina se necessário

if ($action === 'adicionar') {
	$mal_cliente = request('mal_cliente_id');
	$mal_lacre = request('mal_lacre');
	$mal_observacoes = request('mal_observacoes');
	$stmt = $pdo->prepare(
		"INSERT INTO malote_gerenciar (mal_cliente, mal_lacre, mal_observacoes) VALUES (?, ?, ?)"
	);
	if ($stmt->execute([$mal_cliente, $mal_lacre, $mal_observacoes])) {
		$ultimo_id = $pdo->lastInsertId();

		// Itens do malote
		if (!empty($_POST['fornecedores']) && is_array($_POST['fornecedores'])) {
			foreach ($_POST['fornecedores'] as $valor) {
				$valor = array_filter($valor);
				if (!empty($valor)) {
					$data_venc = isset($valor['mai_data_vencimento']) ? invertDate($valor['mai_data_vencimento']) : null;
					$valor_valor = isset($valor['mai_valor']) ? formatValue($valor['mai_valor']) : null;
					$stmtItem = $pdo->prepare(
						"INSERT INTO malote_itens (mai_fornecedor, mai_tipo_documento, mai_num_cheque, mai_valor, mai_data_vencimento, mai_malote) VALUES (?, ?, ?, ?, ?, ?)"
					);
					$stmtItem->execute([
						$valor['mai_fornecedor'] ?? '',
						$valor['mai_tipo_documento'] ?? '',
						$valor['mai_num_cheque'] ?? '',
						$valor_valor,
						$data_venc,
						$ultimo_id
					]);
				}
			}
		}

		// Upload arquivos
		$caminho = "../admin/malote/$ultimo_id/";
		$mal_pg_eletronico = uploadFiles($_FILES['mal_pg_eletronico'], $caminho);
		$mal_pg_eletronico2 = uploadFiles($_FILES['mal_pg_eletronico2'], $caminho);

		if ($mal_pg_eletronico) {
			$pdo->prepare(
				"UPDATE malote_gerenciar SET mal_pg_eletronico = ? WHERE mal_id = ?"
			)->execute([$mal_pg_eletronico[0], $ultimo_id]);
		}
		if ($mal_pg_eletronico2) {
			$pdo->prepare(
				"UPDATE malote_gerenciar SET mal_pg_eletronico2 = ? WHERE mal_id = ?"
			)->execute([$mal_pg_eletronico2[0], $ultimo_id]);
		}

		echo "<script>abreMask('<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );</script>";
	} else {
		echo "<script>abreMask('<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');</script>";
	}
	exit;
}

// Filtros
$num_por_pagina = 10;
$pag = (int) request('pag', 1);
$primeiro_registro = ($pag - 1) * $num_por_pagina;

$fil_malote = request('fil_malote');
$fil_lacre = request('fil_lacre');
$fil_cheque = request('fil_cheque');
$fil_nome = request('fil_nome');
$fil_data_inicio = invertDate(request('fil_data_inicio'));
$fil_data_fim = invertDate(request('fil_data_fim'));
$fil_baixado = request('fil_baixado');

// Montagem dinâmica dos filtros
$where = ['ucl_usuario = :usuario'];
$params = [':usuario' => $_SESSION['usuario_id']];

if ($fil_malote) {
	$where[] = 'mal_id = :malote';
	$params[':malote'] = $fil_malote;
}
if ($fil_lacre) {
	$where[] = 'mal_lacre LIKE :lacre';
	$params[':lacre'] = "%$fil_lacre%";
}
if ($fil_nome) {
	$where[] = 'cli_nome_razao LIKE :nome';
	$params[':nome'] = "%$fil_nome%";
}
if ($fil_cheque) {
	$where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_num_cheque = :cheque)';
	$params[':cheque'] = $fil_cheque;
}
if ($fil_data_inicio && $fil_data_fim) {
	$where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_data_vencimento BETWEEN :data_inicio AND :data_fim)';
	$params[':data_inicio'] = $fil_data_inicio;
	$params[':data_fim'] = $fil_data_fim . ' 23:59:59';
} elseif ($fil_data_inicio) {
	$where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_data_vencimento >= :data_inicio)';
	$params[':data_inicio'] = $fil_data_inicio;
} elseif ($fil_data_fim) {
	$where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_data_vencimento <= :data_fim)';
	$params[':data_fim'] = $fil_data_fim . ' 23:59:59';
}
if ($fil_baixado !== '') {
	if ($fil_baixado == 1) {
		$where[] = 'mal_id NOT IN (SELECT mai_malote FROM malote_itens WHERE mai_baixado IS NULL)';
	} elseif ($fil_baixado == 0) {
		$where[] = 'mal_id IN (SELECT mai_malote FROM malote_itens WHERE mai_baixado IS NULL)';
	}
}

$where_sql = implode(' AND ', $where);

// Consulta principal
$sql = "SELECT * FROM malote_gerenciar 
	LEFT JOIN (cadastro_clientes 
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
	ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
	WHERE $where_sql
	ORDER BY mal_data_cadastro DESC
	LIMIT $primeiro_registro, $num_por_pagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo $titulo ?? 'Malote Gerenciar'; ?></title>
    <meta name="author" content="MogiComp">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
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

	$page = "Malotes &raquo; <a href='malote_gerenciar.php?pagina=malote_gerenciar$autenticacao'>Gerenciar</a>";

	if ($pagina == 'malote_gerenciar') {
		echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Malote' type='button' onclick=\"window.location.href='malote_gerenciar.php?pagina=adicionar_malote_gerenciar$autenticacao';\" /></div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='malote_gerenciar.php?pagina=malote_gerenciar$autenticacao'>
			<input name='fil_malote' id='fil_malote' value='$fil_malote' placeholder='N° malote'  size='10'>
			<input name='fil_lacre' id='fil_lacre' value='$fil_lacre' placeholder='N° lacre'  size='10'>
			<input name='fil_cheque' id='fil_cheque' value='$fil_cheque' placeholder='N° Cheque' size='10'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início'  size='10' value='" . invertDate($fil_data_inicio, false) . "' onkeypress='return mascaraData(this,event);'>
			<input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim'  size='10' value='" . invertDate($fil_data_fim, false) . "' onkeypress='return mascaraData(this,event);'>
			<select name='fil_baixado' id='fil_baixado'>
				<option value='$fil_baixado'>" . ($fil_baixado === '' ? 'Baixado?' : ($fil_baixado ? 'Sim' : 'Não')) . "</option>
				<option value='1'>Sim</option>
				<option value='0'>Não</option>
				<option value=''>Todos</option>
			</select>
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
		";
		if ($rows) {
			echo "
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>N° Malote</td>
				<td class='titulo_tabela'>N° Lacre</td>
				<td class='titulo_tabela'>Cliente</td>
				<td class='titulo_tabela'>Observação</td>
				<td class='titulo_tabela' align='center'>Data Cadastro</td>
				<td class='titulo_tabela' align='center'>Protocolo</td>
				<td class='titulo_tabela' align='center'>Baixado?</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>";
			$c = 0;
			foreach ($rows as $row) {
				$mal_id = $row['mal_id'];
				$mal_lacre = $row['mal_lacre'];
				$cli_nome_razao = $row['cli_nome_razao'];
				$mal_observacoes = $row['mal_observacoes'];
				$mal_data_cadastro = invertDate(substr($row['mal_data_cadastro'], 0, 10), false);
				$mal_hora_cadastro = substr($row['mal_data_cadastro'], 11, 5);

				// Verifica se todos os itens estão baixados
				$stmt_baixado = $pdo->prepare("SELECT mai_baixado FROM malote_itens WHERE mai_malote = ?");
				$stmt_baixado->execute([$mal_id]);
				$itens = $stmt_baixado->fetchAll(PDO::FETCH_COLUMN);
				$mai_baixado = $itens && count(array_filter($itens, fn($b) => $b != 1)) === 0 ? 1 : 0;
				$mai_baixado_n = $mai_baixado ? "<span class='verde'>Sim</span>" : "<span class='vermelho'>Não</span>";

				$c1 = $c++ % 2 == 0 ? "linhaimpar" : "linhapar";
				echo "<tr class='$c1'>
				  <td><a href='malote_gerenciar.php?pagina=exibir_malote_gerenciar&mal_id=$mal_id$autenticacao'><b>$mal_id</b></a></td>
				  <td>$mal_lacre</td>
				  <td>$cli_nome_razao</td>
				  <td>$mal_observacoes</td>
				  <td align='center'>$mal_data_cadastro<br><span class='detalhe'>$mal_hora_cadastro</span></td>
				  <td align='center'><img class='mouse' src='../imagens/icon-pdf.png' onclick=\"window.open('malote_imprimir.php?mal_id=$mal_id$autenticacao');\"></td>
				  <td align='center'>$mai_baixado_n</td>
				  <td align=center>
					<a href='malote_gerenciar.php?pagina=exibir_malote_gerenciar&mal_id=$mal_id$autenticacao'><img border='0' src='../imagens/icon-exibir.png'></a>
					<a href='malote_gerenciar.php?pagina=editar_malote_gerenciar&mal_id=$mal_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
					<a onclick=\"abreMask('Deseja realmente excluir o malote <b>$cli_nome_razao</b>?<br><br><input value=\\' Sim \\' type=\\'button\\' onclick=javascript:window.location.href=\\'malote_gerenciar.php?pagina=malote_gerenciar&action=excluir&mal_id=$mal_id$autenticacao\\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input value=\\' Não \\' type=\\'button\\' class=\\'close_janela\\'>');\"><img border='0' src='../imagens/icon-excluir.png'></a>
				  </td>
			  </tr>";
			}
			echo "</table>";
		} else {
			echo "<br><br><br>Não há nenhum malote cadastrado.";
		}
		echo "<div class='titulo'>  </div></div>";
	}

	include '../mod_rodape/rodape.php';
	?>
    ?>
</body>

</html>