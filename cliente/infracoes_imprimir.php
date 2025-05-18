<?php
session_start();
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php';

// Meses
$meses = [
	'01' => 'Janeiro',
	'02' => 'Fevereiro',
	'03' => 'Março',
	'04' => 'Abril',
	'05' => 'Maio',
	'06' => 'Junho',
	'07' => 'Julho',
	'08' => 'Agosto',
	'09' => 'Setembro',
	'10' => 'Outubro',
	'11' => 'Novembro',
	'12' => 'Dezembro'
];

// Parâmetros de entrada
$login = $_GET['login'] ?? '';
$n = $_GET['n'] ?? '';
$pagina = isset($_GET['pagina']) ? max(1, (int) $_GET['pagina']) : 1;
$infId = $_GET['inf_id'] ?? '';
$clienteId = $_SESSION['cliente_id'] ?? null;

// Paginação
$itensPorPagina = 1; // Ajuste conforme necessário
$offset = ($pagina - 1) * $itensPorPagina;

// Consulta segura
$sql = "SELECT * FROM infracoes_gerenciar 
		LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
		WHERE inf_id = :inf_id AND inf_cliente = :cliente_id
		LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':inf_id', $infId, PDO::PARAM_INT);
$stmt->bindValue(':cliente_id', $clienteId, PDO::PARAM_INT);
$stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$registro = $stmt->fetch();

if ($registro) {
	$infData = date('d/m/Y', strtotime($registro['inf_data']));
}

// Buffer de saída
ob_start();
?>

<style>
	.titulo_adm {
		width: 960px;
		margin: auto;
		font-size: 18px;
		color: #999;
		text-align: left;
		padding: 10px;
	}

	.laudo {
		font-family: "Calibri";
		font-size: 13px;
		padding: 20px 10px;
		border-radius: 10px;
	}

	.titulo_tabela {
		font-size: 13px;
		font-family: "Calibri";
		background: #EEE;
	}

	.bordatabela {
		border: 1px solid #DADADA;
		font-size: 11px;
		color: #666;
		border-radius: 2px;
	}

	.rodape {
		margin: auto;
		text-align: left;
		padding: 15px 0;
		font-family: "Calibri";
	}
</style>

<table align="center" border="0">
	<tr>
		<td align="left">
			<div class="laudo">
				<table class="laudo" align="center" cellspacing="0" cellpadding="5" width="1000">
					<tr>
						<td colspan="3" class="titulo_tabela">
							Cidade e Data:
							<?= htmlspecialchars($registro['inf_cidade'] ?? '') . ', ' . htmlspecialchars($infData ?? '') ?>
						</td>
					</tr>
					<tr>
						<td><b>Proprietário:</b> <?= htmlspecialchars($registro['inf_proprietario'] ?? '') ?></td>
						<td><b>Unidade:</b> <?= htmlspecialchars($registro['inf_apto'] ?? '') ?></td>
						<td><b>Bloco:</b> <?= htmlspecialchars($registro['inf_bloco'] ?? '') ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Endereço:</b> <?= htmlspecialchars($registro['inf_endereco'] ?? '') ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Email:</b> <?= htmlspecialchars($registro['inf_email'] ?? '') ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Descrição da Irregularidade:</b>
							<?= htmlspecialchars($registro['inf_desc_irregularidade'] ?? '') ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Assunto:</b> <?= htmlspecialchars($registro['inf_assunto'] ?? '') ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Descrição do Artigo:</b>
							<?= htmlspecialchars($registro['inf_desc_artigo'] ?? '') ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Notificação Disciplinar:</b>
							<?= htmlspecialchars($registro['inf_desc_notificacao'] ?? '') ?></td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>

<?php
$html = ob_get_clean();

// Geração do PDF com mPDF
require_once __DIR__ . '/vendor/autoload.php';
use Mpdf\Mpdf;

$mpdf = new Mpdf();
$mpdf->SetTitle('Exacto Adm | Imprimir Prestação de Contas');
$mpdf->SetHTMLHeader(
	'<div class="topo2"><img src="' . htmlspecialchars($registro['cli_foto'] ?? '') . '" height="100"></div>' .
	'<div class="topo2">' . htmlspecialchars($registro['inf_tipo'] ?? '') . '<br><span class="cliente">' . htmlspecialchars($registro['cli_nome_razao'] ?? '') . '</span></div>' .
	'<div class="topo2"><br>Nº ' . str_pad(htmlspecialchars($registro['inf_id'] ?? ''), 3, "0", STR_PAD_LEFT) . '/' . htmlspecialchars($registro['inf_ano'] ?? '') . '</div>'
);
$mpdf->SetHTMLFooter(
	'<div class="rodape"><br>Atenciosamente,<br>' . htmlspecialchars($registro['cli_nome_razao'] ?? '') . '</div>'
);
$mpdf->WriteHTML($html);
$mpdf->Output(
	'Orcamento_' . str_pad(htmlspecialchars($registro['inf_id'] ?? ''), 6, '0', STR_PAD_LEFT) . '.pdf',
	'I'
);
exit();