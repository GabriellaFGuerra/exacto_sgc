<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php';

// Recupera parâmetros da URL
$login = $_GET['login'] ?? '';
$n = $_GET['n'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$idInfracao = $_GET['inf_id'] ?? '';

if (!$idInfracao) {
    die('ID de infração não informado.');
}

// Consulta os dados da infração e do cliente
$sql = "
    SELECT ig.*, cc.cli_nome_razao, cc.cli_cnpj
    FROM infracoes_gerenciar ig
    LEFT JOIN cadastro_clientes cc ON cc.cli_id = ig.inf_cliente
    WHERE ig.inf_id = :inf_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['inf_id' => $idInfracao]);
$dadosInfracao = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dadosInfracao) {
    die('Infração não encontrada.');
}

// Extrai variáveis dos dados da infração
$clienteId = $dadosInfracao['inf_cliente'];
$nomeCondominio = $dadosInfracao['cli_nome_razao'];
$cnpjCondominio = $dadosInfracao['cli_cnpj'];
$anoInfracao = $dadosInfracao['inf_ano'];
$tipoInfracao = $dadosInfracao['inf_tipo'];
$cidadeInfracao = $dadosInfracao['inf_cidade'];
$dataInfracao = date('d/m/Y', strtotime($dadosInfracao['inf_data']));
$proprietario = $dadosInfracao['inf_proprietario'];
$apartamento = $dadosInfracao['inf_apto'];
$bloco = $dadosInfracao['inf_bloco'];
$endereco = $dadosInfracao['inf_endereco'];
$email = $dadosInfracao['inf_email'];
$descricaoIrregularidade = $dadosInfracao['inf_desc_irregularidade'];
$assunto = $dadosInfracao['inf_assunto'];
$descricaoArtigo = $dadosInfracao['inf_desc_artigo'];
$descricaoNotificacao = $dadosInfracao['inf_desc_notificacao'];

/**
 * Retorna o tipo de entrega formatado.
 */
function obterTipoEntrega(string $tipo): string
{
    return match ($tipo) {
        'Notificação de advertência por infração disciplinar' => 'Advertência por infração',
        'Multa por Infração Interna' => 'Multa por infração',
        'Notificação de ressarcimento' => 'Notificação de ressarcimento',
        'Comunicação interna' => 'Comunicação interna',
        default => $tipo
    };
}

/**
 * Gera o HTML do protocolo.
 */
function gerarProtocoloHtml(
    string $proprietario,
    string $data,
    int $id,
    string $ano,
    string $tipo,
    string $nomeCondominio,
    string $apartamento,
    string $bloco
): string {
    $tipoEntrega = obterTipoEntrega($tipo);
    $numeroProtocolo = str_pad($id, 3, "0", STR_PAD_LEFT) . "/$ano";
    return "
        <table class='bordatabela' cellspacing='0' cellpadding='5' width='1000'>
            <tr>
                <td colspan='4' height='60' class='label2' align='center'>
                    A/C $proprietario
                </td>
            </tr>
            <tr>
                <td width='20%' class='label' align='right'>Data entrega:</td>
                <td>$data</td>
                <td width='20%' class='label' align='right'>N°:</td>
                <td>$numeroProtocolo</td>
            </tr>
            <tr>
                <td class='label' align='right' valign='top'>Referente a entrega de:</td>
                <td colspan='3' valign='top'>$tipoEntrega</td>
            </tr>
            <tr>
                <td class='label' align='right' valign='top'>Nome do condomínio:</td>
                <td colspan='3' valign='top'>$nomeCondominio</td>
            </tr>
            <tr>
                <td class='label' align='right' height='100' valign='top'>Unidade:</td>
                <td valign='top'>$apartamento</td>
                <td class='label' align='right' valign='top'>Bloco/Quadra:</td>
                <td valign='top'>$bloco</td>
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

// Gera o conteúdo HTML do PDF
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
                            <?= gerarProtocoloHtml(
                                $proprietario,
                                $dataInfracao,
                                $idInfracao,
                                $anoInfracao,
                                $tipoInfracao,
                                $nomeCondominio,
                                $apartamento,
                                $bloco
                            ) ?>
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
                            <?= gerarProtocoloHtml(
                                $proprietario,
                                $dataInfracao,
                                $idInfracao,
                                $anoInfracao,
                                $tipoInfracao,
                                $nomeCondominio,
                                $apartamento,
                                $bloco
                            ) ?>
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

// Configuração do PDF
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

$nomeArquivo = 'Orcamento_' . str_pad($idInfracao, 6, '0', STR_PAD_LEFT) . '.pdf';
$mpdf->Output($nomeArquivo, 'I');
exit();