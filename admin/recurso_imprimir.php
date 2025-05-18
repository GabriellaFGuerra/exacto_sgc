<?php
session_start();
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

require_once '../mod_includes/php/connect.php'; // Conexão PDO em $pdo

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
$rec_id = $_GET['rec_id'] ?? '';

$sql = "
    SELECT * FROM recurso_gerenciar 
    LEFT JOIN (
        infracoes_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    ) ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
    WHERE rec_id = :rec_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['rec_id' => $rec_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Registro não encontrado.');
}

$rec_id = $row['rec_id'];
$cli_foto = $row['cli_foto'] ?? '';
$cli_nome_razao = $row['cli_nome_razao'] ?? '';
$rec_assunto = $row['rec_assunto'] ?? '';
$rec_descricao = $row['rec_descricao'] ?? '';
$inf_cidade = $row['inf_cidade'] ?? '';
$inf_data = isset($row['inf_data']) ? implode("/", array_reverse(explode("-", $row['inf_data']))) : '';
$inf_proprietario = $row['inf_proprietario'] ?? '';
$inf_apto = $row['inf_apto'] ?? '';
$inf_bloco = $row['inf_bloco'] ?? '';
$inf_endereco = $row['inf_endereco'] ?? '';
$inf_email = $row['inf_email'] ?? '';

ob_start();
?>
<div class='laudo'>
    <?= htmlspecialchars($inf_cidade) ?>, <?= htmlspecialchars($inf_data) ?>
    <br><br>
    <b>Proprietário(a):</b> <?= htmlspecialchars($inf_proprietario) ?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <b>Unidade:</b> <?= htmlspecialchars($inf_apto) ?>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <b>Bloco/Quadra:</b> <?= htmlspecialchars($inf_bloco) ?>
    <br>
    <br>
    <b>Endereço:</b> <?= htmlspecialchars($inf_endereco) ?>
    <br>
    <br>
    <b>Email:</b>
    <?= htmlspecialchars($inf_email) ?>
    <br>
    <br>
    <?= nl2br(htmlspecialchars($rec_assunto)) ?>
    <br>
    <br>
    <?= nl2br(htmlspecialchars($rec_descricao)) ?>
    <br>
</div>
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

$mpdf->SetTitle('Exacto Adm | Imprimir Carta Deferimento/Indeferimento');
$mpdf->useOddEven = false;
$mpdf->SetHTMLHeader(
    '<div class="topo2"><img src="' . htmlspecialchars($cli_foto) . '" height="100"></div><div class="topo2"></div><div class="topo2"></div>'
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

$mpdf->Output('recurso_' . str_pad($rec_id, 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit;