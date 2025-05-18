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
$autenticacao = '&login=' . urlencode($login) . '&n=' . urlencode($n);
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

function renderItens(array $itens)
{
    $c = 0;
    $html = '';
    foreach ($itens as $item) {
        $c1 = $c++ % 2 === 0 ? 'linhaimpar' : 'linhapar';
        $fornecedor = htmlspecialchars($item['mai_fornecedor']);
        $tipo_documento = htmlspecialchars($item['mai_tipo_documento']);
        $num_cheque = htmlspecialchars($item['mai_num_cheque']);
        $valor = 'R$ ' . number_format((float) $item['mai_valor'], 2, ',', '.');
        $data_vencimento = $item['mai_data_vencimento'] ? date('d/m/Y', strtotime($item['mai_data_vencimento'])) : '';
        $html .= '<tr class="' . $c1 . '">
            <td>' . $fornecedor . '</td>
            <td>' . $tipo_documento . '</td>
            <td>' . $num_cheque . '</td>
            <td>' . $valor . '</td>
            <td style="text-align:center;">' . $data_vencimento . '</td>
        </tr>';
    }
    return $html;
}

ob_start();
?>
<table align="center" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="left">
            <div class="laudo">
                <table class="laudo" align="center" cellspacing="0" cellpadding="3" width="1000">
                    <tr>
                        <td colspan="2" align="center">
                            <span class="titulo_laudo">Protocolo de Envio</span>
                            <br>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table class="bordatabela" cellspacing="0" cellpadding="3" width="1000">
                                <tr>
                                    <td colspan="4" height="60" class="label2" align="center">
                                        <?php echo htmlspecialchars($cli_nome_razao); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="20%" class="label" align="right">Data de Envio:</td>
                                    <td colspan="3"><?php echo $mal_data_cadastro; ?></td>
                                </tr>
                                <tr>
                                    <td width="20%" class="label" align="right">Malote N°:</td>
                                    <td><?php echo $mal_id; ?></td>
                                    <td width="20%" class="label" align="right">N° Lacre:</td>
                                    <td><?php echo htmlspecialchars($mal_lacre); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" valign="top">
                                        <table class="bordatabela" cellpadding="5" cellspacing="0" width="1000">
                                            <tr>
                                                <td class="titulo_tabela2">Fornecedor</td>
                                                <td class="titulo_tabela2">Tipo Documento</td>
                                                <td class="titulo_tabela2">N° Cheque</td>
                                                <td class="titulo_tabela2">Valor</td>
                                                <td class="titulo_tabela2" align="center">Data Vencimento</td>
                                            </tr>
                                            <?php echo renderItens($itens); ?>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center" class="italic">
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
<table align="center" border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align="left">
            <div class="laudo">
                <table class="laudo" align="center" cellspacing="0" cellpadding="3" width="1000">
                    <tr>
                        <td colspan="2">
                            <br>
                            <img src="../imagens/linha.png" alt="linha" />
                            <br>
                            <div class="topo">
                                <center><img src="../imagens/logo.png" width="200" alt="logo"></center><br><br>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <span class="titulo_laudo">Protocolo de Devolução</span>
                            <br>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <table class="bordatabela" cellspacing="0" cellpadding="3" width="1000">
                                <tr>
                                    <td colspan="4" height="60" class="label2" align="center">
                                        <?php echo htmlspecialchars($cli_nome_razao); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="20%" class="label" align="right">Data de Devolução:</td>
                                    <td colspan="3">_______/_______/____________</td>
                                </tr>
                                <tr>
                                    <td width="20%" class="label" align="right">Malote N°:</td>
                                    <td><?php echo $mal_id; ?></td>
                                    <td width="20%" class="label" align="right">N° Lacre:</td>
                                    <td><?php echo htmlspecialchars($mal_lacre); ?></td>
                                </tr>
                                <tr>
                                    <td colspan="4" valign="top">
                                        <table class="bordatabela" cellpadding="5" cellspacing="0" width="1000">
                                            <tr>
                                                <td class="titulo_tabela2">Fornecedor</td>
                                                <td class="titulo_tabela2">Tipo Documento</td>
                                                <td class="titulo_tabela2">N° Cheque</td>
                                                <td class="titulo_tabela2">Valor</td>
                                                <td class="titulo_tabela2" align="center">Data Vencimento</td>
                                            </tr>
                                            <?php echo renderItens($itens); ?>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center" class="italic">
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
    'margin_top' => 25,
    'margin_bottom' => 23,
    'margin_header' => 5,
    'margin_footer' => 5,
    'orientation' => 'P'
]);
$mpdf->SetTitle('Exacto Adm | Imprimir Malote');
$mpdf->useOddEven = false;
$mpdf->SetHTMLHeader('<div class="topo"><img src="../imagens/logo.png" width="200" alt="logo"><br><br></div>');
$mpdf->SetHTMLFooter('<div class="rodape">
<table align="center" class="rod" width="100%">
<tr><td colspan="2" align="center"></td></tr>
<tr><td colspan="2" align="right">{PAGENO} / {nbpg}</td></tr>
</table>
</div>');

$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'UTF-8';

// Inclui o CSS externo
$css = file_get_contents(__DIR__ . '/pdf.css');
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->Output('Malote_' . str_pad($mal_id, 6, '0', STR_PAD_LEFT) . '.pdf', 'I');