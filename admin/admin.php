<?php
session_start();
include '../mod_includes/php/connect.php';

function renderTable($headers, $rows, $rowRenderer)
{
	if (empty($rows)) {
		echo "<br><br><br>Não há registros.";
		return;
	}
	echo "<table align='center' width='100%' border='0' cellspacing='0' cellpadding='5' class='bordatabela'>";
	echo "<tr>";
	foreach ($headers as $header) {
		echo "<td class='titulo_tabela'{$header['extra']}>{$header['label']}</td>";
	}
	echo "</tr>";
	$c = 0;
	foreach ($rows as $row) {
		$class = $c % 2 == 0 ? 'linhaimpar' : 'linhapar';
		echo $rowRenderer($row, $class);
		$c++;
	}
	echo "</table>";
}

function fetchRows($pdo, $sql, $params = [])
{
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatDate($date, $withTime = false)
{
	if (!$date)
		return '';
	$datePart = implode('/', array_reverse(explode('-', substr($date, 0, 10))));
	if ($withTime) {
		$timePart = substr($date, 11, 5);
		return "$datePart<br><span class='detalhe'>$timePart</span>";
	}
	return $datePart;
}

function getStatusLabel($status)
{
	return match ($status) {
		1 => "<span class='laranja'>Pendente</span>",
		2 => "<span class='azul'>Calculado</span>",
		3 => "<span class='verde'>Aprovado</span>",
		4 => "<span class='vermelho'>Reprovado</span>",
		default => "",
	};
}

function getPeriodicidadeLabel($periodicidade)
{
	return match ($periodicidade) {
		6 => 'Semestral',
		12 => 'Anual',
		24 => 'Bienal',
		36 => 'Trienal',
		48 => 'Quadrienal',
		60 => 'Quinquenal',
		default => '',
	};
}

$titulo ??= '';
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="author" content="MogiComp">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
    <?php
	include '../mod_includes/php/funcoes-jquery.php';
	require_once '../mod_includes/php/verificalogin.php';
	include '../mod_topo/topo.php';
	?>

    <div class="centro">
        <div class="titulo">Bem vindo ao SGO - Sistema de Gerenciamento de Orçamentos</div>
        <table width="100%"></table>
        </table>
        <tr>
            <td align="justify" valign="top">
                <!-- Últimas ações dos clientes -->
                <div class="quadro_home">
                    <div class="formtitulo">Últimas ações dos clientes</div>
                    <?php
					$notificacoes = fetchRows($pdo, "SELECT * FROM notificacoes ORDER BY not_id DESC LIMIT 10");
					renderTable(
						[
							['label' => 'Nome', 'extra' => ''],
							['label' => 'Obs', 'extra' => '']
						],
						$notificacoes,
						fn($row, $class) => "
								<tr class='$class'>
									<td>{$row['not_nome']}</td>
									<td>{$row['not_obs']}</td>
								</tr>"
					);
					?>
                </div>
                <br>
                <!-- Orçamentos Pendentes -->
                <div class="quadro_home">
                    <div class="formtitulo">Orçamentos Pendentes</div>
                    <?php
					$orcamentosPendentes = fetchRows(
						$pdo,
						"SELECT * FROM orcamento_gerenciar 
							LEFT JOIN (cadastro_clientes 
								INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
							ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
							LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
							LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
							WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento) 
								AND ucl_usuario = :usuario_id AND sto_status = 1
							ORDER BY orc_data_cadastro DESC
							LIMIT 10",
						['usuario_id' => $_SESSION['usuario_id']]
					);
					renderTable(
						[
							['label' => 'N° Orçamento', 'extra' => ''],
							['label' => 'Cliente', 'extra' => ''],
							['label' => 'Serviço', 'extra' => ''],
							['label' => 'Status', 'extra' => " align='center'"],
							['label' => 'Data Cadastro', 'extra' => " align='center'"],
							['label' => 'Imprimir', 'extra' => " align='center'"]
						],
						$orcamentosPendentes,
						function ($row, $class) {
							$orc_id = $row['orc_id'];
							$cli_nome_razao = $row['cli_nome_razao'];
							$tps_nome = $row['tps_nome'] ?: $row['orc_tipo_servico_cliente'] . "<br><span class='detalhe'>Digitado pelo cliente</span>";
							$sto_status_n = getStatusLabel($row['sto_status']);
							$dataCadastro = formatDate($row['orc_data_cadastro'], true);
							$autenticacao = ''; // Defina se necessário
							return "
								<tr class='$class'>
									<td>$orc_id</td>
									<td>$cli_nome_razao</td>
									<td>$tps_nome</td>
									<td align='center'>$sto_status_n</td>
									<td align='center'>$dataCadastro</td>
									<td align='center'>
										<img class='mouse' src='../imagens/icon-pdf.png' onclick=\"window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');\">
									</td>
								</tr>";
						}
					);
					?>
                </div>
                <br>
                <!-- Orçamentos calculados e ainda não aprovados -->
                <div class="quadro_home">
                    <div class="formtitulo">Orçamentos calculados e ainda não aprovados</div>
                    <?php
					$orcamentosCalculados = fetchRows(
						$pdo,
						"SELECT * FROM orcamento_gerenciar 
							LEFT JOIN (cadastro_clientes 
								INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
							ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
							LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
							LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
							WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento) 
								AND ucl_usuario = :usuario_id AND sto_status = 2
							ORDER BY orc_data_cadastro DESC
							LIMIT 10",
						['usuario_id' => $_SESSION['usuario_id']]
					);
					renderTable(
						[
							['label' => 'N° Orçamento', 'extra' => ''],
							['label' => 'Cliente', 'extra' => ''],
							['label' => 'Serviço', 'extra' => ''],
							['label' => 'Status', 'extra' => " align='center'"],
							['label' => 'Data Cadastro', 'extra' => " align='center'"],
							['label' => 'Imprimir', 'extra' => " align='center'"]
						],
						$orcamentosCalculados,
						function ($row, $class) {
							$orc_id = $row['orc_id'];
							$cli_nome_razao = $row['cli_nome_razao'];
							$tps_nome = $row['tps_nome'] ?: $row['orc_tipo_servico_cliente'] . "<br><span class='detalhe'>Digitado pelo cliente</span>";
							$sto_status = $row['sto_status'];
							$sto_status_n = getStatusLabel($sto_status);
							$dataCadastro = formatDate($row['orc_data_cadastro'], true);
							$autenticacao = ''; // Defina se necessário
							$imprimir = ($sto_status == 2 || $sto_status == 3 || $sto_status == 4)
								? "<img class='mouse' src='../imagens/icon-pdf.png' onclick=\"window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');\">"
								: '';
							return "
								<tr class='$class'>
									<td>$orc_id</td>
									<td>$cli_nome_razao</td>
									<td>$tps_nome</td>
									<td align='center'>$sto_status_n</td>
									<td align='center'>$dataCadastro</td>
									<td align='center'>$imprimir</td>
								</tr>";
						}
					);
					?>
                </div>
                <br>
                <!-- Documentos à vencer nos próximos 30 dias -->
                <div class="quadro_home">
                    <div class="formtitulo">Documentos à vencer nos próximos 30 dias</div>
                    <?php
					$hoje = date('Y-m-d');
					$hoje30 = date('Y-m-d', strtotime('+30 days'));
					$documentosVencer = fetchRows(
						$pdo,
						"SELECT * FROM documento_gerenciar 
							LEFT JOIN (cadastro_clientes 
								INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
							ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
							LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
							LEFT JOIN (orcamento_gerenciar 
								LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
							ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
							WHERE doc_data_vencimento BETWEEN :hoje AND :hoje30
								AND ucl_usuario = :usuario_id
							ORDER BY doc_data_cadastro DESC",
						[
							'hoje' => $hoje,
							'hoje30' => $hoje30,
							'usuario_id' => $_SESSION['usuario_id']
						]
					);
					renderTable(
						[
							['label' => 'Tipo de Doc', 'extra' => ''],
							['label' => 'Cliente', 'extra' => ''],
							['label' => 'Orçamento', 'extra' => ''],
							['label' => 'Data Emissão', 'extra' => " align='center'"],
							['label' => 'Periodicidade', 'extra' => " align='center'"],
							['label' => 'Data Vencimento', 'extra' => " align='center'"],
							['label' => 'Anexo', 'extra' => " align='center'"]
						],
						$documentosVencer,
						function ($row, $class) {
							$orc_id = $row['orc_id'];
							$tps_nome = $row['tps_nome'];
							$tpd_nome = $row['tpd_nome'];
							$doc_anexo = $row['doc_anexo'];
							$doc_periodicidade_n = getPeriodicidadeLabel($row['doc_periodicidade']);
							$doc_data_emissao = formatDate($row['doc_data_emissao']);
							$doc_data_vencimento = formatDate($row['doc_data_vencimento']);
							$cli_nome_razao = $row['cli_nome_razao'];
							$anexo = !empty($doc_anexo)
								? "<a href='" . htmlspecialchars($doc_anexo) . "' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>"
								: '';
							return "
								<tr class='$class'>
									<td>$tpd_nome</td>
									<td>$cli_nome_razao</td>
									<td>$orc_id ($tps_nome)</td>
									<td align='center'>$doc_data_emissao</td>
									<td align='center'>$doc_periodicidade_n</td>
									<td align='center'>$doc_data_vencimento</td>
									<td align='center'>$anexo</td>
								</tr>";
						}
					);
					?>
                </div>
                <br>
                <!-- Malotes com documentos à vencer -->
                <div class="quadro_home">
                    <div class="formtitulo">Malotes com documentos à vencer</div>
                    <?php
					$hoje1 = date('Y-m-d', strtotime('+1 days'));
					$malotesVencer = fetchRows(
						$pdo,
						"SELECT * FROM malote_itens 
							INNER JOIN (malote_gerenciar 
								LEFT JOIN (cadastro_clientes 
									INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id) 
								ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente)
							ON malote_gerenciar.mal_id = malote_itens.mai_malote
							WHERE mai_data_vencimento BETWEEN :hoje AND :hoje1 AND mai_baixado IS NULL
								AND ucl_usuario = :usuario_id
							GROUP BY mai_malote
							ORDER BY mal_data_cadastro DESC",
						[
							'hoje' => $hoje,
							'hoje1' => $hoje1,
							'usuario_id' => $_SESSION['usuario_id']
						]
					);
					renderTable(
						[
							['label' => 'N° Malote', 'extra' => ''],
							['label' => 'N° Lacre', 'extra' => ''],
							['label' => 'Cliente', 'extra' => ''],
							['label' => 'Observação', 'extra' => ''],
							['label' => 'Data Cadastro', 'extra' => " align='center'"]
						],
						$malotesVencer,
						function ($row, $class) {
							$mal_id = $row['mal_id'];
							$mal_lacre = $row['mal_lacre'];
							$cli_nome_razao = $row['cli_nome_razao'];
							$mal_observacoes = $row['mal_observacoes'];
							$mal_data_cadastro = formatDate($row['mal_data_cadastro'], true);
							$autenticacao = ''; // Defina se necessário
							return "
								<tr class='$class'>
									<td><a href='malote_gerenciar.php?pagina=exibir_malote_gerenciar&mal_id=$mal_id$autenticacao'><b>$mal_id</b></a></td>
									<td>$mal_lacre</td>
									<td>$cli_nome_razao</td>
									<td>$mal_observacoes</td>
									<td align='center'>$mal_data_cadastro</td>
								</tr>";
						}
					);
					?>
                </div>
            </td>
        </tr>
        </table>
        <div class="titulo"></div>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>