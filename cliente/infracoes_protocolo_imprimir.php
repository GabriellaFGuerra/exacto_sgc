<?php
session_start();
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php';

$login = $_GET['login'] ?? '';
$n = $_GET['n'] ?? '';
$pagina = isset($_GET['pagina']) ? (int) $_GET['pagina'] : 1;
$infId = $_GET['inf_id'] ?? '';

if (!$infId) {
	die('ID da infração não informado.');
}

// Paginação
$registrosPorPagina = 1; // Ajuste conforme necessário
$offset = ($pagina - 1) * $registrosPorPagina;

// Consulta segura com paginação
$sql = "
	SELECT ig.*, cc.cli_nome_razao 
	FROM infracoes_gerenciar ig
	LEFT JOIN cadastro_clientes cc ON cc.cli_id = ig.inf_cliente
	WHERE ig.inf_id = :inf_id
	LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':inf_id', $infId, PDO::PARAM_INT);
$stmt->bindValue(':limit', $registrosPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$registro = $stmt->fetch();

if (!$registro) {
	die('Registro não encontrado.');
}

$dataEntrega = date('d/m/Y', strtotime($registro['inf_data']));

$tipos = [
	'Notificação de advertência por infração disciplinar' => 'Advertência por infração',
	'Multa por Infração Interna' => 'Multa por infração',
	'Notificação de ressarcimento' => 'Notificação de ressarcimento',
	'Comunicação interna' => 'Comunicação interna'
];
$tipoTexto = $tipos[$registro['inf_tipo']] ?? 'Não especificado';

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
		padding: 20px;
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
				<table class="bordatabela" cellspacing="0" cellpadding="5" width="1000">
					<tr>
						<td colspan="4" class="label2" align="center">
							A/C <?php echo htmlspecialchars($registro['inf_proprietario']); ?>
						</td>
					</tr>
					<tr>
						<td class="label" align="right">Data entrega:</td>
						<td><?php echo $dataEntrega; ?></td>
						<td class="label" align="right">N°:</td>
						<td>
							<?php
							echo str_pad($registro['inf_id'], 3, '0', STR_PAD_LEFT) . '/' . htmlspecialchars($registro['inf_ano']);
							?>
						</td>
					</tr>
					<tr>
						<td class="label" align="right">Referente a entrega de:</td>
						<td colspan="3"><?php echo htmlspecialchars($tipoTexto); ?></td>
					</tr>
					<tr>
						<td class="label" align="right">Nome do condomínio:</td>
						<td colspan="3"><?php echo htmlspecialchars($registro['cli_nome_razao']); ?></td>
					</tr>
					<tr>
						<td class="label" align="right">Apto:</td>
						<td><?php echo htmlspecialchars($registro['inf_apto']); ?></td>
						<td class="label" align="right">Bloco:</td>
						<td><?php echo htmlspecialchars($registro['inf_bloco']); ?></td>
					</tr>
					<tr>
						<td colspan="4" align="center" class="italic">
							Recebi em _______/_______/____________<br><br><br>
							________________________________________________<br>Nome legível
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>

<?php
$html = ob_get_clean();

// Geração do PDF usando mPDF
require_once __DIR__ . '/vendor/autoload.php';

use Mpdf\Mpdf;

$mpdf = new Mpdf();
$mpdf->SetTitle('Exacto Adm | Imprimir Prestação de Contas');
$mpdf->SetHTMLHeader('<div class="topo"><img src="../imagens/logo.png" width="200"><br><br></div>');
$mpdf->SetHTMLFooter('
	<div class="rodape">
		<span class="azul">Exacto Assessoria e Administração</span><br>
		Rua Prof. Emilio Augusto Ferreira, 32 - Vila Oliveira, Mogi das Cruzes/SP<br>
		Fone: (11) <span class="verde">4791-9220</span><br>
		Email: <span class="azul">exacto@exactoadm.com.br</span> | 
		Site: <span class="azul">www.exactoadm.com.br</span>
	</div>
');
$mpdf->WriteHTML($html);
$nomeArquivo = 'Protocolo_' . str_pad(htmlspecialchars($registro['inf_id']), 6, '0', STR_PAD_LEFT) . '.pdf';
$mpdf->Output($nomeArquivo, 'I');
exit();