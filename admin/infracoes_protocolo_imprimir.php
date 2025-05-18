<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php';

$login = $_GET['login'] ?? '';
$n = $_GET['n'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$inf_id = $_GET['inf_id'] ?? '';

if (!$inf_id) {
    die('ID de infração não informado.');
}

$stmt = $pdo->prepare("
    SELECT ig.*, cc.cli_nome_razao, cc.cli_cnpj
    FROM infracoes_gerenciar ig
    LEFT JOIN cadastro_clientes cc ON cc.cli_id = ig.inf_cliente
    WHERE ig.inf_id = :inf_id
");
$stmt->execute(['inf_id' => $inf_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Infração não encontrada.');
}

// Variáveis
$inf_id = $row['inf_id'];
$inf_cliente = $row['inf_cliente'];
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

// Função para tipo de entrega
function tipoEntrega($tipo)
{
    return match ($tipo) {
        'Notificação de advertência por infração disciplinar' => 'Advertência por infração',
        'Multa por Infração Interna' => 'Multa por infração',
        'Notificação de ressarcimento' => 'Notificação de ressarcimento',
        'Comunicação interna' => 'Comunicação interna',
        default => $tipo
    };
}

function protocoloHtml($inf_proprietario, $inf_data, $inf_id, $inf_ano, $inf_tipo, $cli_nome_razao, $inf_apto, $inf_bloco)
{
    $tipo = tipoEntrega($inf_tipo);
    $numero = str_pad($inf_id, 3, "0", STR_PAD_LEFT) . "/$inf_ano";
    return "
    <table class='bordatabela' cellspacing='0' cellpadding='5' width='1000'>
        <tr>
            <td colspan='4' height='60' class='label2' align='center'>
                A/C $inf_proprietario
            </td>
        </tr>
        <tr>
            <td width='20%' class='label' align='right'>Data entrega:</td>
            <td>$inf_data</td>
            <td width='20%' class='label' align='right'>N°:</td>
            <td>$numero</td>
        </tr>
        <tr>
            <td class='label' align='right' valign='top'>Referente a entrega de:</td>
            <td colspan='3' valign='top'>$tipo</td>
        </tr>
        <tr>
            <td class='label' align='right' valign='top'>Nome do condomínio:</td>
            <td colspan='3' valign='top'>$cli_nome_razao</td>
        </tr>
        <tr>
            <td class='label' align='right' height='100' valign='top'>Unidade:</td>
            <td valign='top'>$inf_apto</td>
            <td class='label' align='right' valign='top'>Bloco/Quadra:</td>
            <td valign='top'>$inf_bloco</td>
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
    ";
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
                            <span class="titulo_laudo">Protocolo</span>
                            <br>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?= protocoloHtml($inf_proprietario, $inf_data, $inf_id, $inf_ano, $inf_tipo, $cli_nome_razao, $inf_apto, $inf_bloco) ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <div class="rodape">
                                <table align="center" class="rod" width="100%">
                                    <tr>
                                        <td colspan="2" align="center">
                                            <br>
                                            <span class="azul">Exacto Assessoria e Administração</span><br>
                                            Rua Prof. Emilio Augusto Ferreira, 32 - Vila Oliveira, Mogi das
                                            Cruzes/SP<br>
                                            Fone: (11) <span class="verde">4791-9220</span><br>
                                            Email: <span class="azul">exacto@exactoadm.com.br</span> | Site: <span
                                                class="azul">www.exactoadm.com.br</span><br>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <br>
                            <img src="../imagens/linha.png" />
                            <br>
                            <div class="topo">
                                <center><img src="../imagens/logo.png" width="200"></center><br><br>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" align="center">
                            <span class="titulo_laudo">Protocolo</span>
                            <br>&nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?= protocoloHtml($inf_proprietario, $inf_data, $inf_id, $inf_ano, $inf_tipo, $cli_nome_razao, $inf_apto, $inf_bloco) ?>
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

// Inclui o CSS externo
$css = file_get_contents(__DIR__ . '/pdf.css');
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'UTF-8';
$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

$mpdf->Output('Orcamento_' . str_pad($inf_id, 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit();