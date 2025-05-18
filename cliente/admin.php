<?php
session_start();
include('../mod_includes/php/connect.php');
?>
<!DOCTYPE html>
<html lang="pt">

<head>
	<title><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></title>
	<meta name="author" content="MogiComp">
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
	<?php
	include('../mod_includes/php/funcoes-jquery.php');
	require_once('../mod_includes/php/verificalogincliente.php');
	include("../mod_topo_cliente/topo.php");
	?>

	<div class='centro'>
		<div class='titulo'>Bem-vindo ao SGO - Sistema de Gerenciamento de Orçamentos</div>
		<table width='100%'>
			<tr>
				<td align='justify' valign='top'>
					<div class='quadro_home'>
						<div class='formtitulo'>Últimos orçamentos realizados</div>

						<?php
						$sql = "SELECT * FROM orcamento_gerenciar 
                            LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
                            LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
                            LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
                            WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento)
                            AND orc_cliente = :cliente_id
                            ORDER BY orc_data_cadastro DESC
                            LIMIT 10";

						$stmt = $pdo->prepare($sql);
						$stmt->bindParam(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
						$stmt->execute();
						$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

						if ($rows) {
							echo "<table class='bordatabela' width='100%' border='0' cellspacing='0' cellpadding='5'>
                                <tr>
                                    <td class='titulo_tabela'>N. Orçamento</td>
                                    <td class='titulo_tabela' width='150'>Tipo de Serviço</td>
                                    <td class='titulo_tabela'>Observações</td>
                                    <td class='titulo_tabela' align='center'>Data de Abertura</td>
                                    <td class='titulo_tabela' align='center'>Status</td>
                                    <td class='titulo_tabela' align='center'>Visualizar</td>
                                </tr>";

							$c = 0;
							foreach ($rows as $row) {
								$orc_id = htmlspecialchars($row['orc_id']);
								$tps_nome = htmlspecialchars($row['tps_nome'] ?? $row['orc_tipo_servico_cliente']);
								$orc_data_cadastro = date("d/m/Y", strtotime($row['orc_data_cadastro']));
								$orc_hora_cadastro = date("H:i", strtotime($row['orc_data_cadastro']));
								$orc_observacoes = htmlspecialchars($row['orc_observacoes']);
								$sto_status = $row['sto_status'];
								$sto_status_n = ["<span class='laranja'>Pendente</span>", "<span class='azul'>Calculado</span>", "<span class='verde'>Aprovado</span>", "<span class='vermelho'>Reprovado</span>"][$sto_status - 1] ?? '';

								$c1 = $c ? "linhapar" : "linhaimpar";
								$c = !$c;

								echo "<tr class='$c1'>
                                      <td>$orc_id</td>
                                      <td>$tps_nome</td>
                                      <td>$orc_observacoes</td>
                                      <td align='center'>$orc_data_cadastro<br><span class='detalhe'>$orc_hora_cadastro</span></td>
                                      <td align='center'>$sto_status_n</td>
                                      <td align='center'><img class='mouse' src='../imagens/icon-pdf.png' onclick=\"window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');\"></td>
                                  </tr>";
							}

							echo "</table>";
							include("../mod_includes/php/paginacao.php");
						} else {
							echo "<br><br><br>Não há nenhum orçamento cadastrado.";
						}
						?>
					</div>
				</td>
			</tr>
		</table>
	</div>

	<?php include('../mod_rodape/rodape.php'); ?>
</body>

</html>