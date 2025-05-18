<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php'; // $pdo deve estar disponível

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

$login = $_GET['login'] ?? '';
$n = $_GET['n'] ?? '';
$autenticacao = "&login=$login&n=" . urlencode($n);
$pagina = $_GET['pagina'] ?? '';
$inf_id = $_GET['inf_id'] ?? '';

$sql = "SELECT ig.*, cc.cli_foto, cc.cli_nome_razao, cc.cli_cnpj 
		FROM infracoes_gerenciar ig
		LEFT JOIN cadastro_clientes cc ON cc.cli_id = ig.inf_cliente
		WHERE ig.inf_id = :inf_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['inf_id' => $inf_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
	die('Infração não encontrada.');
}

$inf_id = $row['inf_id'];
$inf_cliente = $row['inf_cliente'];
$cli_foto = $row['cli_foto'];
$cli_nome_razao = $row['cli_nome_razao'];
$cli_cnpj = $row['cli_cnpj'];
$inf_ano = $row['inf_ano'];
$inf_tipo = $row['inf_tipo'];
$inf_cidade = $row['inf_cidade'];
$inf_data = date('d/m/Y', strtotime($row['inf_data']));
$inf_proprietario = $row['inf_proprietario'];
$inf_apto = $row['inf_apto'];
$inf_bloco = $row['inf_bloco'];
$inf_endereco = $row['inf_endereco'];
$inf_email = $row['inf_email'];
$inf_desc_irregularidade = $row['inf_desc_irregularidade'];
$inf_assunto = $row['inf_assunto'];
$inf_desc_artigo = $row['inf_desc_artigo'];
$inf_desc_notificacao = $row['inf_desc_notificacao'];

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

.label,
.label2 {
    font-family: "Calibri";
    font-weight: bold;
}

.label2 {
    font-size: 16px;
}

.cliente {
    font-family: "Calibri";
    font-weight: normal;
    font-size: 11px;
    color: #000;
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

.topo2 {
    float: left;
    width: 33%;
    text-align: center;
    font-size: 15px;
    font-family: "sharpmedium";
    color: #0F72BD;
    font-weight: bold;
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
<div class='laudo'>
    <table class='bordatabela' cellspacing='0' cellpadding='5' width='1000'>
        <tr>
            <td colspan='3' class='label' align='left'>
                <?= htmlspecialchars($inf_cidade) ?>, <?= htmlspecialchars($inf_data) ?>
            </td>
        </tr>
        <tr>
            <td align='left' valign='top'>
                <b>Proprietário(a):</b> <?= htmlspecialchars($inf_proprietario) ?>
            </td>
            <td align='left' valign='top'>
                <b>Unidade:</b> <?= htmlspecialchars($inf_apto) ?>
            </td>
            <td align='left' valign='top'>
                <b>Bloco/Quadra:</b> <?= htmlspecialchars($inf_bloco) ?>
            </td>
        </tr>
        <tr>
            <td colspan='3' width='20%' align='left'>
                <b>Endereço:</b> <?= htmlspecialchars($inf_endereco) ?>
            </td>
        </tr>
        <tr>
            <td colspan='3' width='20%' align='left'>
                <b>Email:</b> <?= htmlspecialchars($inf_email) ?>
            </td>
        </tr>
    </table>
    <br>
    <table class='bordatabela' cellspacing='0' cellpadding='5' width='1000'>
        <tr>
            <td align='left'>
                <b>Assunto:</b> <?= htmlspecialchars($inf_assunto) ?>
            </td>
        </tr>
    </table>
    <br>
    <table class='bordatabela' cellspacing='0' cellpadding='5' width='1000'>
        <tr>
            <td colspan='3' class='label' align='left'>
                Descrição da irregularidade / ocorrência, data e hora:
            </td>
        </tr>
        <tr>
            <td colspan='3' align='left' valign='top'>
                <?= nl2br(htmlspecialchars($inf_desc_irregularidade)) ?>
            </td>
        </tr>
    </table>
    <br>
    <table class='bordatabela' cellspacing='0' cellpadding='5' width='1000'>
        <tr>
            <td colspan='3' class='label' align='left'>
                Descrição do(s) artigo(s) que regulam o assunto:
            </td>
        </tr>
        <tr>
            <td colspan='3' align='left' valign='top'>
                <?= nl2br(htmlspecialchars($inf_desc_artigo)) ?>
            </td>
        </tr>
    </table>
    <br>
    <table class='bordatabela' cellspacing='0' cellpadding='5' width='1000'>
        <tr>
            <td colspan='3' class='label' align='left'>
                Notificação Disciplinar:
            </td>
        </tr>
        <tr>
            <td colspan='3' align='left' valign='top'>
                <?= nl2br(htmlspecialchars($inf_desc_notificacao)) ?>
            </td>
        </tr>
    </table>
    <br><br>
</div>
<div class='titulo_adm'></div>
<?php
$html = ob_get_clean();

require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

$mpdf = new Mpdf([
	'format' => 'A4',
	'margin_left' => 10,
	'margin_right' => 10,
	'margin_top' => 35,
	'margin_bottom' => 30,
	'margin_header' => 5,
	'margin_footer' => 15,
	'orientation' => 'P'
]);
$mpdf->SetTitle('Exacto Adm | Imprimir Prestação de Contas');
$mpdf->useOddEven = false;
$mpdf->SetHTMLHeader(
	'<div class="topo2"><img src="' . htmlspecialchars($cli_foto) . '" height="100"></div>
	<div class="topo2"><br>' . htmlspecialchars($inf_tipo) . '<br><span class="cliente">' . htmlspecialchars($cli_nome_razao) . '</span></div>
	<div class="topo2"><br>Nº. ' . str_pad($inf_id, 3, "0", STR_PAD_LEFT) . '/' . $inf_ano . '</div>'
);
$mpdf->SetHTMLFooter(
	'<div class="rodape">
		<table align="center" class="rod" width="100%">
			<tr>
				<td colspan="2" align="left">
					<br>
					Atenciosamente,
					<br>
					' . htmlspecialchars($cli_nome_razao) . '
				</td>
			</tr>
		</table>
	</div>'
);

$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'UTF-8';
$mpdf->WriteHTML($html);
$mpdf->SetImportUse();

$mpdf->Output('Infração_' . str_pad($inf_id, 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit();