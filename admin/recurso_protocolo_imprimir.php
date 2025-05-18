<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Mpdf\Mpdf;

// Função para obter os dados do recurso
function buscarDadosRecurso($pdo, $idRecurso)
{
    $sql = "SELECT * FROM recurso_gerenciar 
        LEFT JOIN (infracoes_gerenciar 
            LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente)
        ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
        WHERE rec_id = :rec_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['rec_id' => $idRecurso]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para formatar o tipo de infração
function formatarTipoInfracao($tipo)
{
    switch ($tipo) {
        case "Notificação de advertência por infração disciplinar":
            return "Advertência por infração";
        case "Multa por Infração Interna":
            return "Multa por infração";
        case "Notificação de ressarcimento":
            return "Notificação de ressarcimento";
        case "Comunicação interna":
            return "Comunicação interna";
        default:
            return htmlspecialchars($tipo);
    }
}

// Função para gerar o HTML do protocolo
function gerarHtmlProtocolo($dados)
{
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
                                            A/C <?= htmlspecialchars($dados['inf_proprietario']) ?>
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
                                            <?= htmlspecialchars($dados['cli_nome_razao']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label" align="right" height="100" valign="top">
                                            Unidade:
                                        </td>
                                        <td valign="top">
                                            <?= htmlspecialchars($dados['inf_apto']) ?>
                                        </td>
                                        <td class="label" align="right" valign="top">
                                            Bloco/Quadra:
                                        </td>
                                        <td valign="top">
                                            <?= htmlspecialchars($dados['inf_bloco']) ?>
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
                                            A/C <?= htmlspecialchars($dados['inf_proprietario']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="20%" class="label" align="right">
                                            Data entrega:
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($dados['inf_data']) ?>
                                        </td>
                                        <td width="20%" class="label" align="right">
                                            N°:
                                        </td>
                                        <td>
                                            <?= str_pad($dados['rec_id'], 3, "0", STR_PAD_LEFT) ?>/<?= htmlspecialchars($dados['inf_ano']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label" align="right" valign="top">
                                            Referente a entrega de:
                                        </td>
                                        <td colspan="3" valign="top">
                                            <?= formatarTipoInfracao($dados['inf_tipo']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label" align="right" valign="top">
                                            Nome do condomínio:
                                        </td>
                                        <td colspan="3" valign="top">
                                            <?= htmlspecialchars($dados['cli_nome_razao']) ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="label" align="right" height="100" valign="top">
                                            Unidade:
                                        </td>
                                        <td valign="top">
                                            <?= htmlspecialchars($dados['inf_apto']) ?>
                                        </td>
                                        <td class="label" align="right" valign="top">
                                            Bloco/Quadra:
                                        </td>
                                        <td valign="top">
                                            <?= htmlspecialchars($dados['inf_bloco']) ?>
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
    return ob_get_clean();
}

// Parâmetros recebidos via GET
$login = $_GET['login'] ?? '';
$numero = $_GET['n'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$idRecurso = $_GET['rec_id'] ?? '';

// Busca os dados do recurso
$dadosRecurso = buscarDadosRecurso($pdo, $idRecurso);

if (!$dadosRecurso) {
    die('Registro não encontrado.');
}

// Formata a data da infração
$dadosRecurso['inf_data'] = $dadosRecurso['inf_data'] ? date('d/m/Y', strtotime($dadosRecurso['inf_data'])) : '';

// Gera o HTML do protocolo
$htmlProtocolo = gerarHtmlProtocolo($dadosRecurso);

// Configuração do PDF
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

// Adiciona o conteúdo HTML ao PDF
$mpdf->WriteHTML($htmlProtocolo, \Mpdf\HTMLParserMode::HTML_BODY);

// Gera o PDF para visualização
$mpdf->Output('Protocolo_' . str_pad($dadosRecurso['rec_id'], 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit;
