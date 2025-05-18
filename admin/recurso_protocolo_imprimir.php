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
$pagina = $_GET['pagina'] ?? '';
$rec_id = $_GET['rec_id'] ?? '';

$sql = "SELECT * FROM recurso_gerenciar 
    LEFT JOIN (infracoes_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente)
    ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
    WHERE rec_id = :rec_id";
$stmt = $pdo->prepare($sql);
$stmt->execute(['rec_id' => $rec_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Registro não encontrado.');
}

$rec_id = $row['rec_id'];
$inf_cliente = $row['inf_cliente'];
$cli_nome_razao = $row['cli_nome_razao'];
$cli_cnpj = $row['cli_cnpj'];
$inf_ano = $row['inf_ano'];
$inf_tipo = $row['inf_tipo'];
$inf_cidade = $row['inf_cidade'];
$inf_data = $row['inf_data'] ? date('d/m/Y', strtotime($row['inf_data'])) : '';
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
                            <table class="bordatabela" cellspacing="0" cellpadding="5" width="1000">
                                <tr>
                                    <td colspan="4" height="60" class="label2" align="center">
                                        A/C <?= htmlspecialchars($inf_proprietario) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="20%" class="label" align="right">
                                        Data entrega:
                                    </td>
                                    <td>
                                        <?= date('d/m/Y') ?>
                                    </td>
                                    <td width="20%" class="label" align="right"></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="label" align="right" valign="top">
                                        Referente a entrega de:
                                    </td>
                                    <td colspan="3" valign="top">
                                        Resultado de Recurso Apresentado
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label" align="right" valign="top">
                                        Nome do condomínio:
                                    </td>
                                    <td colspan="3" valign="top">
                                        <?= htmlspecialchars($cli_nome_razao) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label" align="right" height="100" valign="top">
                                        Unidade:
                                    </td>
                                    <td valign="top">
                                        <?= htmlspecialchars($inf_apto) ?>
                                    </td>
                                    <td class="label" align="right" valign="top">
                                        Bloco/Quadra:
                                    </td>
                                    <td valign="top">
                                        <?= htmlspecialchars($inf_bloco) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center" class="italic">
                                        Recebi em _______/_______/____________
                                        <br><br><br>
                                        ______________________________________________<br>
                                        Nome legível
                                    </td>
                                </tr>
                            </table>
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
                            <table class="bordatabela" cellspacing="0" cellpadding="5" width="1000">
                                <tr>
                                    <td colspan="4" height="60" class="label2" align="center">
                                        A/C <?= htmlspecialchars($inf_proprietario) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="20%" class="label" align="right">
                                        Data entrega:
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($inf_data) ?>
                                    </td>
                                    <td width="20%" class="label" align="right">
                                        N°:
                                    </td>
                                    <td>
                                        <?= str_pad($rec_id, 3, "0", STR_PAD_LEFT) ?>/<?= htmlspecialchars($inf_ano) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label" align="right" valign="top">
                                        Referente a entrega de:
                                    </td>
                                    <td colspan="3" valign="top">
                                        <?php
                                        switch ($inf_tipo) {
                                            case "Notificação de advertência por infração disciplinar":
                                                echo "Advertência por infração";
                                                break;
                                            case "Multa por Infração Interna":
                                                echo "Multa por infração";
                                                break;
                                            case "Notificação de ressarcimento":
                                                echo "Notificação de ressarcimento";
                                                break;
                                            case "Comunicação interna":
                                                echo "Comunicação interna";
                                                break;
                                            default:
                                                echo htmlspecialchars($inf_tipo);
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label" align="right" valign="top">
                                        Nome do condomínio:
                                    </td>
                                    <td colspan="3" valign="top">
                                        <?= htmlspecialchars($cli_nome_razao) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="label" align="right" height="100" valign="top">
                                        Unidade:
                                    </td>
                                    <td valign="top">
                                        <?= htmlspecialchars($inf_apto) ?>
                                    </td>
                                    <td class="label" align="right" valign="top">
                                        Bloco/Quadra:
                                    </td>
                                    <td valign="top">
                                        <?= htmlspecialchars($inf_bloco) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="4" align="center" class="italic">
                                        Recebi em _______/_______/____________
                                        <br><br><br>
                                        ______________________________________________<br>
                                        Nome legível
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

$mpdf->SetTitle('Exacto Adm | Imprimir Protocolo');
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

$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
$mpdf->Output('Protocolo_' . str_pad($rec_id, 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit;