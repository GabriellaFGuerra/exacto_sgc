<?php
session_start();
$pagina_link = 'documento_gerenciar';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Funções utilitárias
function formatDateToDb($date)
{
	if (!$date)
		return null;
	if (strpos($date, '/') !== false) {
		$parts = explode('/', $date);
		return (count($parts) == 3) ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : $date;
	}
	return $date;
}
function formatDateToBr($date)
{
	if (!$date)
		return '';
	$parts = explode('-', $date);
	return (count($parts) == 3) ? "{$parts[2]}/{$parts[1]}/{$parts[0]}" : $date;
}
function getPeriodicidadeNome($valor)
{
	$nomes = [
		6 => "Semestral",
		12 => "Anual",
		24 => "Bienal",
		36 => "Trienal",
		48 => "Quadrienal",
		60 => "Quinquenal"
	];
	return $nomes[$valor] ?? '';
}
function renderPagination($total, $porPagina, $paginaAtual, $queryStringBase)
{
	$totalPaginas = ceil($total / $porPagina);
	if ($totalPaginas <= 1)
		return;
	echo "<div class='paginacao' style='text-align:center; margin:20px 0;'>";
	for ($i = 1; $i <= $totalPaginas; $i++) {
		if ($i == $paginaAtual) {
			echo "<span style='font-weight:bold; color:#000;'>$i</span> ";
		} else {
			$queryString = "{$queryStringBase}&pag={$i}";
			echo "<a href='documento_gerenciar.php?$queryString'>$i</a> ";
		}
	}
	echo "</div>";
}

// Variáveis de controle
$titulo = 'Documentos - Gerenciar';
$pagina = $_GET['pagina'] ?? '';
$action = $_GET['action'] ?? '';
$autenticacao = $_GET['autenticacao'] ?? '';
$pag = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
$num_por_pagina = 10;
$primeiro_registro = ($pag - 1) * $num_por_pagina;
$page = "Documentos &raquo; <a href='documento_gerenciar.php?pagina=documento_gerenciar$autenticacao'>Gerenciar</a>";

// CRUD
if ($action === "adicionar") {
	$doc_cliente = $_POST['doc_cliente'] ?? null;
	$doc_orcamento = $_POST['doc_orcamento'] ?? null;
	$doc_tipo = $_POST['doc_tipo'] ?? null;
	$doc_data_emissao = formatDateToDb($_POST['doc_data_emissao'] ?? '');
	$doc_periodicidade = $_POST['doc_periodicidade'] ?? null;
	$doc_observacoes = $_POST['doc_observacoes'] ?? null;

	$doc_data_vencimento = null;
	if ($doc_data_emissao && $doc_periodicidade) {
		$date = new DateTime($doc_data_emissao);
		switch ($doc_periodicidade) {
			case 6:
				$date->modify('+6 months');
				break;
			case 12:
				$date->modify('+1 year');
				break;
			case 24:
				$date->modify('+2 years');
				break;
			case 36:
				$date->modify('+3 years');
				break;
			case 48:
				$date->modify('+4 years');
				break;
			case 60:
				$date->modify('+5 years');
				break;
		}
		$doc_data_vencimento = $date->format('Y-m-d');
	}

	$stmt = $pdo->prepare(
		"INSERT INTO documento_gerenciar (
            doc_cliente, doc_orcamento, doc_tipo, doc_data_emissao, doc_periodicidade, doc_data_vencimento, doc_observacoes
        ) VALUES (
            :doc_cliente, :doc_orcamento, :doc_tipo, :doc_data_emissao, :doc_periodicidade, :doc_data_vencimento, :doc_observacoes
        )"
	);
	$stmt->bindValue(':doc_cliente', $doc_cliente);
	$stmt->bindValue(':doc_orcamento', !empty($doc_orcamento) ? $doc_orcamento : null, PDO::PARAM_INT);
	$stmt->bindValue(':doc_tipo', $doc_tipo);
	$stmt->bindValue(':doc_data_emissao', $doc_data_emissao);
	$stmt->bindValue(':doc_periodicidade', $doc_periodicidade);
	$stmt->bindValue(':doc_data_vencimento', $doc_data_vencimento);
	$stmt->bindValue(':doc_observacoes', $doc_observacoes);

	if ($stmt->execute()) {
		$ultimo_id = $pdo->lastInsertId();
		$caminho = "../admin/docs/$ultimo_id/";
		if (!file_exists($caminho))
			mkdir($caminho, 0755, true);
		$doc_anexo = $_FILES['doc_anexo']['name'] ?? [];
		$tmp_anexo = $_FILES['doc_anexo']['tmp_name'] ?? [];
		if (!is_array($doc_anexo))
			$doc_anexo = [$doc_anexo];
		if (!is_array($tmp_anexo))
			$tmp_anexo = [$tmp_anexo];
		foreach ($doc_anexo as $k => $value) {
			if ($doc_anexo[$k] != '') {
				$extensao = pathinfo($doc_anexo[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho . md5(mt_rand(1, 10000) . $doc_anexo[$k]) . '.' . $extensao;
				move_uploaded_file($tmp_anexo[$k], $arquivo);
				$stmt2 = $pdo->prepare("UPDATE documento_gerenciar SET doc_anexo = :arquivo WHERE doc_id = :id");
				$stmt2->execute([':arquivo' => $arquivo, ':id' => $ultimo_id]);
			}
		}
		echo "<script>alert('Cadastro efetuado com sucesso.'); window.location.href='documento_gerenciar.php?pagina=documento_gerenciar';</script>";
		exit;
	} else {
		$errorInfo = $stmt->errorInfo();
		echo "<script>alert('Erro ao efetuar cadastro: {$errorInfo[2]}'); window.history.back();</script>";
		exit;
	}
}

if ($action === "editar") {
	$doc_id = $_POST['doc_id'] ?? null;
	$doc_cliente = $_POST['doc_cliente'] ?? null;
	$doc_orcamento = $_POST['doc_orcamento'] ?? null;
	$doc_tipo = $_POST['doc_tipo'] ?? null;
	$doc_data_emissao = formatDateToDb($_POST['doc_data_emissao'] ?? '');
	$doc_periodicidade = $_POST['doc_periodicidade'] ?? null;
	$doc_observacoes = $_POST['doc_observacoes'] ?? null;

	$doc_data_vencimento = null;
	if ($doc_data_emissao && $doc_periodicidade) {
		$date = new DateTime($doc_data_emissao);
		switch ($doc_periodicidade) {
			case 6:
				$date->modify('+6 months');
				break;
			case 12:
				$date->modify('+1 year');
				break;
			case 24:
				$date->modify('+2 years');
				break;
			case 36:
				$date->modify('+3 years');
				break;
			case 48:
				$date->modify('+4 years');
				break;
			case 60:
				$date->modify('+5 years');
				break;
		}
		$doc_data_vencimento = $date->format('Y-m-d');
	}

	$stmt = $pdo->prepare(
		"UPDATE documento_gerenciar SET
			doc_cliente = :doc_cliente,
			doc_orcamento = :doc_orcamento,
			doc_tipo = :doc_tipo,
			doc_data_emissao = :doc_data_emissao,
			doc_periodicidade = :doc_periodicidade,
			doc_data_vencimento = :doc_data_vencimento,
			doc_observacoes = :doc_observacoes
		WHERE doc_id = :doc_id"
	);
	$stmt->bindValue(':doc_cliente', $doc_cliente);
	$stmt->bindValue(':doc_orcamento', !empty($doc_orcamento) ? $doc_orcamento : null, PDO::PARAM_INT);
	$stmt->bindValue(':doc_tipo', $doc_tipo);
	$stmt->bindValue(':doc_data_emissao', $doc_data_emissao);
	$stmt->bindValue(':doc_periodicidade', $doc_periodicidade);
	$stmt->bindValue(':doc_data_vencimento', $doc_data_vencimento);
	$stmt->bindValue(':doc_observacoes', $doc_observacoes);
	$stmt->bindValue(':doc_id', $doc_id);

	if ($stmt->execute()) {
		// Anexo (opcional)
		$caminho = "../admin/docs/$doc_id/";
		if (!file_exists($caminho))
			mkdir($caminho, 0755, true);
		$doc_anexo = $_FILES['doc_anexo']['name'] ?? [];
		$tmp_anexo = $_FILES['doc_anexo']['tmp_name'] ?? [];
		if (!is_array($doc_anexo))
			$doc_anexo = [$doc_anexo];
		if (!is_array($tmp_anexo))
			$tmp_anexo = [$tmp_anexo];
		foreach ($doc_anexo as $k => $value) {
			if ($doc_anexo[$k] != '') {
				$extensao = pathinfo($doc_anexo[$k], PATHINFO_EXTENSION);
				$arquivo = $caminho . md5(mt_rand(1, 10000) . $doc_anexo[$k]) . '.' . $extensao;
				move_uploaded_file($tmp_anexo[$k], $arquivo);
				$stmt2 = $pdo->prepare("UPDATE documento_gerenciar SET doc_anexo = :arquivo WHERE doc_id = :id");
				$stmt2->execute([':arquivo' => $arquivo, ':id' => $doc_id]);
			}
		}
		echo "<script>alert('Documento atualizado com sucesso.'); window.location.href='documento_gerenciar.php?pagina=documento_gerenciar';</script>";
		exit;
	} else {
		$errorInfo = $stmt->errorInfo();
		echo "<script>alert('Erro ao atualizar documento: {$errorInfo[2]}'); window.history.back();</script>";
		exit;
	}
}

if ($action === 'excluir') {
	$doc_id = $_GET['doc_id'] ?? null;
	$stmt = $pdo->prepare("DELETE FROM documento_gerenciar WHERE doc_id = :doc_id");
	if ($stmt->execute([':doc_id' => $doc_id])) {
		echo "<script>alert('Exclusão realizada com sucesso'); window.location.href='documento_gerenciar.php?pagina=documento_gerenciar';</script>";
		exit;
	} else {
		echo "<script>alert('Este item não pode ser excluído pois está relacionado com alguma tabela.'); window.history.back();</script>";
		exit;
	}
}

// Filtros e paginação
$fil_nome = $_REQUEST['fil_nome'] ?? '';
$fil_doc_tipo = $_REQUEST['fil_doc_tipo'] ?? '';
$fil_data_inicio = formatDateToDb($_REQUEST['fil_data_inicio'] ?? '');
$fil_data_fim = formatDateToDb($_REQUEST['fil_data_fim'] ?? '');
$fil_vencido = $_REQUEST['fil_vencido'] ?? '';

$nome_query = $fil_nome ? "cadastro_clientes.cli_nome_razao LIKE :fil_nome" : "1=1";
$tipo_doc_query = $fil_doc_tipo ? "doc_tipo = :fil_doc_tipo" : "1=1";
$data_query = "1=1";
if ($fil_data_inicio && $fil_data_fim) {
	$data_query = "doc_data_vencimento BETWEEN :fil_data_inicio AND :fil_data_fim";
} elseif ($fil_data_inicio) {
	$data_query = "doc_data_vencimento >= :fil_data_inicio";
} elseif ($fil_data_fim) {
	$data_query = "doc_data_vencimento <= :fil_data_fim";
}
$vencido_query = "1=1";
if ($fil_vencido === 'Sim') {
	$vencido_query = "doc_data_vencimento <= :hoje";
} elseif ($fil_vencido === 'Não') {
	$vencido_query = "doc_data_vencimento > :hoje";
}

// CORREÇÃO: cli_deletado = 0 (não deletado)
$where = "cli_status = 1 and cli_deletado = 0 AND $nome_query AND $tipo_doc_query AND $data_query AND $vencido_query";
$sql = "SELECT documento_gerenciar.*, cadastro_clientes.cli_nome_razao, cadastro_tipos_docs.tpd_nome, orcamento_gerenciar.orc_id, cadastro_tipos_servicos.tps_nome
    FROM documento_gerenciar
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
    LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
    LEFT JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
    LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
    WHERE $where
    ORDER BY doc_data_cadastro DESC
    LIMIT $primeiro_registro, $num_por_pagina";

$params = [];
if ($fil_nome)
	$params[':fil_nome'] = "%$fil_nome%";
if ($fil_doc_tipo)
	$params[':fil_doc_tipo'] = $fil_doc_tipo;
if ($fil_data_inicio && $fil_data_fim) {
	$params[':fil_data_inicio'] = $fil_data_inicio;
	$params[':fil_data_fim'] = "{$fil_data_fim} 23:59:59";
} elseif ($fil_data_inicio) {
	$params[':fil_data_inicio'] = $fil_data_inicio;
} elseif ($fil_data_fim) {
	$params[':fil_data_fim'] = "{$fil_data_fim} 23:59:59";
}
if ($fil_vencido === 'Sim' || $fil_vencido === 'Não') {
	$params[':hoje'] = date('Y-m-d');
}

$query = $pdo->prepare($sql);
$query->execute($params);
$rows = $query->rowCount();

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php
	include '../mod_includes/php/funcoes-jquery.php';
	include '../mod_topo/topo.php';
	?>

    <?php if ($pagina == "documento_gerenciar"): ?>
    <div class='centro'>
        <div class='titulo'> <?= $page ?> </div>
        <div id='botoes'><input value='Novo Documento' type='button'
                onclick="window.location.href='documento_gerenciar.php?pagina=adicionar_documento_gerenciar<?= $autenticacao ?>';" />
        </div>
        <div class='filtro'>
            <form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post'
                action='documento_gerenciar.php?pagina=documento_gerenciar<?= $autenticacao ?>'>
                <input name='fil_nome' id='fil_nome' value='<?= htmlspecialchars($fil_nome) ?>' placeholder='Cliente'>
                <select name='fil_doc_tipo' id='fil_doc_tipo'>
                    <option value=''>Tipo de documento</option>
                    <?php
						$sql_tpd = "SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC";
						foreach ($pdo->query($sql_tpd) as $row_tpd) {
							$selected = ($fil_doc_tipo == $row_tpd['tpd_id']) ? "selected" : "";
							echo "<option value='{$row_tpd['tpd_id']}' $selected>{$row_tpd['tpd_nome']}</option>";
						}
						?>
                </select>
                <input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Venc. Início'
                    style='width:150px;' value='<?= htmlspecialchars($_REQUEST['fil_data_inicio'] ?? '') ?>'
                    onkeypress='return mascaraData(this,event);'>
                <input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Venc. Fim'
                    style='width:150px;' value='<?= htmlspecialchars($_REQUEST['fil_data_fim'] ?? '') ?>'
                    onkeypress='return mascaraData(this,event);'>
                <select name='fil_vencido' id='fil_vencido' style='width:150px;'>
                    <option value=''> Exibir vencidos?</option>
                    <option value='Sim'>Sim</option>
                    <option value='Não'>Não</option>
                    <option value=''>Todos</option>
                </select>
                <input type='submit' value='Filtrar'>
            </form>
        </div>
        <?php if ($rows > 0): ?>
        <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
            <tr>
                <td class='titulo_tabela'>Tipo de Doc</td>
                <td class='titulo_tabela'>Cliente</td>
                <td class='titulo_tabela'>Orçamento</td>
                <td class='titulo_tabela' align='center'>Data Emissão</td>
                <td class='titulo_tabela' align='center'>Periodicidade</td>
                <td class='titulo_tabela' align='center'>Data Vencimento</td>
                <td class='titulo_tabela' align='center'>Data Cadastro</td>
                <td class='titulo_tabela' align='center'>Anexo</td>
                <td class='titulo_tabela' align='center'>Gerenciar</td>
            </tr>
            <?php $c = 0;
					foreach ($query as $row):
						$doc_id = $row['doc_id'];
						$cli_nome_razao = htmlspecialchars($row['cli_nome_razao']);
						$orc_id = htmlspecialchars($row['orc_id']);
						$tps_nome = htmlspecialchars($row['tps_nome']);
						$tpd_nome = htmlspecialchars($row['tpd_nome']);
						$doc_anexo = $row['doc_anexo'];
						$doc_data_emissao = formatDateToBr($row['doc_data_emissao']);
						$doc_periodicidade = $row['doc_periodicidade'];
						$doc_data_vencimento = formatDateToBr($row['doc_data_vencimento']);
						$doc_data_cadastro = formatDateToBr(substr($row['doc_data_cadastro'], 0, 10));
						$doc_hora_cadastro = substr($row['doc_data_cadastro'], 11, 5);
						$doc_periodicidade_n = getPeriodicidadeNome($doc_periodicidade);
						$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
						$c++;
						?>
            <tr class='<?= $c1 ?>'>
                <td><?= $tpd_nome ?></td>
                <td><?= $cli_nome_razao ?></td>
                <td><?= $orc_id ?> (<?= $tps_nome ?>)</td>
                <td align="center"><?= $doc_data_emissao ?></td>
                <td align="center"><?= $doc_periodicidade_n ?></td>
                <td align="center"><?= $doc_data_vencimento ?></td>
                <td align="center"><?= $doc_data_cadastro ?><br><span class='detalhe'><?= $doc_hora_cadastro ?></span>
                </td>
                <td align="center">
                    <?php if ($doc_anexo): ?>
                    <a href="<?= htmlspecialchars($doc_anexo) ?>" target="_blank"><img src="../imagens/icon-pdf.png"
                            valign="middle"></a>
                    <?php endif; ?>
                </td>
                <td align="center">
                    <a href="documento_gerenciar.php?pagina=editar_documento_gerenciar&doc_id=<?= $doc_id . $autenticacao ?>"
                        title="Editar">
                        <img src="../imagens/icon-editar.png" border="0" />
                    </a>
                    <a href="javascript:void(0);"
                        onclick="if(confirm('Deseja realmente excluir o documento <?= $cli_nome_razao ?>?')){window.location.href='documento_gerenciar.php?pagina=documento_gerenciar&action=excluir&doc_id=<?= $doc_id . $autenticacao ?>';}"
                        title="Excluir">
                        <img src="../imagens/icon-excluir.png" border="0" />
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
				// Paginação
				$sql_total = "SELECT COUNT(*) FROM documento_gerenciar
            LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
            LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
            LEFT JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
            LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
            WHERE $where";
				$stmt_total = $pdo->prepare($sql_total);
				$stmt_total->execute($params);
				$total_registros = $stmt_total->fetchColumn();

				$queryStringBase = http_build_query(array_merge($_GET, $_POST));
				$queryStringBase = preg_replace('/(&|\?)pag=\d+/', '', $queryStringBase);

				renderPagination($total_registros, $num_por_pagina, $pag, $queryStringBase);
				?>
        <?php else: ?>
        <br><br><br>Não há nenhum documento cadastrado.
        <?php endif; ?>
        <div class='titulo'></div>
    </div>
    <?php elseif ($pagina == "adicionar_documento_gerenciar"): ?>
    <div class='centro'>
        <div class='titulo'>Adicionar Documento</div>
        <form method="post" enctype="multipart/form-data"
            action="documento_gerenciar.php?pagina=documento_gerenciar&action=adicionar">
            <table class="formulario" align="center">
                <tr>
                    <td>Cliente:</td>
                    <td>
                        <select name="doc_cliente" required>
                            <option value="">Selecione</option>
                            <?php
								$sql = "SELECT cli_id, cli_nome_razao FROM cadastro_clientes WHERE cli_status = 1 AND cli_deletado = 0 ORDER BY cli_nome_razao ASC";
								foreach ($pdo->query($sql) as $row) {
									echo "<option value='{$row['cli_id']}'>{$row['cli_nome_razao']}</option>";
								}
								?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Orçamento:</td>
                    <td>
                        <select name="doc_orcamento">
                            <option value="">Selecione</option>
                            <?php
								$sql = "SELECT orc_id FROM orcamento_gerenciar ORDER BY orc_id DESC";
								foreach ($pdo->query($sql) as $row) {
									echo "<option value='{$row['orc_id']}'>{$row['orc_id']}</option>";
								}
								?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Tipo de Documento:</td>
                    <td>
                        <select name="doc_tipo" required>
                            <option value="">Selecione</option>
                            <?php
								$sql = "SELECT tpd_id, tpd_nome FROM cadastro_tipos_docs ORDER BY tpd_nome ASC";
								foreach ($pdo->query($sql) as $row) {
									echo "<option value='{$row['tpd_id']}'>{$row['tpd_nome']}</option>";
								}
								?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Data de Emissão:</td>
                    <td>
                        <input type="date" name="doc_data_emissao" maxlength="10" required />
                    </td>
                </tr>
                <tr>
                    <td>Periodicidade:</td>
                    <td>
                        <select name="doc_periodicidade" required>
                            <option value="">Selecione</option>
                            <option value="6">Semestral</option>
                            <option value="12">Anual</option>
                            <option value="24">Bienal</option>
                            <option value="36">Trienal</option>
                            <option value="48">Quadrienal</option>
                            <option value="60">Quinquenal</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Observações:</td>
                    <td>
                        <textarea name="doc_observacoes" rows="3"></textarea>
                    </td>
                </tr>
                <tr>
                    <td>Anexo:</td>
                    <td>
                        <input type="file" name="doc_anexo[]" multiple />
                    </td>
                </tr>
                <tr>
                    <td colspan="2" align="center">
                        <input type="submit" value="Salvar" />
                        <input type="button" value="Cancelar"
                            onclick="window.location.href='documento_gerenciar.php?pagina=documento_gerenciar';" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
    <?php endif; ?>

    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>