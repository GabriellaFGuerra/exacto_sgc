<?php
session_start();
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php'; // Conexão PDO em $pdo
require_once __DIR__ . '/../vendor/autoload.php'; // Ajuste o caminho conforme necessário

// Função para formatar data de yyyy-mm-dd para dd/mm/yyyy
function formatarData($data)
{
    if (!$data)
        return '';
    $partes = explode('-', $data);
    if (count($partes) !== 3)
        return $data;
    return "{$partes[2]}/{$partes[1]}/{$partes[0]}";
}

// Função para obter parâmetro GET com valor padrão
function getParametro($nome, $padrao = '')
{
    return $_GET[$nome] ?? $padrao;
}

// Parâmetros recebidos via GET
$login = getParametro('login');
$n = getParametro('n');
$pagina = max(1, intval(getParametro('pagina', 1)));
$recId = getParametro('rec_id');

// Monta string de autenticação
$autenticacao = "&login=" . urlencode($login) . "&n=" . urlencode($n);

// Consulta SQL para buscar dados do recurso
$sql = "
    SELECT * FROM recurso_gerenciar 
    LEFT JOIN (
        infracoes_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    ) ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
    WHERE rec_id = :rec_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['rec_id' => $recId]);
$registro = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$registro) {
    die('Registro não encontrado.');
}

// Extrai dados do registro
$fotoCliente = $registro['cli_foto'] ?? '';
$nomeCliente = $registro['cli_nome_razao'] ?? '';
$assuntoRecurso = $registro['rec_assunto'] ?? '';
$descricaoRecurso = $registro['rec_descricao'] ?? '';
$cidadeInfracao = $registro['inf_cidade'] ?? '';
$dataInfracao = formatarData($registro['inf_data'] ?? '');
$proprietario = $registro['inf_proprietario'] ?? '';
$apartamento = $registro['inf_apto'] ?? '';
$bloco = $registro['inf_bloco'] ?? '';
$endereco = $registro['inf_endereco'] ?? '';
$email = $registro['inf_email'] ?? '';

// Gera HTML do documento
ob_start();
?>
<div class='laudo'>
    <?= htmlspecialchars($cidadeInfracao) ?>, <?= htmlspecialchars($dataInfracao) ?>
    <br><br>
    <b>Proprietário(a):</b> <?= htmlspecialchars($proprietario) ?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <b>Unidade:</b> <?= htmlspecialchars($apartamento) ?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <b>Bloco/Quadra:</b> <?= htmlspecialchars($bloco) ?>
    <br><br>
    <b>Endereço:</b> <?= htmlspecialchars($endereco) ?>
    <br><br>
    <b>Email:</b> <?= htmlspecialchars($email) ?>
    <br><br>
    <?= nl2br(htmlspecialchars($assuntoRecurso)) ?>
    <br><br>
    <?= nl2br(htmlspecialchars($descricaoRecurso)) ?>
    <br>
</div>
<?php
$htmlDocumento = ob_get_clean();

// Carrega CSS externo para o mPDF
$css = file_get_contents(__DIR__ . '/pdf.css');

// Instancia o mPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 20,
    'margin_bottom' => 20,
    'margin_header' => 5,
    'margin_footer' => 15,
    'orientation' => 'P'
]);

$mpdf->SetTitle('Exacto Adm | Imprimir Carta Deferimento/Indeferimento');
$mpdf->SetHTMLHeader(
    '<div class="topo2"><img src="' . htmlspecialchars($fotoCliente) . '" height="100"></div>'
);
$mpdf->SetHTMLFooter(
    '<div class="rodape">
        <table align="center" class="rod" width="100%">
            <tr>
                <td colspan="2" align="left">
                    <br>
                    Atenciosamente,
                    <br>
                    ' . htmlspecialchars($nomeCliente) . '
                </td>
            </tr>
        </table>
    </div>'
);

$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'UTF-8';

// Aplica o CSS
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

// Adiciona o conteúdo HTML
$mpdf->WriteHTML($htmlDocumento, \Mpdf\HTMLParserMode::HTML_BODY);

// Paginação automática do mPDF já é aplicada por padrão

$nomeArquivo = 'recurso_' . str_pad($recId, 6, '0', STR_PAD_LEFT) . '.pdf';
$mpdf->Output($nomeArquivo, 'I');
exit;