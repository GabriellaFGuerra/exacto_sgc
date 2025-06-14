<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php';

// Array de meses em português
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

// Função para obter parâmetro GET com valor padrão
function obterParametro($nome, $padrao = '')
{
    return $_GET[$nome] ?? $padrao;
}

// Parâmetros recebidos via GET
$login = obterParametro('login');
$n = obterParametro('n');
$pagina = obterParametro('pagina');
$infId = obterParametro('inf_id');

// Autenticação para uso futuro
$autenticacao = "&login=$login&n=" . urlencode($n);

// Consulta dos dados da infração e cliente
$sql = "
    SELECT ig.*, cc.cli_foto, cc.cli_nome_razao, cc.cli_cnpj 
    FROM infracoes_gerenciar ig
    LEFT JOIN cadastro_clientes cc ON cc.cli_id = ig.inf_cliente
    WHERE ig.inf_id = :inf_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['inf_id' => $infId]);
$dados = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dados) {
    exit('Infração não encontrada.');
}

// Extração dos dados
$infId = $dados['inf_id'];
$clienteId = $dados['inf_cliente'];
$fotoCliente = $dados['cli_foto'];
$nomeCliente = $dados['cli_nome_razao'];
$cnpjCliente = $dados['cli_cnpj'];
$ano = $dados['inf_ano'];
$tipo = $dados['inf_tipo'];
$cidade = $dados['inf_cidade'];
$data = date('d/m/Y', strtotime($dados['inf_data']));
$proprietario = $dados['inf_proprietario'];
$apartamento = $dados['inf_apto'];
$bloco = $dados['inf_bloco'];
$endereco = $dados['inf_endereco'];
$email = $dados['inf_email'];
$descricaoIrregularidade = $dados['inf_desc_irregularidade'];
$assunto = $dados['inf_assunto'];
$descricaoArtigo = $dados['inf_desc_artigo'];
$descricaoNotificacao = $dados['inf_desc_notificacao'];

// Geração do HTML do PDF
ob_start();
?>
<style>
.laudo {
    font-family: Arial, sans-serif;
    font-size: 13px;
}

.bordatabela {
    border: 1px solid #333;
    border-collapse: collapse;
    width: 100%;
}

.bordatabela td,
.bordatabela th {
    border: 1px solid #333;
    padding: 8px;
}

.label {
    background: #f0f0f0;
    font-weight: bold;
}

.titulo_adm {
    margin-top: 30px;
}

.topo2 {
    text-align: center;
    font-size: 16px;
    font-weight: bold;
}

.rodape {
    font-size: 12px;
}

.rod {
    border: none;
}
</style>
<div class="laudo">
    <table class="bordatabela" cellspacing="0" cellpadding="5">
        <tr>
            <td colspan="3" class="label" align="left">
                <?= $cidade ?>, <?= $data ?>
            </td>
        </tr>
        <tr>
            <td align="left" valign="top">
                <b>Proprietário(a):</b> <?= $proprietario ?>
            </td>
            <td align="left" valign="top">
                <b>Unidade:</b> <?= $apartamento ?>
            </td>
            <td align="left" valign="top">
                <b>Bloco/Quadra:</b> <?= $bloco ?>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="left">
                <b>Endereço:</b> <?= $endereco ?>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="left">
                <b>Email:</b> <?= $email ?>
            </td>
        </tr>
    </table>
    <br>
    <table class="bordatabela" cellspacing="0" cellpadding="5">
        <tr>
            <td align="left">
                <b>Assunto:</b> <?= $assunto ?>
            </td>
        </tr>
    </table>
    <br>
    <table class="bordatabela" cellspacing="0" cellpadding="5">
        <tr>
            <td colspan="3" class="label" align="left">
                Descrição da irregularidade / ocorrência, data e hora:
            </td>
        </tr>
        <tr>
            <td colspan="3" align="left" valign="top">
                <?= nl2br($descricaoIrregularidade) ?>
            </td>
        </tr>
    </table>
    <br>
    <table class="bordatabela" cellspacing="0" cellpadding="5">
        <tr>
            <td colspan="3" class="label" align="left">
                Descrição do(s) artigo(s) que regulam o assunto:
            </td>
        </tr>
        <tr>
            <td colspan="3" align="left" valign="top">
                <?= nl2br($descricaoArtigo) ?>
            </td>
        </tr>
    </table>
    <br>
    <table class="bordatabela" cellspacing="0" cellpadding="5">
        <tr>
            <td colspan="3" class="label" align="left">
                Notificação Disciplinar:
            </td>
        </tr>
        <tr>
            <td colspan="3" align="left" valign="top">
                <?= nl2br($descricaoNotificacao) ?>
            </td>
        </tr>
    </table>
    <br><br>
</div>
<div class="titulo_adm"></div>
<?php
$html = ob_get_clean();

// Configuração do PDF
require_once __DIR__ . '/../vendor/autoload.php';
use Mpdf\Mpdf;

$mpdf = new Mpdf([
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
]);

$cabecalho = '
    <div class="topo2"><img src="' . $fotoCliente . '" height="100"></div>
    <div class="topo2"><br>' . $tipo . '<br><span class="cliente">' . $nomeCliente . '</span></div>
    <div class="topo2"><br>Nº. ' . str_pad($infId, 3, "0", STR_PAD_LEFT) . '/' . $ano . '</div>
';
$mpdf->SetHTMLHeader($cabecalho);

// Rodapé do PDF
$rodape = '
    <div class="rodape">
        <table align="center" class="rod" width="100%">
            <tr>
                <td colspan="2" align="left">
                    <br>
                    Atenciosamente,
                    <br>
                    ' . $nomeCliente . '
                </td>
            </tr>
        </table>
    </div>
';
$mpdf->SetHTMLFooter($rodape);

$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'UTF-8';

// Inclusão do CSS externo
$css = file_get_contents(__DIR__ . '/pdf.css');
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

// Geração do PDF
$mpdf->WriteHTML($html);
$nomeArquivo = 'Infração_' . str_pad($infId, 6, '0', STR_PAD_LEFT) . '.pdf';
$mpdf->Output($nomeArquivo, 'I');
exit;