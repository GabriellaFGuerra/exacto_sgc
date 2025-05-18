<?php
session_start();
$pagina_link = 'infracoes_gerenciar';
include '../mod_includes/php/connect.php';

require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';
include '../mod_topo/topo.php';

function abreMask($msg)
{
	echo "<script>abreMask(`$msg`);</script>";
}

function abreMaskAcao($msg)
{
	echo "<script>abreMaskAcao(`$msg`);</script>";
}

function redirect($url)
{
	header("Location: $url");
	exit;
}

function formatDateToDB($date)
{
	if (!$date)
		return null;
	$parts = explode('/', $date);
	if (count($parts) === 3)
		return "{$parts[2]}-{$parts[1]}-{$parts[0]}";
	return $date;
}

function formatDateToBR($date)
{
	if (!$date)
		return '';
	$parts = explode('-', $date);
	if (count($parts) === 3)
		return "{$parts[2]}/{$parts[1]}/{$parts[0]}";
	return $date;
}

$action = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? 'infracoes_gerenciar';
$autenticacao = $_GET['autenticacao'] ?? '';
$pag = $_GET['pag'] ?? 1;

$page = "Infrações &raquo; <a href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'>Gerenciar</a>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Adicionar
	if ($action === "adicionar" || $action === "duplicar") {
		$inf_cliente = $_POST['inf_cliente_id'] ?? null;
		$inf_tipo = $_POST['inf_tipo'] ?? '';
		$inf_ano = date("Y");
		$inf_cidade = $_POST['inf_cidade'] ?? '';
		$inf_data = formatDateToDB($_POST['inf_data'] ?? '');
		$inf_proprietario = $_POST['inf_proprietario'] ?? '';
		$inf_apto = $_POST['inf_apto'] ?? '';
		$inf_bloco = $_POST['inf_bloco'] ?? '';
		$inf_endereco = $_POST['inf_endereco'] ?? '';
		$inf_email = $_POST['inf_email'] ?? '';
		$inf_desc_irregularidade = $_POST['inf_desc_irregularidade'] ?? '';
		$inf_assunto = $_POST['inf_assunto'] ?? '';
		$inf_desc_artigo = $_POST['inf_desc_artigo'] ?? '';
		$inf_desc_notificacao = $_POST['inf_desc_notificacao'] ?? '';

		$stmt = $pdo->prepare(
			"INSERT INTO infracoes_gerenciar (
				inf_cliente, inf_tipo, inf_ano, inf_cidade, inf_data, inf_proprietario, inf_apto, inf_bloco, inf_endereco, inf_email, inf_desc_irregularidade, inf_assunto, inf_desc_artigo, inf_desc_notificacao
			) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
		);
		$ok = $stmt->execute([
			$inf_cliente,
			$inf_tipo,
			$inf_ano,
			$inf_cidade,
			$inf_data,
			$inf_proprietario,
			$inf_apto,
			$inf_bloco,
			$inf_endereco,
			$inf_email,
			$inf_desc_irregularidade,
			$inf_assunto,
			$inf_desc_artigo,
			$inf_desc_notificacao
		]);
		if ($ok) {
			abreMask("<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			abreMask("<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	// Editar
	if ($action === 'editar') {
		$inf_id = $_GET['inf_id'] ?? null;
		$inf_tipo = $_POST['inf_tipo'] ?? '';
		$inf_cidade = $_POST['inf_cidade'] ?? '';
		$inf_data = formatDateToDB($_POST['inf_data'] ?? '');
		$inf_proprietario = $_POST['inf_proprietario'] ?? '';
		$inf_apto = $_POST['inf_apto'] ?? '';
		$inf_bloco = $_POST['inf_bloco'] ?? '';
		$inf_endereco = $_POST['inf_endereco'] ?? '';
		$inf_email = $_POST['inf_email'] ?? '';
		$inf_desc_irregularidade = $_POST['inf_desc_irregularidade'] ?? '';
		$inf_assunto = $_POST['inf_assunto'] ?? '';
		$inf_desc_artigo = $_POST['inf_desc_artigo'] ?? '';
		$inf_desc_notificacao = $_POST['inf_desc_notificacao'] ?? '';

		$stmt = $pdo->prepare(
			"UPDATE infracoes_gerenciar SET
				inf_tipo = ?, inf_cidade = ?, inf_data = ?, inf_proprietario = ?, inf_apto = ?, inf_bloco = ?, inf_endereco = ?, inf_email = ?, inf_desc_irregularidade = ?, inf_assunto = ?, inf_desc_artigo = ?, inf_desc_notificacao = ?
				WHERE inf_id = ?"
		);
		$ok = $stmt->execute([
			$inf_tipo,
			$inf_cidade,
			$inf_data,
			$inf_proprietario,
			$inf_apto,
			$inf_bloco,
			$inf_endereco,
			$inf_email,
			$inf_desc_irregularidade,
			$inf_assunto,
			$inf_desc_artigo,
			$inf_desc_notificacao,
			$inf_id
		]);
		if ($ok) {
			abreMask("<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			abreMask("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	// Adicionar recurso
	if ($action === "adicionar_recurso") {
		$rec_infracao = $_POST['inf_id'] ?? null;
		$rec_assunto = $_POST['rec_assunto'] ?? '';
		$rec_descricao = $_POST['rec_descricao'] ?? '';
		$rec_status = $_POST['rec_status'] ?? '';

		$stmt = $pdo->prepare(
			"INSERT INTO recurso_gerenciar (rec_infracao, rec_assunto, rec_descricao, rec_status) VALUES (?, ?, ?, ?)"
		);
		$ok = $stmt->execute([$rec_infracao, $rec_assunto, $rec_descricao, $rec_status]);
		$ultimo_id = $pdo->lastInsertId();

		if ($ok && isset($_FILES['rec_recurso'])) {
			$files = $_FILES['rec_recurso'];
			$caminho = "../admin/recurso/$ultimo_id/";
			if (!is_dir($caminho))
				mkdir($caminho, 0755, true);

			foreach ($files['name'] as $k => $name) {
				if ($name) {
					$ext = pathinfo($name, PATHINFO_EXTENSION);
					$arquivo = $caminho . md5(mt_rand(1, 10000) . $name) . '.' . $ext;
					move_uploaded_file($files['tmp_name'][$k], $arquivo);
					$stmt2 = $pdo->prepare("UPDATE recurso_gerenciar SET rec_recurso = ? WHERE rec_id = ?");
					$stmt2->execute([$arquivo, $ultimo_id]);
				}
			}
			abreMask("<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			abreMask("<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	// Comprovante
	if ($action === 'comprovante') {
		$erro = false;
		$inf_id = $_GET['inf_id'] ?? null;
		$files = $_FILES['inf_comprovante'];
		$caminho = "../admin/infracoes_comprovante/$inf_id/";
		if (!is_dir($caminho))
			mkdir($caminho, 0755, true);

		foreach ($files['name'] as $k => $name) {
			if ($name) {
				$ext = pathinfo($name, PATHINFO_EXTENSION);
				$arquivo = $caminho . md5(mt_rand(1, 10000) . $name) . '.' . $ext;
				move_uploaded_file($files['tmp_name'][$k], $arquivo);
				$stmt = $pdo->prepare("UPDATE infracoes_gerenciar SET inf_comprovante = ? WHERE inf_id = ?");
				if (!$stmt->execute([$arquivo, $inf_id]))
					$erro = true;
			}
		}
		if (!$erro) {
			abreMask("<img src=../imagens/ok.png> Anexo enviado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			abreMask("<img src=../imagens/x.png> Erro ao enviar anexo.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}
}

// Exclusão
if ($action === 'excluir' && isset($_GET['inf_id'])) {
	$stmt = $pdo->prepare("DELETE FROM infracoes_gerenciar WHERE inf_id = ?");
	$ok = $stmt->execute([$_GET['inf_id']]);
	if ($ok) {
		abreMask("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

if ($action === 'excluir_recurso' && isset($_GET['rec_id'])) {
	$stmt = $pdo->prepare("DELETE FROM recurso_gerenciar WHERE rec_id = ?");
	$ok = $stmt->execute([$_GET['rec_id']]);
	if ($ok) {
		abreMask("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		abreMask("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

// Filtros
$num_por_pagina = 10;
$primeiro_registro = ($pag - 1) * $num_por_pagina;

$fil_nome = $_REQUEST['fil_nome'] ?? '';
$fil_bloco = $_REQUEST['fil_bloco'] ?? '';
$fil_assunto = $_REQUEST['fil_assunto'] ?? '';
$fil_apto = $_REQUEST['fil_apto'] ?? '';
$fil_proprietario = $_REQUEST['fil_proprietario'] ?? '';
$fil_inf_tipo = $_REQUEST['fil_inf_tipo'] ?? '';

$where = [];
$params = [$_SESSION['usuario_id']];

if ($fil_nome)
	$where[] = "cli_nome_razao LIKE ?";
$params[] = "%$fil_nome%";
if ($fil_bloco)
	$where[] = "inf_bloco LIKE ?";
$params[] = "%$fil_bloco%";
if ($fil_assunto)
	$where[] = "inf_assunto LIKE ?";
$params[] = "%$fil_assunto%";
if ($fil_apto)
	$where[] = "inf_apto LIKE ?";
$params[] = "%$fil_apto%";
if ($fil_proprietario)
	$where[] = "inf_proprietario LIKE ?";
$params[] = "%$fil_proprietario%";
if ($fil_inf_tipo)
	$where[] = "inf_tipo = ?";
$params[] = $fil_inf_tipo;

$where_sql = $where ? implode(' AND ', $where) : '1=1';

$sql = "SELECT infracoes_gerenciar.*, cli_nome_razao, rec_id
		FROM infracoes_gerenciar
		LEFT JOIN (cadastro_clientes
			INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
		ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
		LEFT JOIN recurso_gerenciar ON recurso_gerenciar.rec_infracao = infracoes_gerenciar.inf_id
		WHERE ucl_usuario = ? AND $where_sql
		ORDER BY inf_data DESC
		LIMIT $primeiro_registro, $num_por_pagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
	<title>Infrações</title>
	<meta charset="utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include "../css/style.php"; ?>
	<script src="../mod_includes/js/funcoes.js"></script>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
	<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
	<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
	<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
	<script src="../mod_includes/js/tinymce/tinymce.min.js"></script>
	<script src="../mod_includes/js/placeholder/plugin.js"></script>
	<script>
		tinymce.init({
			selector: 'textarea',
			plugins: "placeholder image jbimages imagetools advlist link table textcolor media paste",
			toolbar: "undo redo fontsizeselect format bold italic forecolor backcolor alignleft aligncenter alignright alignjustify bullist numlist outdent indent table link media image jbimages",
			imagetools_toolbar: "rotateleft rotateright | flipv fliph | editimage imageoptions",
			paste_data_images: true,
			media_live_embeds: true,
			relative_urls: false,
			elements: 'nourlconvert',
			convert_urls: false,
			paste_auto_cleanup_on_paste: true,
			paste_remove_styles: true,
			paste_remove_styles_if_webkit: true,
			paste_as_text: true
		});
	</script>
</head>

<body>
	<?php
	include '../mod_includes/php/funcoes-jquery.php';

	// Página principal
	if ($pagina === "infracoes_gerenciar") {
		echo "<div class='centro'>
	<div class='titulo'> $page  </div>
	<div id='botoes'><input value='Nova Infração' type='button' onclick=\"window.location.href='infracoes_gerenciar.php?pagina=adicionar_infracoes_gerenciar$autenticacao';\" /></div>
	<div class='filtro'>
		<form name='form_filtro' id='form_filtro' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'>
			<input name='fil_nome' value='$fil_nome' placeholder='Cliente'>
			<input name='fil_bloco' value='$fil_bloco' placeholder='Bloco/Quadra'>
			<input name='fil_apto' value='$fil_apto' placeholder='Apto.'>
			<input name='fil_proprietario' value='$fil_proprietario' placeholder='Proprietário'>
			<input name='fil_assunto' value='$fil_assunto' placeholder='Assunto'>
			<select name='fil_inf_tipo'>
				<option value='$fil_inf_tipo'>" . ($fil_inf_tipo ?: "Tipo de Infração") . "</option>
				<option value='Notificação de advertência por infração disciplinar'>Notificação de advertência por infração disciplinar</option>
				<option value='Multa por Infração Interna'>Multa por Infração Interna</option>
				<option value='Notificação de ressarcimento'>Notificação de ressarcimento</option>
				<option value='Comunicação interna'>Comunicação interna</option>
				<option value=''>Todos</option>
			</select>
			<input type='submit' value='Filtrar'>
		</form>
	</div>";

		if ($rows) {
			echo "<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
		<tr>
			<td class='titulo_tabela'>N.</td>
			<td class='titulo_tabela'>Cliente</td>
			<td class='titulo_tabela'>Tipo</td>
			<td class='titulo_tabela'>Assunto</td>
			<td class='titulo_tabela'>Proprietário</td>
			<td class='titulo_tabela'>Bloco/Quadra/Ap</td>
			<td class='titulo_tabela'>Data</td>
			<td class='titulo_tabela' align='center'>Gerar advertência/multa</td>
			<td class='titulo_tabela' align='center'>Gerar protocolo</td>
			<td class='titulo_tabela' align='center'>Comprovante</td>
			<td class='titulo_tabela' align='center'>Recurso</td>
			<td class='titulo_tabela' align='center'>Gerenciar</td>
		</tr>";
			$c = 0;
			foreach ($rows as $row) {
				$c1 = $c++ % 2 ? "linhapar" : "linhaimpar";
				$inf_id = $row['inf_id'];
				$inf_ano = $row['inf_ano'];
				$cli_nome_razao = $row['cli_nome_razao'];
				$inf_tipo = $row['inf_tipo'];
				$inf_assunto = $row['inf_assunto'];
				$inf_bloco = $row['inf_bloco'];
				$inf_apto = $row['inf_apto'];
				$inf_proprietario = $row['inf_proprietario'];
				$inf_comprovante = $row['inf_comprovante'] ?? '';
				$inf_data = formatDateToBR($row['inf_data']);
				$rec_id = $row['rec_id'] ?? '';

				echo "<tr class='$c1'>
			<td>" . str_pad($inf_id, 3, "0", STR_PAD_LEFT) . "/$inf_ano</td>
			<td>$cli_nome_razao</td>
			<td>$inf_tipo</td>
			<td>$inf_assunto</td>
			<td>$inf_proprietario</td>
			<td>$inf_bloco/$inf_apto</td>
			<td>$inf_data</td>
			<td align='center'><a href='infracoes_imprimir.php?inf_id=$inf_id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
			<td align='center'><a href='infracoes_protocolo_imprimir.php?inf_id=$inf_id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
			<td align='center'>";
				if ($inf_comprovante)
					echo "<a href='$inf_comprovante' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";
				echo "</td>
			<td align='center'>";
				if ($rec_id) {
					echo "<a href='recurso_gerenciar.php?pagina=recurso_gerenciar&rec_id=$rec_id$autenticacao'><img src='../imagens/icon-exibir.png'></a>";
				} else {
					echo "<a href='infracoes_gerenciar.php?pagina=recurso_gerenciar&inf_id=$inf_id$autenticacao'>Gerar Recurso</a>";
				}
				echo "</td>
			<td align=center>
				<div id='normal-button-$inf_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div>
			</td>
		</tr>";
			}
			echo "</table>";
			// Paginação pode ser implementada aqui
		} else {
			echo "<br><br><br>Não há nenhuma infração cadastrada.";
		}
		echo "<div class='titulo'>  </div></div>";
	}

	// Demais páginas (adicionar, editar, duplicar, recurso) podem ser implementadas de forma semelhante, usando prepared statements e PDO.
	
	include '../mod_rodape/rodape.php';
	?>
</body>

</html>