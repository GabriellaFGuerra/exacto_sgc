<?php
session_start();
$paginaAtual = 'infracoes_gerenciar';
require_once '../mod_includes/php/connect.php';

require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';
include '../mod_topo/topo.php';

// Funções utilitárias
function mostrarMascara($mensagem)
{
	echo "<script>abreMask(`$mensagem`);</script>";
}

function mostrarMascaraAcao($mensagem)
{
	echo "<script>abreMaskAcao(`$mensagem`);</script>";
}

function redirecionar($url)
{
	header("Location: $url");
	exit;
}

function dataParaBanco($data)
{
	if (!$data)
		return null;
	$partes = explode('/', $data);
	if (count($partes) === 3)
		return "{$partes[2]}-{$partes[1]}-{$partes[0]}";
	return $data;
}

function dataParaBR($data)
{
	if (!$data)
		return '';
	$partes = explode('-', $data);
	if (count($partes) === 3)
		return "{$partes[2]}/{$partes[1]}/{$partes[0]}";
	return $data;
}

// Parâmetros da requisição
$acao = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? 'infracoes_gerenciar';
$autenticacao = $_GET['autenticacao'] ?? '';
$paginaNumero = max(1, intval($_GET['pag'] ?? 1));

// Página de navegação
$tituloPagina = "Infrações &raquo; <a href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'>Gerenciar</a>";

// Processamento de formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($acao === "adicionar" || $acao === "duplicar") {
		$clienteId = $_POST['inf_cliente_id'] ?? null;
		$tipo = $_POST['inf_tipo'] ?? '';
		$ano = date("Y");
		$cidade = $_POST['inf_cidade'] ?? '';
		$data = dataParaBanco($_POST['inf_data'] ?? '');
		$proprietario = $_POST['inf_proprietario'] ?? '';
		$apto = $_POST['inf_apto'] ?? '';
		$bloco = $_POST['inf_bloco'] ?? '';
		$endereco = $_POST['inf_endereco'] ?? '';
		$email = $_POST['inf_email'] ?? '';
		$descIrregularidade = $_POST['inf_desc_irregularidade'] ?? '';
		$assunto = $_POST['inf_assunto'] ?? '';
		$descArtigo = $_POST['inf_desc_artigo'] ?? '';
		$descNotificacao = $_POST['inf_desc_notificacao'] ?? '';

		$stmt = $pdo->prepare(
			"INSERT INTO infracoes_gerenciar (
				inf_cliente, inf_tipo, inf_ano, inf_cidade, inf_data, inf_proprietario, inf_apto, inf_bloco, inf_endereco, inf_email, inf_desc_irregularidade, inf_assunto, inf_desc_artigo, inf_desc_notificacao
			) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
		);
		$sucesso = $stmt->execute([
			$clienteId,
			$tipo,
			$ano,
			$cidade,
			$data,
			$proprietario,
			$apto,
			$bloco,
			$endereco,
			$email,
			$descIrregularidade,
			$assunto,
			$descArtigo,
			$descNotificacao
		]);
		if ($sucesso) {
			mostrarMascara("<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			mostrarMascara("<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	if ($acao === 'editar') {
		$id = $_GET['inf_id'] ?? null;
		$tipo = $_POST['inf_tipo'] ?? '';
		$cidade = $_POST['inf_cidade'] ?? '';
		$data = dataParaBanco($_POST['inf_data'] ?? '');
		$proprietario = $_POST['inf_proprietario'] ?? '';
		$apto = $_POST['inf_apto'] ?? '';
		$bloco = $_POST['inf_bloco'] ?? '';
		$endereco = $_POST['inf_endereco'] ?? '';
		$email = $_POST['inf_email'] ?? '';
		$descIrregularidade = $_POST['inf_desc_irregularidade'] ?? '';
		$assunto = $_POST['inf_assunto'] ?? '';
		$descArtigo = $_POST['inf_desc_artigo'] ?? '';
		$descNotificacao = $_POST['inf_desc_notificacao'] ?? '';

		$stmt = $pdo->prepare(
			"UPDATE infracoes_gerenciar SET
				inf_tipo = ?, inf_cidade = ?, inf_data = ?, inf_proprietario = ?, inf_apto = ?, inf_bloco = ?, inf_endereco = ?, inf_email = ?, inf_desc_irregularidade = ?, inf_assunto = ?, inf_desc_artigo = ?, inf_desc_notificacao = ?
				WHERE inf_id = ?"
		);
		$sucesso = $stmt->execute([
			$tipo,
			$cidade,
			$data,
			$proprietario,
			$apto,
			$bloco,
			$endereco,
			$email,
			$descIrregularidade,
			$assunto,
			$descArtigo,
			$descNotificacao,
			$id
		]);
		if ($sucesso) {
			mostrarMascara("<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			mostrarMascara("<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	if ($acao === "adicionar_recurso") {
		$infracaoId = $_POST['inf_id'] ?? null;
		$assunto = $_POST['rec_assunto'] ?? '';
		$descricao = $_POST['rec_descricao'] ?? '';
		$status = $_POST['rec_status'] ?? '';

		$stmt = $pdo->prepare(
			"INSERT INTO recurso_gerenciar (rec_infracao, rec_assunto, rec_descricao, rec_status) VALUES (?, ?, ?, ?)"
		);
		$sucesso = $stmt->execute([$infracaoId, $assunto, $descricao, $status]);
		$ultimoId = $pdo->lastInsertId();

		if ($sucesso && isset($_FILES['rec_recurso'])) {
			$arquivos = $_FILES['rec_recurso'];
			$caminho = "../admin/recurso/$ultimoId/";
			if (!is_dir($caminho))
				mkdir($caminho, 0755, true);

			foreach ($arquivos['name'] as $k => $nome) {
				if ($nome) {
					$ext = pathinfo($nome, PATHINFO_EXTENSION);
					$arquivoFinal = $caminho . md5(mt_rand(1, 10000) . $nome) . '.' . $ext;
					move_uploaded_file($arquivos['tmp_name'][$k], $arquivoFinal);
					$stmt2 = $pdo->prepare("UPDATE recurso_gerenciar SET rec_recurso = ? WHERE rec_id = ?");
					$stmt2->execute([$arquivoFinal, $ultimoId]);
				}
			}
			mostrarMascara("<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			mostrarMascara("<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	if ($acao === 'comprovante') {
		$erro = false;
		$id = $_GET['inf_id'] ?? null;
		$arquivos = $_FILES['inf_comprovante'];
		$caminho = "../admin/infracoes_comprovante/$id/";
		if (!is_dir($caminho))
			mkdir($caminho, 0755, true);

		foreach ($arquivos['name'] as $k => $nome) {
			if ($nome) {
				$ext = pathinfo($nome, PATHINFO_EXTENSION);
				$arquivoFinal = $caminho . md5(mt_rand(1, 10000) . $nome) . '.' . $ext;
				move_uploaded_file($arquivos['tmp_name'][$k], $arquivoFinal);
				$stmt = $pdo->prepare("UPDATE infracoes_gerenciar SET inf_comprovante = ? WHERE inf_id = ?");
				if (!$stmt->execute([$arquivoFinal, $id]))
					$erro = true;
			}
		}
		if (!$erro) {
			mostrarMascara("<img src=../imagens/ok.png> Anexo enviado com sucesso.<br><br><input value=' Ok ' type='button' class='close_janela'>");
		} else {
			mostrarMascara("<img src=../imagens/x.png> Erro ao enviar anexo.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}
}

// Exclusão
if ($acao === 'excluir' && isset($_GET['inf_id'])) {
	$stmt = $pdo->prepare("DELETE FROM infracoes_gerenciar WHERE inf_id = ?");
	$sucesso = $stmt->execute([$_GET['inf_id']]);
	if ($sucesso) {
		mostrarMascara("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		mostrarMascara("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

if ($acao === 'excluir_recurso' && isset($_GET['rec_id'])) {
	$stmt = $pdo->prepare("DELETE FROM recurso_gerenciar WHERE rec_id = ?");
	$sucesso = $stmt->execute([$_GET['rec_id']]);
	if ($sucesso) {
		mostrarMascara("<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br><input value=' OK ' type='button' class='close_janela'>");
	} else {
		mostrarMascara("<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br><input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

// Filtros
$itensPorPagina = 10;
$primeiroRegistro = ($paginaNumero - 1) * $itensPorPagina;

$filtroNome = $_REQUEST['fil_nome'] ?? '';
$filtroBloco = $_REQUEST['fil_bloco'] ?? '';
$filtroAssunto = $_REQUEST['fil_assunto'] ?? '';
$filtroApto = $_REQUEST['fil_apto'] ?? '';
$filtroProprietario = $_REQUEST['fil_proprietario'] ?? '';
$filtroTipo = $_REQUEST['fil_inf_tipo'] ?? '';

$where = [];
$params = [$_SESSION['usuario_id']];

if ($filtroNome) {
	$where[] = "cli_nome_razao LIKE ?";
	$params[] = "%$filtroNome%";
}
if ($filtroBloco) {
	$where[] = "inf_bloco LIKE ?";
	$params[] = "%$filtroBloco%";
}
if ($filtroAssunto) {
	$where[] = "inf_assunto LIKE ?";
	$params[] = "%$filtroAssunto%";
}
if ($filtroApto) {
	$where[] = "inf_apto LIKE ?";
	$params[] = "%$filtroApto%";
}
if ($filtroProprietario) {
	$where[] = "inf_proprietario LIKE ?";
	$params[] = "%$filtroProprietario%";
}
if ($filtroTipo) {
	$where[] = "inf_tipo = ?";
	$params[] = $filtroTipo;
}

$whereSql = $where ? implode(' AND ', $where) : '1=1';

// Consulta para paginação
$sqlTotal = "SELECT COUNT(*) FROM infracoes_gerenciar
	LEFT JOIN (cadastro_clientes
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
	ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
	WHERE ucl_usuario = ? AND $whereSql";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Consulta principal
$sql = "SELECT infracoes_gerenciar.*, cli_nome_razao, rec_id
	FROM infracoes_gerenciar
	LEFT JOIN (cadastro_clientes
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
	ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
	LEFT JOIN recurso_gerenciar ON recurso_gerenciar.rec_infracao = infracoes_gerenciar.inf_id
	WHERE ucl_usuario = ? AND $whereSql
	ORDER BY inf_data DESC
	LIMIT $primeiroRegistro, $itensPorPagina";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$infracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
	<div class='titulo'> $tituloPagina  </div>
	<div id='botoes'><input value='Nova Infração' type='button' onclick=\"window.location.href='infracoes_gerenciar.php?pagina=adicionar_infracoes_gerenciar$autenticacao';\" /></div>
	<div class='filtro'>
		<form name='form_filtro' id='form_filtro' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'>
			<input name='fil_nome' value='$filtroNome' placeholder='Cliente'>
			<input name='fil_bloco' value='$filtroBloco' placeholder='Bloco/Quadra'>
			<input name='fil_apto' value='$filtroApto' placeholder='Apto.'>
			<input name='fil_proprietario' value='$filtroProprietario' placeholder='Proprietário'>
			<input name='fil_assunto' value='$filtroAssunto' placeholder='Assunto'>
			<select name='fil_inf_tipo'>
				<option value='$filtroTipo'>" . ($filtroTipo ?: "Tipo de Infração") . "</option>
				<option value='Notificação de advertência por infração disciplinar'>Notificação de advertência por infração disciplinar</option>
				<option value='Multa por Infração Interna'>Multa por Infração Interna</option>
				<option value='Notificação de ressarcimento'>Notificação de ressarcimento</option>
				<option value='Comunicação interna'>Comunicação interna</option>
				<option value=''>Todos</option>
			</select>
			<input type='submit' value='Filtrar'>
		</form>
	</div>";

		if ($infracoes) {
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
			$contador = 0;
			foreach ($infracoes as $infracao) {
				$classeLinha = $contador++ % 2 ? "linhapar" : "linhaimpar";
				$id = $infracao['inf_id'];
				$ano = $infracao['inf_ano'];
				$cliente = $infracao['cli_nome_razao'];
				$tipo = $infracao['inf_tipo'];
				$assunto = $infracao['inf_assunto'];
				$bloco = $infracao['inf_bloco'];
				$apto = $infracao['inf_apto'];
				$proprietario = $infracao['inf_proprietario'];
				$comprovante = $infracao['inf_comprovante'] ?? '';
				$data = dataParaBR($infracao['inf_data']);
				$recursoId = $infracao['rec_id'] ?? '';

				echo "<tr class='$classeLinha'>
				<td>" . str_pad($id, 3, "0", STR_PAD_LEFT) . "/$ano</td>
				<td>$cliente</td>
				<td>$tipo</td>
				<td>$assunto</td>
				<td>$proprietario</td>
				<td>$bloco/$apto</td>
				<td>$data</td>
				<td align='center'><a href='infracoes_imprimir.php?inf_id=$id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
				<td align='center'><a href='infracoes_protocolo_imprimir.php?inf_id=$id&autenticacao' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
				<td align='center'>";
				if ($comprovante)
					echo "<a href='$comprovante' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";
				echo "</td>
				<td align='center'>";
				if ($recursoId) {
					echo "<a href='recurso_gerenciar.php?pagina=recurso_gerenciar&rec_id=$recursoId$autenticacao'><img src='../imagens/icon-exibir.png'></a>";
				} else {
					echo "<a href='infracoes_gerenciar.php?pagina=recurso_gerenciar&inf_id=$id$autenticacao'>Gerar Recurso</a>";
				}
				echo "</td>
				<td align=center>
					<div id='normal-button-$id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div>
				</td>
			</tr>";
			}
			echo "</table>";

			// Paginação
			if ($totalPaginas > 1) {
				echo "<div class='paginacao' style='text-align:center; margin:20px 0;'>";
				for ($i = 1; $i <= $totalPaginas; $i++) {
					$classe = ($i == $paginaNumero) ? "pagina-ativa" : "";
					$url = "infracoes_gerenciar.php?pagina=infracoes_gerenciar&pag=$i$autenticacao";
					echo "<a class='$classe' href='$url'>$i</a> ";
				}
				echo "</div>";
			}
		} else {
			echo "<br><br><br>Não há nenhuma infração cadastrada.";
		}
		echo "<div class='titulo'>  </div></div>";
	}

	include '../mod_rodape/rodape.php';
	?>
</body>

</html>