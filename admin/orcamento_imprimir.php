<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php';

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

// Sanitização básica
$login = $_GET['login'] ?? '';
$n = $_GET['n'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$orc_id = isset($_GET['orc_id']) ? (int) $_GET['orc_id'] : 0;

$autenticacao = '&login=' . urlencode($login) . '&n=' . urlencode($n);

// Consulta principal
$sql = "
	SELECT og.*, cc.cli_nome_razao, cc.cli_cnpj, cs.tps_nome, h1.sto_status, h1.sto_id, og.orc_tipo_servico_cliente
	FROM orcamento_gerenciar og
	LEFT JOIN cadastro_clientes cc ON cc.cli_id = og.orc_cliente
	LEFT JOIN cadastro_tipos_servicos cs ON cs.tps_id = og.orc_tipo_servico
	LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = og.orc_id
	WHERE h1.sto_id = (
		SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento
	) AND og.orc_id = :orc_id
	LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['orc_id' => $orc_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
	die('Orçamento não encontrado.');
}

$orc_id = $row['orc_id'];
$orc_cliente = $row['orc_cliente'];
$cli_nome_razao = $row['cli_nome_razao'];
$cli_cnpj = $row['cli_cnpj'];
$orc_tipo_servico = $row['orc_tipo_servico'];
$tps_nome = $row['tps_nome'] ?: $row['orc_tipo_servico_cliente'];
$orc_observacoes = $row['orc_observacoes'];
$sto_status = $row['sto_status'];
$orc_data_cadastro = date('d/m/Y', strtotime($row['orc_data_cadastro']));
$orc_hora_cadastro = date('H:i', strtotime($row['orc_data_cadastro']));
$orc_data_aprovacao = $row['orc_data_aprovacao'] ? date('d/m/Y', strtotime($row['orc_data_aprovacao'])) : '';

$status_labels = [
	1 => "<span class='laranja'>Pendente</span>",
	2 => "<span class='azul'>Calculado</span>",
	3 => "<span class='verde'>Aprovado</span>",
	4 => "<span class='vermelho'>Reprovado</span>"
];
$sto_status_n = $status_labels[$sto_status] ?? '';

ob_start();
?>
<table align="center" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="left">
            <div class="laudo">
                <table class="laudo" align="center" cellspacing="0" cellpadding="3" width="1000">
                    <tr>
                        <td colspan="2" align="center">
                            <span class="titulo_laudo">Cotação de Material/Serviço</span>
                            <br>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table cellspacing="0" cellpadding="5" width="1000">
                                <tr>
                                    <td width="20%" class="label" align="right">Orçamento N°:</td>
                                    <td><?= str_pad($orc_id, 6, '0', STR_PAD_LEFT) ?></td>
                                    <td width="30%" class="label" align="right">Status:</td>
                                    <td><?= $sto_status_n ?></td>
                                </tr>
                                <tr>
                                    <td class="label" align="right">Condomínio:</td>
                                    <td colspan="3"><?= htmlspecialchars($cli_nome_razao) ?></td>
                                </tr>
                                <tr>
                                    <td class="label" align="right">Referente:</td>
                                    <td colspan="3"><?= htmlspecialchars($tps_nome) ?></td>
                                </tr>
                                <tr>
                                    <td class="label" align="right">Data de cadastro:</td>
                                    <td><?= $orc_data_cadastro ?> às <?= $orc_hora_cadastro ?></td>
                                    <td class="label" align="right">Data de aprovação/reprovação:</td>
                                    <td><?= $orc_data_aprovacao ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center" class="formtitulo">Empresas Contatadas</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table class="bordatabela" cellpadding="10" cellspacing="0" width="700">
                                <tr>
                                    <td class="titulo_first">Nome da Empresa</td>
                                    <td class="titulo_tabela">Valor</td>
                                    <td class="titulo_tabela">INSS (20%)</td>
                                    <td class="titulo_tabela">Observação</td>
                                    <td class="titulo_tabela" align="right">Total</td>
                                    <td class="titulo_last"></td>
                                </tr>
                                <?php
								$sql_fornecedores = "
									SELECT ofn.*, cf.for_nome_razao, cf.for_autonomo
									FROM orcamento_fornecedor ofn
									LEFT JOIN cadastro_fornecedores cf ON cf.for_id = ofn.orf_fornecedor
									WHERE ofn.orf_orcamento = :orc_id
									ORDER BY ofn.orf_valor ASC
								";
								$stmt_fornecedores = $pdo->prepare($sql_fornecedores);
								$stmt_fornecedores->execute(['orc_id' => $orc_id]);
								$fornecedores = $stmt_fornecedores->fetchAll(PDO::FETCH_ASSOC);

								$c = 0;
								foreach ($fornecedores as $fornecedor):
									$total = $fornecedor['orf_valor'];
									$classe = $c % 2 === 0 ? 'linhaimpar' : 'linhapar';
									$c++;
									$inss = '';
									if ($fornecedor['for_autonomo'] == 1) {
										$valor_autonomo = $fornecedor['orf_valor'] * 0.2;
										$total += $valor_autonomo;
										$inss = '+ R$ ' . number_format($valor_autonomo, 2, ',', '.');
									}
									?>
                                <tr class="<?= $classe ?>">
                                    <td><?= htmlspecialchars($fornecedor['for_nome_razao']) ?></td>
                                    <td>R$ <?= number_format($fornecedor['orf_valor'], 2, ',', '.') ?></td>
                                    <td><?= $inss ?></td>
                                    <td><?= htmlspecialchars($fornecedor['orf_obs']) ?></td>
                                    <td align="right"><b>R$ <?= number_format($total, 2, ',', '.') ?></b></td>
                                    <td>
                                        <div style="border:1px solid #666;">&nbsp;&nbsp;&nbsp;&nbsp;</div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"></td>
                    </tr>
                    <tr>
                        <td align="left" colspan="2">
                            <b>Observações:</b> <?= nl2br(htmlspecialchars($orc_observacoes)) ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><br><br>&nbsp;</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table class="bordatabela" cellpadding="10" cellspacing="0" width="700">
                                <tr>
                                    <td colspan="2" class="titulo_tabela" align="center">
                                        Aprovação (assinalar a empresa acima e preencher com data/assinatura)
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width="30%" align="right">Data</td>
                                    <td>_______/_______/______________</td>
                                </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td align="right">Assinatura</td>
                                    <td>_________________________________________________________</td>
                                </tr>
                                <tr>
                                    <td colspan="2">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="titulo_adm"></div>
        </td>
    </tr>
</table>
<?php
$html = ob_get_clean();

require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

$mpdf = new Mpdf([
	'format' => 'A4',
	'margin_left' => 10,
	'margin_right' => 10,
	'margin_top' => 30,
	'margin_bottom' => 30,
	'margin_header' => 5,
	'margin_footer' => 5,
	'orientation' => 'P'
]);
$mpdf->SetTitle('Exacto Adm | Imprimir Orçamento');
$mpdf->useOddEven = false;
$mpdf->SetHTMLHeader('<div class="topo"><img src="../imagens/logo.png" width="200"><br><br><img src="../imagens/linha.png" /></div>');
$mpdf->SetHTMLFooter('
<div class="rodape">
<img src="../imagens/linha.png" />
<table align="center" class="rod" width="100%">
<tr>
<td colspan="2" align="center">
<br>
<span class="azul">Exacto Assessoria e Administração</span><br>
Rua Prof. Emilio Augusto Ferreira, 32 - Vila Oliveira, Mogi das Cruzes/SP<br>
Fone: (11) <span class="verde">4791-9220</span><br>
Email: <span class="azul">exacto@exactoadm.com.br</span> | Site: <span class="azul">www.exactoadm.com.br</span><br>
</td>
</tr>
<tr>
<td colspan="2" align="right">
{PAGENO} / {nbpg}
</td>
</tr>
</table>
</div>
');

// Inclui o CSS externo
$css = file_get_contents(__DIR__ . '/pdf.css');
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'UTF-8';
$mpdf->WriteHTML($html);

// Importar planilha PDF do orçamento, se houver
$sql_planilha = "SELECT orc_planilha FROM orcamento_gerenciar WHERE orc_id = :orc_id";
$stmt_planilha = $pdo->prepare($sql_planilha);
$stmt_planilha->execute(['orc_id' => $orc_id]);
$planilha = $stmt_planilha->fetchColumn();

if ($planilha) {
	$mpdf->SetHTMLHeader('');
	$pagecount = $mpdf->SetSourceFile($planilha);
	for ($i = 1; $i <= $pagecount; $i++) {
		$mpdf->AddPage();
		$mpdf->SetHTMLFooter('<div class="rodape">
<table align="center" class="rod" width="100%">
<tr>
<td colspan="2" align="center"></td>
</tr>
<tr>
<td colspan="2" align="right">{PAGENO} / {nbpg}</td>
</tr>
</table>
</div>');
		$import_page = $mpdf->ImportPage($i);
		$mpdf->UseTemplate($import_page);
	}
}

// Importar anexos dos fornecedores, se houver
$sql_anexos = "
	SELECT ofn.orf_anexo
	FROM orcamento_fornecedor ofn
	WHERE ofn.orf_orcamento = :orc_id AND ofn.orf_anexo != ''
	ORDER BY ofn.orf_id ASC
";
$stmt_anexos = $pdo->prepare($sql_anexos);
$stmt_anexos->execute(['orc_id' => $orc_id]);
$anexos = $stmt_anexos->fetchAll(PDO::FETCH_COLUMN);

foreach ($anexos as $anexo) {
	$mpdf->SetHTMLHeader('');
	$pagecount = $mpdf->SetSourceFile($anexo);
	for ($i = 1; $i <= $pagecount; $i++) {
		$mpdf->AddPage();
		$mpdf->SetHTMLFooter('<div class="rodape">
<table align="center" class="rod" width="100%">
<tr>
<td colspan="2" align="center"></td>
</tr>
<tr>
<td colspan="2" align="right">{PAGENO} / {nbpg}</td>
</tr>
</table>
</div>');
		$import_page = $mpdf->ImportPage($i);
		$mpdf->UseTemplate($import_page);
	}
}

$mpdf->Output('Orçamento_' . str_pad($orc_id, 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit();