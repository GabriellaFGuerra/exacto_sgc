<?php
session_start();
include '../mod_includes/php/connect.php';
?>
<!DOCTYPE html
    PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>
        <?php echo $titulo ?? ''; ?>
    </title>
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
        <table width="100%">
            <tr>
                <td align="justify" valign="top">
                    <div class="quadro_home">
                        <div class="formtitulo">Últimas ações dos clientes</div>
                        <?php
						$sql = "SELECT * FROM notificacoes ORDER BY not_id DESC LIMIT 10";
						$stmt = $pdo->query($sql);
						$rows = $stmt->rowCount();
						if ($rows > 0) {
							echo "
							<table align='center' width='100%' border='0' cellspacing='0' cellpadding='5' class='bordatabela'>
								<tr>
									<td class='titulo_tabela'>Nome</td>
									<td class='titulo_tabela'>Obs</td>
								</tr>";
							$c = 0;
							foreach ($stmt as $row) {
								$c1 = $c % 2 == 0 ? 'linhaimpar' : 'linhapar';
								echo "
									<tr class='$c1'>
										<td>{$row['not_nome']}</td>
										<td>{$row['not_obs']}</td>
									</tr>";
								$c++;
							}
							echo "</table>";
						} else {
							echo "<br><br><br>Não há nenhum orçamento cadastrado.";
						}
						?>
                    </div>
                    <br>
                    <div class="quadro_home">
                        <div class="formtitulo">Orçamentos Pendentes</div>
                        <?php
						$sql = "SELECT * FROM orcamento_gerenciar 
							LEFT JOIN (cadastro_clientes 
								INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
							ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
							LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
							LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
							WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento) 
								AND ucl_usuario = :usuario_id AND sto_status = 1
							ORDER BY orc_data_cadastro DESC
							LIMIT 10";
						$stmt = $pdo->prepare($sql);
						$stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
						$rows = $stmt->rowCount();
						if ($rows > 0) {
							echo "
							<table align='center' width='100%' border='0' cellspacing='0' cellpadding='5' class='bordatabela'>
								<tr>
									<td class='titulo_tabela'>N° Orçamento</td>
									<td class='titulo_tabela'>Cliente</td>
									<td class='titulo_tabela'>Serviço</td>
									<td class='titulo_tabela' align='center'>Status</td>
									<td class='titulo_tabela' align='center'>Data Cadastro</td>
									<td class='titulo_tabela' align='center'>Imprimir</td>
								</tr>";
							$c = 0;
							foreach ($stmt as $row) {
								$orc_id = $row['orc_id'];
								$cli_nome_razao = $row['cli_nome_razao'];
								$tps_nome = $row['tps_nome'] ?: $row['orc_tipo_servico_cliente'] . "<br><span class='detalhe'>Digitado pelo cliente</span>";
								$sto_status = $row['sto_status'];
								$orc_data_cadastro = implode('/', array_reverse(explode('-', substr($row['orc_data_cadastro'], 0, 10))));
								$orc_hora_cadastro = substr($row['orc_data_cadastro'], 11, 5);

								$sto_status_n = match ($sto_status) {
									1 => "<span class='laranja'>Pendente</span>",
									2 => "<span class='azul'>Calculado</span>",
									3 => "<span class='verde'>Aprovado</span>",
									4 => "<span class='vermelho'>Reprovado</span>",
									default => "",
								};

								$c1 = $c % 2 == 0 ? 'linhaimpar' : 'linhapar';
								echo "
									<tr class='$c1'>
										<td>$orc_id</td>
										<td>$cli_nome_razao</td>
										<td>$tps_nome</td>
										<td align='center'>$sto_status_n</td>
										<td align='center'>$orc_data_cadastro<br><span class='detalhe'>$orc_hora_cadastro</span></td>
										<td align='center'>
											<img class='mouse' src='../imagens/icon-pdf.png' onclick=\"window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');\">
										</td>
									</tr>";
								$c++;
							}
							echo "</table>";
						} else {
							echo "<br><br><br>Não há nenhum orçamento cadastrado.";
						}
						?>
                    </div>
                    <br>
                    <div class="quadro_home">
                        <div class="formtitulo">Orçamentos calculados e ainda não aprovados</div>
                        <?php
						$sql = "SELECT * FROM orcamento_gerenciar 
							LEFT JOIN (cadastro_clientes 
								INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
							ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
							LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
							LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
							WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento) 
								AND ucl_usuario = :usuario_id AND sto_status = 2
							ORDER BY orc_data_cadastro DESC
							LIMIT 10";
						$stmt = $pdo->prepare($sql);
						$stmt->execute(['usuario_id' => $_SESSION['usuario_id']]);
						$rows = $stmt->rowCount();
						if ($rows > 0) {
							echo "
							<table align='center' width='100%' border='0' cellspacing='0' cellpadding='5' class='bordatabela'>
								<tr>
									<td class='titulo_tabela'>N° Orçamento</td>
									<td class='titulo_tabela'>Cliente</td>
									<td class='titulo_tabela'>Serviço</td>
									<td class='titulo_tabela' align='center'>Status</td>
									<td class='titulo_tabela' align='center'>Data Cadastro</td>
									<td class='titulo_tabela' align='center'>Imprimir</td>
								</tr>";
							$c = 0;
							foreach ($stmt as $row) {
								$orc_id = $row['orc_id'];
								$cli_nome_razao = $row['cli_nome_razao'];
								$tps_nome = $row['tps_nome'] ?: $row['orc_tipo_servico_cliente'] . "<br><span class='detalhe'>Digitado pelo cliente</span>";
								$sto_status = $row['sto_status'];
								$orc_data_cadastro = implode('/', array_reverse(explode('-', substr($row['orc_data_cadastro'], 0, 10))));
								$orc_hora_cadastro = substr($row['orc_data_cadastro'], 11, 5);

								$sto_status_n = match ($sto_status) {
									1 => "<span class='laranja'>Pendente</span>",
									2 => "<span class='azul'>Calculado</span>",
									3 => "<span class='verde'>Aprovado</span>",
									4 => "<span class='vermelho'>Reprovado</span>",
									default => "",
								};

								$c1 = $c % 2 == 0 ? 'linhaimpar' : 'linhapar';
								echo "
									<tr class='$c1'>
										<td>$orc_id</td>
										<td>$cli_nome_razao</td>
										<td>$tps_nome</td>
										<td align='center'>$sto_status_n</td>
										<td align='center'>$orc_data_cadastro<br><span class='detalhe'>$orc_hora_cadastro</span></td>
										<td align='center'>";
								if ($sto_status == 2 || $sto_status == 3 || $sto_status == 4) {
									echo "<img class='mouse' src='../imagens/icon-pdf.png' onclick=\"window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');\">";
								}
								echo "</td>
									</tr>";
								$c++;
							}
							echo "</table>";
						} else {
							echo "<br><br><br>Não há nenhum orçamento cadastrado.";
						}
						?>
                    </div>
                    <br>
                    <div class="quadro_home">
                        <div class="formtitulo">Documentos à vencer nos próximos 30 dias</div>
                        <?php
						$hoje = date('Y-m-d');
						$hoje30 = date('Y-m-d', strtotime('+30 days'));
						$sql = "SELECT * FROM documento_gerenciar 
							LEFT JOIN (cadastro_clientes 
								INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
							ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
							LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
							LEFT JOIN (orcamento_gerenciar 
								LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
							ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
							WHERE doc_data_vencimento BETWEEN :hoje AND :hoje30
								AND ucl_usuario = :usuario_id
							ORDER BY doc_data_cadastro DESC";
						$stmt = $pdo->prepare($sql);
						$stmt->execute([
							'hoje' => $hoje,
							'hoje30' => $hoje30,
							'usuario_id' => $_SESSION['usuario_id']
						]);
						$rows = $stmt->rowCount();
						if ($rows > 0) {
							echo "
							<table align='center' width='100%' border='0' cellspacing='0' cellpadding='5' class='bordatabela'>
								<tr>
									<td class='titulo_tabela'>Tipo de Doc</td>
									<td class='titulo_tabela'>Cliente</td>
									<td class='titulo_tabela'>Orçamento</td>
									<td class='titulo_tabela' align='center'>Data Emissão</td>
									<td class='titulo_tabela' align='center'>Periodicidade</td>
									<td class='titulo_tabela' align='center'>Data Vencimento</td>
									<td class='titulo_tabela' align='center'>Anexo</td>
								</tr>";
							$c = 0;
							foreach ($stmt as $row) {
								$doc_id = $row['doc_id'];
								$cli_nome_razao = $row['cli_nome_razao'];
								$orc_id = $row['orc_id'];
								$tps_nome = $row['tps_nome'];
								$tpd_nome = $row['tpd_nome'];
								$doc_anexo = $row['doc_anexo'];
								$doc_data_emissao = implode('/', array_reverse(explode('-', $row['doc_data_emissao'])));
								$doc_periodicidade = $row['doc_periodicidade'];
								$doc_data_vencimento = implode('/', array_reverse(explode('-', $row['doc_data_vencimento'])));
								$doc_periodicidade_n = match ($doc_periodicidade) {
									6 => 'Semestral',
									12 => 'Anual',
									24 => 'Bienal',
									36 => 'Trienal',
									48 => 'Quadrienal',
									60 => 'Quinquenal',
									default => '',
								};
								$doc_data_cadastro = implode('/', array_reverse(explode('-', substr($row['doc_data_cadastro'], 0, 10))));
								$doc_hora_cadastro = substr($row['doc_data_cadastro'], 11, 5);

								$c1 = $c % 2 == 0 ? 'linhaimpar' : 'linhapar';
								echo "
									<tr class='$c1'>
										<td>$tpd_nome</td>
										<td>$cli_nome_razao</td>
										<td>$orc_id ($tps_nome)</td>
										<td align='center'>$doc_data_emissao</td>
										<td align='center'>$doc_periodicidade_n</td>
										<td align='center'>$doc_data_vencimento</td>
										<td align='center'>";
								if (!empty($doc_anexo)) {
									echo "<a href='" . htmlspecialchars($doc_anexo) . "' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>";
								}
								echo "</td>
									</tr>";
								$c++;
							}
							echo "</table>";
						} else {
							echo "<br><br><br>Não há nenhum documento à vencer nos próximos 30 dias.";
						}
						?>
                    </div>
                    <br>
                    <div class="quadro_home">
                        <div class="formtitulo">Malotes com documentos à vencer</div>
                        <?php
						$hoje = date('Y-m-d');
						$hoje1 = date('Y-m-d', strtotime('+1 days'));
						$sql = "SELECT * FROM malote_itens 
							INNER JOIN (malote_gerenciar 
								LEFT JOIN (cadastro_clientes 
									INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id) 
								ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente)
							ON malote_gerenciar.mal_id = malote_itens.mai_malote
							WHERE mai_data_vencimento BETWEEN :hoje AND :hoje1 AND mai_baixado IS NULL
								AND ucl_usuario = :usuario_id
							GROUP BY mai_malote
							ORDER BY mal_data_cadastro DESC";
						$stmt = $pdo->prepare($sql);
						$stmt->execute([
							'hoje' => $hoje,
							'hoje1' => $hoje1,
							'usuario_id' => $_SESSION['usuario_id']
						]);
						$rows = $stmt->rowCount();
						if ($rows > 0) {
							echo "
							<table align='center' width='100%' border='0' cellspacing='0' cellpadding='5' class='bordatabela'>
								<tr>
									<td class='titulo_tabela'>N° Malote</td>
									<td class='titulo_tabela'>N° Lacre</td>
									<td class='titulo_tabela'>Cliente</td>
									<td class='titulo_tabela'>Observação</td>
									<td class='titulo_tabela' align='center'>Data Cadastro</td>
								</tr>";
							$c = 0;
							foreach ($stmt as $row) {
								$mal_id = $row['mal_id'];
								$mal_lacre = $row['mal_lacre'];
								$cli_nome_razao = $row['cli_nome_razao'];
								$mal_observacoes = $row['mal_observacoes'];
								$mal_data_cadastro = implode('/', array_reverse(explode('-', substr($row['mal_data_cadastro'], 0, 10))));
								$mal_hora_cadastro = substr($row['mal_data_cadastro'], 11, 5);

								$c1 = $c % 2 == 0 ? 'linhaimpar' : 'linhapar';
								echo "
									<tr class='$c1'>
										<td><a href='malote_gerenciar.php?pagina=exibir_malote_gerenciar&mal_id=$mal_id$autenticacao'><b>$mal_id</b></a></td>
										<td>$mal_lacre</td>
										<td>$cli_nome_razao</td>
										<td>$mal_observacoes</td>
										<td align='center'>$mal_data_cadastro<br><span class='detalhe'>$mal_hora_cadastro</span></td>
									</tr>";
								$c++;
							}
							echo "</table>";
						} else {
							echo "<br><br><br>Não há nenhum malote com documento à vencer.";
						}
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