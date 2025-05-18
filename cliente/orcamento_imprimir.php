<?php
session_start();
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php';

// Obtém o ID do orçamento
$orcamentoId = $_GET['orc_id'] ?? '';

if (!$orcamentoId) {
	die('ID do orçamento não informado.');
}

// Consulta os dados do orçamento
$sql = "
	SELECT * 
	FROM orcamento_gerenciar 
	LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
	LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
	LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
	WHERE h1.sto_id = (
		SELECT MAX(h2.sto_id) 
		FROM cadastro_status_orcamento h2 
		WHERE h2.sto_orcamento = h1.sto_orcamento
	)
	AND orc_id = :orc_id
";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':orc_id', $orcamentoId, PDO::PARAM_INT);
$stmt->execute();
$orcamento = $stmt->fetch();

if (!$orcamento) {
	die('Orçamento não encontrado.');
}

// Formatação de datas
$dataCadastro = date('d/m/Y H:i', strtotime($orcamento['orc_data_cadastro']));
$dataAprovacao = $orcamento['orc_data_aprovacao'] ? date('d/m/Y', strtotime($orcamento['orc_data_aprovacao'])) : '-';

// Status formatado
$statusFormatado = match ($orcamento['sto_status']) {
	1 => "<span class='laranja'>Pendente</span>",
	2 => "<span class='azul'>Calculado</span>",
	3 => "<span class='verde'>Aprovado</span>",
	4 => "<span class='vermelho'>Reprovado</span>",
	default => "Não especificado"
};

// Inicia o buffer de saída
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
						<td class="label">Orçamento N°:</td>
						<td><?= str_pad($orcamento['orc_id'], 6, '0', STR_PAD_LEFT) ?></td>
						<td class="label">Status:</td>
						<td><?= $statusFormatado ?></td>
					</tr>
					<tr>
						<td class="label">Condomínio:</td>
						<td colspan="3"><?= htmlspecialchars($orcamento['cli_nome_razao']) ?></td>
					</tr>
					<tr>
						<td class="label">Referente:</td>
						<td colspan="3">
							<?= htmlspecialchars($orcamento['tps_nome'] ?? $orcamento['orc_tipo_servico_cliente']) ?>
						</td>
					</tr>
					<tr>
						<td class="label">Data de cadastro:</td>
						<td><?= $dataCadastro ?></td>
						<td class="label">Data de aprovação/reprovação:</td>
						<td><?= $dataAprovacao ?></td>
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
$mpdf->SetTitle('Exacto Adm | Imprimir Orçamento');
$mpdf->SetHTMLHeader(
	'<div class="topo">
		<img src="../imagens/logo.png" width="200"><br><br>
		<img src="../imagens/linha.png">
	</div>'
);
$mpdf->SetHTMLFooter(
	'<div class="rodape">
		<span class="azul">Exacto Assessoria e Administração</span><br>
		Rua Prof. Emilio Augusto Ferreira, 32 - Vila Oliveira, Mogi das Cruzes/SP<br>
		Fone: (11) <span class="verde">4791-9220</span><br>
		Email: <span class="azul">exacto@exactoadm.com.br</span> | 
		Site: <span class="azul">www.exactoadm.com.br</span>
	</div>'
);
$mpdf->WriteHTML($html);
$nomeArquivo = 'Orçamento_' . str_pad(htmlspecialchars($orcamento['orc_id']), 6, '0', STR_PAD_LEFT) . '.pdf';
$mpdf->Output($nomeArquivo, 'I');
exit();