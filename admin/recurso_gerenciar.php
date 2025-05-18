<?php
session_start();
require_once '../mod_includes/php/connect.php';

$pagina_link = 'infracoes_gerenciar';
$autenticacao = $_GET['autenticacao'] ?? '';
$page_breadcrumb = "<a href='infracoes_gerenciar.php?pagina=infracoes_gerenciar{$autenticacao}'>Infrações</a> &raquo; Recurso";

// Função para atualizar recurso
function atualizarRecurso($pdo, $rec_id, $rec_assunto, $rec_descricao, $rec_status, $arquivos) {
	// Atualiza dados principais
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
	$caminho = "../admin/recurso/$rec_id/";
	if (!file_exists($caminho)) {
		mkdir($caminho, 0755, true);
	}

	// Busca o arquivo atual
	$sql = "SELECT rec_recurso FROM recurso_gerenciar WHERE rec_id = :rec_id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([':rec_id' => $rec_id]);
	$anexo_atual = $stmt->fetchColumn();

	foreach ($arquivos['name'] as $key => $nome_arquivo) {
		if (!empty($nome_arquivo)) {
			$extensao = pathinfo($nome_arquivo, PATHINFO_EXTENSION);
			$novo_nome = md5(mt_rand(1, 10000) . $nome_arquivo) . '.' . $extensao;
			$destino = $caminho . $novo_nome;
			move_uploaded_file($arquivos['tmp_name'][$key], $destino);

			// Remove arquivo antigo, se existir
			if ($anexo_atual && file_exists($anexo_atual)) {
				unlink($anexo_atual);
			}

			// Atualiza caminho do arquivo no banco
			$sql = "UPDATE recurso_gerenciar SET rec_recurso = :arquivo WHERE rec_id = :rec_id";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([':arquivo' => $destino, ':rec_id' => $rec_id]);
		}
	}
}

// Editar recurso
if (isset($_GET['action']) && $_GET['action'] === 'editar') {
	$rec_id = $_GET['rec_id'] ?? '';
	$rec_assunto = $_POST['rec_assunto'] ?? '';
	$rec_descricao = $_POST['rec_descricao'] ?? '';
	$rec_status = $_POST['rec_status'] ?? '';
	$arquivos = $_FILES['rec_recurso'] ?? ['name' => []];

	atualizarRecurso($pdo, $rec_id, $rec_assunto, $rec_descricao, $rec_status, $arquivos);

	echo "<script>
			abreMask('<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br>
			<input value=\" Ok \" type=\"button\" class=\"close_janela\">');
		  </script>";
}

// Exibe detalhes do recurso
if (isset($_GET['pagina']) && $_GET['pagina'] === 'recurso_gerenciar') {
	$rec_id = $_GET['rec_id'] ?? '';

	$sql = "SELECT * FROM recurso_gerenciar 
			LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
			LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
			WHERE rec_id = :rec_id";
	$stmt = $pdo->prepare($sql);
	$stmt->execute([':rec_id' => $rec_id]);
	$recurso = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($recurso) {
		?>
		<form name="form_recurso_gerenciar" id="form_recurso_gerenciar" enctype="multipart/form-data" method="post" action="infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=editar&rec_id=<?= htmlspecialchars($rec_id) . $autenticacao ?>">
			<div class="centro">
				<div class="titulo"><?= $page_breadcrumb ?> &raquo; Gerenciar: <?= htmlspecialchars($recurso['rec_assunto']) ?></div>
				<table align="center" cellspacing="0" width="90%">
					<tr>
						<td align="left">
							<b>Cliente:</b> <?= htmlspecialchars($recurso['cli_nome_razao']) ?> (<?= htmlspecialchars($recurso['cli_cnpj']) ?>)
							<p>
							<b>Recurso:</b>
							<?php if (!empty($recurso['rec_recurso'])): ?>
								<a href="<?= htmlspecialchars($recurso['rec_recurso']) ?>" target="_blank"><img src="../imagens/icon-pdf.png" border="0"></a>
							<?php else: ?>
								Nenhum arquivo anexado.
							<?php endif; ?>
							<p>
							<b>Status:</b> <?= htmlspecialchars($recurso['rec_status']) ?>
							<p>
							<textarea name="rec_descricao" rows="15" id="rec_descricao" placeholder="Descrição"><?= htmlspecialchars($recurso['rec_descricao']) ?></textarea>
							<p>
							<select name="rec_status" id="rec_status">
								<option value="<?= htmlspecialchars($recurso['rec_status']) ?>"><?= htmlspecialchars($recurso['rec_status']) ?></option>
								<option value="Deferido">Deferido</option>
								<option value="Indeferido">Indeferido</option>
							</select>
							<p>
							<center>
								<input type="submit" id="bt_recurso_gerenciar" value="Salvar" />&nbsp;&nbsp;&nbsp;&nbsp; 
								<input type="button" id="botao_cancelar" onclick="window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar<?= $autenticacao ?>';" value="Cancelar"/>
							</center>
						</td>
					</tr>
				</table>
				<div class="titulo"></div>
			</div>
		</form>
		<?php
	}
}

// Paginação para listagem de recursos
if (isset($_GET['pagina']) && $_GET['pagina'] === 'recurso_listar') {
	$itens_por_pagina = 10;
	$pagina_atual = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
	$offset = ($pagina_atual - 1) * $itens_por_pagina;

	// Conta total de registros
	$sql_total = "SELECT COUNT(*) FROM recurso_gerenciar";
	$total_registros = $pdo->query($sql_total)->fetchColumn();

	// Busca recursos paginados
	$sql = "SELECT * FROM recurso_gerenciar 
			LEFT JOIN infracoes_gerenciar ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
			LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
			ORDER BY rec_id DESC
			LIMIT :offset, :limite";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
	$stmt->bindValue(':limite', $itens_por_pagina, PDO::PARAM_INT);
	$stmt->execute();
	$recursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	echo "<div class='titulo'>Lista de Recursos</div>";
	echo "<table border='1' width='100%'>";
	echo "<tr><th>ID</th><th>Assunto</th><th>Cliente</th><th>Status</th><th>Ações</th></tr>";
	foreach ($recursos as $recurso) {
		echo "<tr>
				<td>" . htmlspecialchars($recurso['rec_id']) . "</td>
				<td>" . htmlspecialchars($recurso['rec_assunto']) . "</td>
				<td>" . htmlspecialchars($recurso['cli_nome_razao']) . "</td>
				<td>" . htmlspecialchars($recurso['rec_status']) . "</td>
				<td>
					<a href='?pagina=recurso_gerenciar&rec_id=" . htmlspecialchars($recurso['rec_id']) . "'>Gerenciar</a>
				</td>
			  </tr>";
	}
	echo "</table>";

	// Paginação
	$total_paginas = ceil($total_registros / $itens_por_pagina);
	echo "<div class='paginacao'>";
	for ($i = 1; $i <= $total_paginas; $i++) {
		if ($i == $pagina_atual) {
			echo "<strong>$i</strong> ";
		} else {
			echo "<a href='?pagina=recurso_listar&p=$i'>$i</a> ";
		}
	}
	echo "</div>";
}

require_once '../mod_rodape/rodape.php';
?>