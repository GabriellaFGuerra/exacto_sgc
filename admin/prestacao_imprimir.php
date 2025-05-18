<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php'; // Sua conexão PDO deve estar aqui

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
$pre_id = $_GET['pre_id'] ?? '';

$autenticacao = "&login=" . urlencode($login) . "&n=" . urlencode($n);

$pre_id_int = intval($pre_id);

$sql = "SELECT pg.*, cc.cli_nome_razao, cc.cli_cnpj 
		FROM prestacao_gerenciar pg
		LEFT JOIN cadastro_clientes cc ON cc.cli_id = pg.pre_cliente
		WHERE pg.pre_id = :pre_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['pre_id' => $pre_id_int]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
	die('Registro não encontrado.');
}

$pre_id = $row['pre_id'];
$pre_cliente = $row['pre_cliente'];
$cli_nome_razao = htmlspecialchars($row['cli_nome_razao'] ?? '', ENT_QUOTES, 'UTF-8');
$cli_cnpj = htmlspecialchars($row['cli_cnpj'] ?? '', ENT_QUOTES, 'UTF-8');
$pre_referencia = $row['pre_referencia'] ?? '';
$ref = explode("/", $pre_referencia);
$pre_ref_mes = str_pad($ref[0] ?? '', 2, '0', STR_PAD_LEFT);
$pre_ref_ano = $ref[1] ?? '';
$pre_ref_mes_n = $meses[$pre_ref_mes] ?? '';

$pre_data_envio = '';
if (!empty($row['pre_data_envio']) && $row['pre_data_envio'] !== '0000-00-00') {
	$pre_data_envio = date('d/m/Y', strtotime($row['pre_data_envio']));
}

$pre_enviado_por = htmlspecialchars($row['pre_enviado_por'] ?? '', ENT_QUOTES, 'UTF-8');
$pre_observacoes = nl2br(htmlspecialchars($row['pre_observacoes'] ?? '', ENT_QUOTES, 'UTF-8'));

$pre_data_cadastro = '';
$pre_hora_cadastro = '';
if (!empty($row['pre_data_cadastro'])) {
	$pre_data_cadastro = date('d/m/Y', strtotime($row['pre_data_cadastro']));
	$pre_hora_cadastro = date('H:i', strtotime($row['pre_data_cadastro']));
}

ob_start();
?>
<style>
.topo {
    margin: 0 auto;
    text-align: center;
    padding: 0 0 15px 0;
}

.rodape {
    margin: 0 auto;
    text-align: left;
    padding: 15px 0 0 0;
    font-family: "Calibri";
}

.rod {
    color: #999;
    font-size: 13px;
    font-family: "Calibri";
}

.titulo_adm {
    width: 960px;
    margin: 0 auto;
    font-size: 18px;
    color: #999;
    text-align: left;
    border-bottom: 1px dashed #DDD;
    padding: 0 0 10px 10px;
    margin: 20px 0 10px 0;
}

.laudo {
    font-family: "Calibri";
    font-size: 13px;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    border-radius: 10px;
    padding: 20px 10px;
}

.titulo_laudo {
    font-size: 20px;
    font-family: "sharpmedium";
    color: #0F72BD;
    font-weight: bold;
    text-align: center;
}

.titulo_tabela,
.titulo_first,
.titulo_last {
    font-size: 13px;
    font-family: "Calibri";
    border: 0;
    color: #333;
    background: #EEE;
}

.titulo_first {
    border-radius: 5px 0px 0px 0px;
}

.titulo_last {
    border-radius: 0px 5px 0px 0px;
}

.bordatabela {
    border: 1px solid #DADADA;
    font-size: 11px;
    color: #666;
    border-radius: 2px 2px 0px 0px;
}

.formtitulo {
    font-family: "Calibri";
    text-align: left;
    font-size: 16px;
    color: #81C566;
    padding: 25px 0px 0px 0px;
}

.label {
    font-family: "Calibri";
    font-weight: bold;
}

.label2 {
    font-family: "Calibri";
    font-weight: bold;
    font-size: 16px;
}

.azul {
    color: #0F72BD;
}

.laranja {
    color: #F60;
    font-weight: bold;
}

.verde {
    color: #81C566;
    font-weight: bold;
}

.vermelho {
    color: #900;
    font-weight: bold;
}

.italic {
    font-style: italic;
}

.linhapar {
    background: #FAFAFA;
}

.linhaimpar {
    background: #FFFFFF;
}

#resultados_anteriores {
    border-collapse: collapse;
    width: 1000px;
}

#resultados_anteriores tr td {
    border: 1px solid #CCC;
    text-align: center;
}

#resultados_anteriores .titulo_ant {
    background: #EEE;
    text-align: center;
}

#resultados_anteriores .esquerda {
    text-align: left;
}
</style>
<table align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='left'>
            <div class='laudo'>
                <table class='laudo' align='center' cellspacing='0' cellpadding='3' width='1000'>
                    <?php for ($i = 0; $i < 2; $i++): ?>
                    <tr>
                        <td colspan='2' align='center'>
                            <span class='titulo_laudo'>Protocolo</span>
                            <br>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <table class='bordatabela' cellspacing='0' cellpadding='5' width='1000'>
                                <tr>
                                    <td colspan='4' height='60' class='label2' align='center'>
                                        A/C <?= $cli_nome_razao ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width='20%' class='label' align='right'>
                                        Data entrega:
                                    </td>
                                    <td>
                                        <?= $pre_data_envio ?>
                                    </td>
                                    <td width='20%' class='label' align='right'>
                                        N°:
                                    </td>
                                    <td>
                                        <?= $pre_id ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width='20%' class='label' align='right'>
                                        Enviado por:
                                    </td>
                                    <td colspan='3'>
                                        <?= $pre_enviado_por ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class='label' align='right' height='120' valign='top'>
                                        Referente a entrega de:
                                    </td>
                                    <td colspan='3' valign='top'>
                                        Pasta de Prestação de <?= $pre_ref_mes_n ?> de <?= $pre_ref_ano ?>
                                        <br><br><br>
                                        <?= $pre_observacoes ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan='4' align='center' class='italic'>
                                        Recebi em _______/_______/____________
                                        <br><br><br>
                                        ______________________________________________<br>
                                        Nome legível
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <?php if ($i == 0): ?>
                    <tr>
                        <td colspan='2'>
                            <div class='rodape'>
                                <table align='center' class='rod' width='100%'>
                                    <tr>
                                        <td colspan='2' align='center'>
                                            <br>
                                            <span class='azul'>Exacto Assessoria e Administração</span><br>
                                            Rua Prof. Emilio Augusto Ferreira, 32 - Vila Oliveira, Mogi das
                                            Cruzes/SP<br>
                                            Fone: (11) <span class='verde'>4791-9220</span><br>
                                            Email: <span class='azul'>exacto@exactoadm.com.br</span> | Site: <span
                                                class='azul'>www.exactoadm.com.br</span><br>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <br>
                            <img src='../imagens/linha.png' />
                            <br>
                            <div class='topo'>
                                <center><img src='../imagens/logo.png' width='200'></center><br><br>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endfor; ?>
                </table>
            </div>
            <div class='titulo_adm'> </div>
        </td>
    </tr>
</table>
<?php
$html = ob_get_clean();

require_once __DIR__ . '../vendor/autoload.php';
use Mpdf\Mpdf;

$mpdf = new Mpdf([
	'format' => 'A4',
	'margin_left' => 10,
	'margin_right' => 10,
	'margin_top' => 25,
	'margin_bottom' => 23,
	'margin_header' => 5,
	'margin_footer' => 5,
	'orientation' => 'P'
]);

$mpdf->SetTitle('Exacto Adm | Imprimir Prestação de Contas');
$mpdf->useOddEven = false;
$mpdf->SetHTMLHeader('<div class="topo"><img src="../imagens/logo.png" width="200"><br><br></div>');
$mpdf->SetHTMLFooter('
<div class="rodape">
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
</table>
</div>
');

$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'UTF-8';
$mpdf->WriteHTML($html);
$mpdf->Output('Prestacao_' . str_pad($pre_id, 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit();