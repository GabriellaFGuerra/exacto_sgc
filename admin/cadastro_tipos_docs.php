<?php
session_start();
$pagina_link = 'cadastro_tipos_docs';
include '../mod_includes/php/connect.php';

// Função para exibir mensagens com JavaScript
function showMessage($img, $msg, $button = "<input value=' Ok ' type='button' class='close_janela'>")
{
	echo "
	<script>
		abreMask('<img src=../imagens/$img.png> $msg<br><br>$button');
	</script>
	";
}

// Variáveis de controle
$pagina = $_GET['pagina'] ?? '';
$action = $_GET['action'] ?? '';
$autenticacao = $_GET['autenticacao'] ?? '';
$pag = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
$titulo = 'Tipos de Documento';
$itensPorPagina = 20;
$offset = ($pag - 1) * $itensPorPagina;

// CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$tpd_nome = trim($_POST['tpd_nome'] ?? '');

	if ($action === 'adicionar') {
		$stmt = $pdo->prepare('INSERT INTO cadastro_tipos_docs (tpd_nome) VALUES (:tpd_nome)');
		if ($stmt->execute(['tpd_nome' => $tpd_nome])) {
			showMessage('ok', 'Cadastro efetuado com sucesso.');
		} else {
			showMessage('x', 'Erro ao efetuar cadastro, por favor tente novamente.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}

	if ($action === 'editar') {
		$tpd_id = (int) ($_GET['tpd_id'] ?? 0);
		$stmt = $pdo->prepare('UPDATE cadastro_tipos_docs SET tpd_nome = :tpd_nome WHERE tpd_id = :tpd_id');
		if ($stmt->execute(['tpd_nome' => $tpd_nome, 'tpd_id' => $tpd_id])) {
			showMessage('ok', 'Dados alterados com sucesso.');
		} else {
			showMessage('x', 'Erro ao alterar dados, por favor tente novamente.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
		}
	}
}

if ($action === 'excluir') {
	$tpd_id = (int) ($_GET['tpd_id'] ?? 0);
	$stmt = $pdo->prepare('DELETE FROM cadastro_tipos_docs WHERE tpd_id = :tpd_id');
	if ($stmt->execute(['tpd_id' => $tpd_id])) {
		showMessage('ok', 'Exclusão realizada com sucesso');
	} else {
		showMessage('x', 'Este tipo de documento não pode ser excluído pois está relacionado com alguma tabela.', "<input value=' Ok ' type='button' onclick='window.history.back();'>");
	}
}

// Cabeçalho HTML
?>
<!DOCTYPE html> <html lang="pt-br">

<head>
	<title>
		<?= $titulo ?>
	</title>
	<meta name="author" content="MogiComp">
		<meta charset="utf-8" />
		<link rel="shortcut icon" href="../imagens/favicon.png">
		<?php include '../css/style.php'; ?>
		<script src="../mod_includes/js/funcoes.js"></script>
		<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
		<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
		<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
		<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script> </head> <body> <?php
		include '../mod_includes/php/funcoes-jquery.php';
		require_once '../mod_includes/php/verificalogin.php';
		include '../mod_topo/topo.php';
		require_once '../mod_includes/php/verificapermissao.php';

		$pageBreadcrumb = "Cadastros &raquo; <a href='cadastro_tipos_docs.php?pagina=cadastro_tipos_docs$autenticacao'>Tipos de Documento</a>";

		if ($pagina === 'cadastro_tipos_docs') {
			// Paginação
			$stmtTotal = $pdo->query('SELECT COUNT(*) FROM cadastro_tipos_docs');
			$totalRegistros = (int) $stmtTotal->fetchColumn();

			$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC LIMIT :offset, :limit');
			$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
			$stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
			$stmt->execute();
			$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

			echo "
	<div class='centro'>
		<div class='titulo'> $pageBreadcrumb </div>
		<div id='botoes'>
			<input value='Novo Documento' type='button' onclick=\"window.location.href='cadastro_tipos_docs.php?pagina=adicionar_cadastro_tipos_docs$autenticacao';\" />
		</div>
	";

			if ($totalRegistros > 0) {
				echo "
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Documento</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>";
				foreach ($docs as $index => $doc) {
					$tpd_id = $doc['tpd_id'];
					$tpd_nome = htmlspecialchars($doc['tpd_nome']);
					$rowClass = $index % 2 === 0 ? 'linhaimpar' : 'linhapar';
					echo "
			<script>
				$(function() {
					$('#normal-button-$tpd_id').toolbar({content: '#user-options-$tpd_id', position: 'top', hideOnClick: true});
				});
			</script>
			<div id='user-options-$tpd_id' class='toolbar-icons' style='display: none;'>
				<a href='cadastro_tipos_docs.php?pagina=editar_cadastro_tipos_docs&tpd_id=$tpd_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
				<a onclick=\"
					abreMask(
						'Deseja realmente excluir o tipo de documento <b>" . addslashes($tpd_nome) . "</b>?<br><br>'+
						'<input value=\' Sim \' type=\'button\' onclick=window.location.href=\\'cadastro_tipos_docs.php?pagina=cadastro_tipos_docs&action=excluir&tpd_id=$tpd_id$autenticacao\\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\' Não \' type=\'button\' class=\'close_janela\'' );
					\">
					<img border='0' src='../imagens/icon-excluir.png'>
				</a>
			</div>
			<tr class='$rowClass'>
				<td>$tpd_nome</td>
				<td align='center'><div id='normal-button-$tpd_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
			</tr>";
				}
				echo '</table>';

				// Paginação
				$totalPaginas = ceil($totalRegistros / $itensPorPagina);
				$variavel = "&pagina=cadastro_tipos_docs$autenticacao";
				include '../mod_includes/php/paginacao.php';

			} else {
				echo '<br><br><br>Não há nenhum tipo de documento cadastrado.';
			}
			echo "<div class='titulo'></div></div>";
		}

		if ($pagina === 'adicionar_cadastro_tipos_docs') {
			echo "
	<form name='form_cadastro_tipos_docs' id='form_cadastro_tipos_docs' enctype='multipart/form-data' method='post' action='cadastro_tipos_docs.php?pagina=cadastro_tipos_docs&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $pageBreadcrumb &raquo; Adicionar </div>
		<table align='center' cellspacing='0' width='500'>
			<tr>
				<td align='center'>
					<input name='tpd_nome' id='tpd_nome' placeholder='Nome do Documento'>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='submit' id='bt_cadastro_tipos_docs' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=\"window.location.href='cadastro_tipos_docs.php?pagina=cadastro_tipos_docs$autenticacao';\" value='Cancelar'/>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'></div>
	</div>
	</form>
	";
		}

		if ($pagina === 'editar_cadastro_tipos_docs') {
			$tpd_id = (int) ($_GET['tpd_id'] ?? 0);
			$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_docs WHERE tpd_id = :tpd_id');
			$stmt->execute(['tpd_id' => $tpd_id]);
			$doc = $stmt->fetch(PDO::FETCH_ASSOC);

			if ($doc) {
				$tpd_nome = htmlspecialchars($doc['tpd_nome']);
				echo "
		<form name='form_cadastro_tipos_docs' id='form_cadastro_tipos_docs' enctype='multipart/form-data' method='post' action='cadastro_tipos_docs.php?pagina=cadastro_tipos_docs&action=editar&tpd_id=$tpd_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $pageBreadcrumb &raquo; Editar: $tpd_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input name='tpd_nome' id='tpd_nome' value=\"$tpd_nome\" placeholder='Nome do Documento'>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='submit' id='bt_cadastro_tipos_docs' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=\"window.location.href='cadastro_tipos_docs.php?pagina=cadastro_tipos_docs$autenticacao';\" value='Cancelar'/>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'></div>
		</div>
		</form>
		";
			}
		}

		include '../mod_rodape/rodape.php';
		?>
</body>
</html>