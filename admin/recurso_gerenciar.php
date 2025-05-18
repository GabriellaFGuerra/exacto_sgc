<?php
session_start();
$pagina_link = 'infracoes_gerenciar';
require_once '../mod_includes/php/connect.php';

$page = "<a href='infracoes_gerenciar.php?pagina=infracoes_gerenciar{$autenticacao}'>Infrações</a> &raquo; Recurso";

// Editar recurso
if (isset($_GET['action']) && $_GET['action'] === 'editar') {
	$rec_id = $_GET['rec_id'] ?? '';
	$rec_assunto = $_POST['rec_assunto'] ?? '';
	$rec_descricao = $_POST['rec_descricao'] ?? '';
	$rec_status = $_POST['rec_status'] ?? '';

	// Atualizar recurso no banco de dados
	$sql = "UPDATE recurso_gerenciar 
			SET rec_assunto = :rec_assunto, rec_descricao = :rec_descricao, rec_status = :rec_status 
			WHERE rec_id = :rec_id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([
		':rec_assunto' => $rec_assunto,
		':rec_descricao' => $rec_descricao,
		':rec_status' => $rec_status,
		':rec_id' => $rec_id
	]);

	// Upload de arquivos
	$rec_recurso = $_FILES['rec_recurso']['name'] ?? [];
	$tmp_anexo = $_FILES['rec_recurso']['tmp_name'] ?? [];
	$caminho = "../admin/recurso/$rec_id/";

	if (!file_exists($caminho)) {
		mkdir($caminho, 0755, true);
	}

	// Obtém o arquivo atual do recurso
	$sql_orcamento = "SELECT rec_recurso FROM recurso_gerenciar WHERE rec_id = :rec_id";
	$stmt = $pdo->prepare($sql_orcamento);
	$stmt->execute([':rec_id' => $rec_id]);
	$anexo_atual = $stmt->fetchColumn();

	foreach ($rec_recurso as $key => $value) {
		if (!empty($rec_recurso[$key])) {
			$extensao = pathinfo($rec_recurso[$key], PATHINFO_EXTENSION);
			$arquivo = $caminho . md5(mt_rand(1, 10000) . $rec_recurso[$key]) . '.' . $extensao;
			move_uploaded_file($tmp_anexo[$key], $arquivo);
			if ($anexo_atual && file_exists($anexo_atual)) {
				unlink($anexo_atual);
			}
			$sql_update = "UPDATE recurso_gerenciar SET rec_recurso = :arquivo WHERE rec_id = :rec_id";
			$stmt = $pdo->prepare($sql_update);
			$stmt->execute([':arquivo' => $arquivo, ':rec_id' => $rec_id]);
		}
	}

	echo "<script>
			abreMask('<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br>
			<input value=\" Ok \" type=\"button\" class=\"close_janela\">');
		  </script>";
}

// Exibe os detalhes do recurso
if (isset($pagina) && $pagina === 'recurso_gerenciar') {
	$rec_id = $_GET['rec_id'] ?? '';

	$sql = "SELECT * FROM recurso_gerenciar 
			LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
			LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
			WHERE rec_id = :rec_id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([':rec_id' => $rec_id]);
	$recurso = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($recurso) {
		echo "<form name='form_recurso_gerenciar' id='form_recurso_gerenciar' enctype='multipart/form-data' method='post' action='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=editar&rec_id=$rec_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Gerenciar: " . htmlspecialchars($recurso['rec_assunto']) . " </div>
			<table align='center' cellspacing='0' width='90%'>
				<tr>
					<td align='left'>
						<b>Cliente:</b> " . htmlspecialchars($recurso['cli_nome_razao']) . " (" . htmlspecialchars($recurso['cli_cnpj']) . ")
						<p>
						<b>Recurso:</b> <a href='" . htmlspecialchars($recurso['rec_recurso']) . "' target='_blank'><img src='../imagens/icon-pdf.png' border='0'></a>
						<p>
						<b>Status:</b> " . htmlspecialchars($recurso['rec_status']) . "
						<p>
						<textarea name='rec_descricao' rows='15' id='rec_descricao' placeholder='Descrição'>" . htmlspecialchars($recurso['rec_descricao']) . "</textarea>
						<p>
						<select name='rec_status' id='rec_status'>
							<option value='" . htmlspecialchars($recurso['rec_status']) . "'>" . htmlspecialchars($recurso['rec_status']) . "</option>
							<option value='Deferido'>Deferido</option>
							<option value='Indeferido'>Indeferido</option>
						</select>
						<p>
						<center>
						<input type='submit' id='bt_recurso_gerenciar' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=\"window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao';\" value='Cancelar'/></center>
					</td>
				</tr>
			</table>
			<div class='titulo'></div>
		</div>
		</form>";
	}
}

require_once '../mod_rodape/rodape.php';
?>