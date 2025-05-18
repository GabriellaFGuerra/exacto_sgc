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

$login = $_GET['login'] ?? '';
$n = $_GET['n'] ?? '';
$autenticacao = "&login=$login&n=" . urlencode($n);
$pagina = $_GET['pagina'] ?? '';
$mal_id = $_GET['mal_id'] ?? '';

$stmt = $pdo->prepare("
	SELECT m.*, c.cli_nome_razao 
	FROM malote_gerenciar m
	LEFT JOIN cadastro_clientes c ON c.cli_id = m.mal_cliente
	WHERE m.mal_id = :mal_id
");
$stmt->execute(['mal_id' => $mal_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
	die('Malote não encontrado.');
}

$mal_id = $row['mal_id'];
$mal_lacre = $row['mal_lacre'];
$cli_nome_razao = $row['cli_nome_razao'];
$mal_observacoes = $row['mal_observacoes'];
$mal_data_cadastro = date('d/m/Y', strtotime($row['mal_data_cadastro']));
$mal_hora_cadastro = date('H:i', strtotime($row['mal_data_cadastro']));

// Itens do malote
$stmtItens = $pdo->prepare("SELECT * FROM malote_itens WHERE mai_malote = :mal_id");
$stmtItens->execute(['mal_id' => $mal_id]);
$itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>
<style>
/* ... (mantém o CSS igual ao original) ... */
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
    -moz-border-radius: 10px;
    -webkit-border-radius: 10px;
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

.titulo_tabela {
    font-size: 13px;
    font-family: "Calibri";
    border: 0;
    color: #333;
    background: #EEE;
}

.titulo_first {
    font-size: 13px;
    font-family: "Calibri";
    border: 0;
    color: #333;
    background: #EEE;
    -moz-border-radius: 5px 0px 0px 0px;
    -webkit-border-radius: 5px 0px 0px 0px;
    border-radius: 5px 0px 0px 0px;
}

.titulo_last {
    font-size: 13px;
    font-family: "Calibri";
    border: 0;
    color: #333;
    background: #EEE;
    -moz-border-radius: 0px 5px 0px 0px;
    -webkit-border-radius: 0px 5px 0px 0px;
    border-radius: 0px 5px 0px 0px;
}

.bordatabela {
    border: 1px solid #DADADA;
    font-size: 11px;
    color: #666;
    -moz-border-radius: 2px 2px 0px 0px;
    -webkit-border-radius: 2px 2px 0px 0px;
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

.linhapar {
    background: #FAFAFA;
}

.linhaimpar {
    background: #FFFFFF;
}

.titulo_tabela2 {
    font-size: 11px;
    font-weight: bold;
    border: 0;
    color: #666;
    background: #FAFAFA;
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
<?php
function renderItens($itens)
{
	$c = 0;
	foreach ($itens as $item) {
		$c1 = ($c++ % 2 == 0) ? "linhaimpar" : "linhapar";
		$fornecedor = htmlspecialchars($item['mai_fornecedor']);
		$tipo_documento = htmlspecialchars($item['mai_tipo_documento']);
		$num_cheque = htmlspecialchars($item['mai_num_cheque']);
		$valor = 'R$ ' . number_format($item['mai_valor'], 2, ',', '.');
		$data_vencimento = $item['mai_data_vencimento'] ? date('d/m/Y', strtotime($item['mai_data_vencimento'])) : '';
		echo "<tr class='$c1'>
			<td>$fornecedor</td>
			<td>$tipo_documento</td>
			<td>$num_cheque</td>
			<td>$valor</td>
			<td align='center'>$data_vencimento</td>
		</tr>";
	}
}
?>
<table align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='left'>
            <div class='laudo'>
                <table class='laudo' align='center' cellspacing='0' cellpadding='3' width='1000'>
                    <tr>
                        <td colspan='2' align='center'>
                            <span class='titulo_laudo'>Protocolo de Envio</span>
                            <br>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <table class='bordatabela' cellspacing='0' cellpadding='3' width='1000'>
                                <tr>
                                    <td colspan='4' height='60' class='label2' align='center'>
                                        <?= htmlspecialchars($cli_nome_razao) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width='20%' class='label' align='right'>Data de Envio:</td>
                                    <td colspan='3'><?= $mal_data_cadastro ?></td>
                                </tr>
                                <tr>
                                    <td width='20%' class='label' align='right'>Malote N°:</td>
                                    <td><?= $mal_id ?></td>
                                    <td width='20%' class='label' align='right'>N° Lacre:</td>
                                    <td><?= htmlspecialchars($mal_lacre) ?></td>
                                </tr>
                                <tr>
                                    <td colspan='4' valign='top'>
                                        <table class='bordatabela' cellpadding='5' cellspacing='0' width='1000'>
                                            <tr>
                                                <td class='titulo_tabela2'>Fornecedor</td>
                                                <td class='titulo_tabela2'>Tipo Documento</td>
                                                <td class='titulo_tabela2'>N° Cheque</td>
                                                <td class='titulo_tabela2'>Valor</td>
                                                <td class='titulo_tabela2' align='center'>Data Vencimento</td>
                                            </tr>
                                            <?php renderItens($itens); ?>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan='4' align='center' class='italic'>
                                        <br>
                                        Data de Envio _______/_______/____________
                                        <br><br><br>
                                        ______________________________________________<br>
                                        Responsável pelo envio
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
        </td>
    </tr>
</table>
<table align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='left'>
            <div class='laudo'>
                <table class='laudo' align='center' cellspacing='0' cellpadding='3' width='1000'>
                    <tr>
                        <td colspan='2'>
                            <br>
                            <img src=../imagens/linha.png />
                            <br>
                            <div class='topo'>
                                <center><img src=../imagens/logo.png width='200'></center><br><br>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2' align='center'>
                            <span class='titulo_laudo'>Protocolo de Devolução</span>
                            <br>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2'>
                            <table class='bordatabela' cellspacing='0' cellpadding='3' width='1000'>
                                <tr>
                                    <td colspan='4' height='60' class='label2' align='center'>
                                        <?= htmlspecialchars($cli_nome_razao) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width='20%' class='label' align='right'>Data de Devolução:</td>
                                    <td colspan='3'>_______/_______/____________</td>
                                </tr>
                                <tr>
                                    <td width='20%' class='label' align='right'>Malote N°:</td>
                                    <td><?= $mal_id ?></td>
                                    <td width='20%' class='label' align='right'>N° Lacre:</td>
                                    <td><?= htmlspecialchars($mal_lacre) ?></td>
                                </tr>
                                <tr>
                                    <td colspan='4' valign='top'>
                                        <table class='bordatabela' cellpadding='5' cellspacing='0' width='1000'>
                                            <tr>
                                                <td class='titulo_tabela2'>Fornecedor</td>
                                                <td class='titulo_tabela2'>Tipo Documento</td>
                                                <td class='titulo_tabela2'>N° Cheque</td>
                                                <td class='titulo_tabela2'>Valor</td>
                                                <td class='titulo_tabela2' align='center'>Data Vencimento</td>
                                            </tr>
                                            <?php renderItens($itens); ?>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan='4' align='center' class='italic'>
                                        <br>
                                        Data de Devolução _______/_______/____________
                                        <br><br><br>
                                        ______________________________________________<br>
                                        Síndico
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <div class='titulo_adm'> </div>
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
	'margin_top' => 25,
	'margin_bottom' => 23,
	'margin_header' => 5,
	'margin_footer' => 5,
	'orientation' => 'P'
]);
$mpdf->SetTitle('Exacto Adm | Imprimir Malote');
$mpdf->useOddEven = false;
$mpdf->SetHTMLHeader('<div class="topo"><img src=../imagens/logo.png width="200"><br><br></div>');
$mpdf->SetHTMLFooter('<div class=rodape>
<table align=center class=rod width="100%">
<tr><td colspan=2 align=center></td></tr>
<tr><td colspan=2 align=right>{PAGENO} / {nbpg}</td></tr>
</table>
</div>');

$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'UTF-8';
$mpdf->WriteHTML($html);
$mpdf->SetImportUse();
$mpdf->Output('Malote_' . str_pad($mal_id, 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit();