<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

// Impede acesso de setor não autorizado
if ($_SESSION['setor'] == 3) {
    echo "Processo não encontrado";
    exit;
}

// Inclui conexão PDO
require_once '../mod_includes/php/connect.php';

/**
 * Obtém parâmetro GET de forma segura
 */
function obterParametro($chave)
{
    return filter_input(INPUT_GET, $chave, FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
}

// Parâmetros recebidos
$login = obterParametro('login');
$n = obterParametro('n');
$pagina = obterParametro('pagina');
$chamadoId = (int) obterParametro('cha_id');

if (!$chamadoId) {
    echo "Chamado inválido";
    exit;
}

// Consulta SQL para buscar dados do chamado
$sql = "
SELECT c.*, 
       e.equ_tipo, e.equ_marca, e.equ_modelo, e.equ_num_serie, e.equ_num_pat, e.equ_nosso_num,
       t.tec_nome,
       u.uni_nome_razao, u.uni_cep, u.uni_bairro, u.uni_endereco, u.uni_numero, u.uni_comp, u.uni_telefone, u.uni_celular, u.uni_responsavel, u.uni_email,
       cli.cli_id, cli.cli_nome_razao,
       m.mun_nome,
       uf.uf_sigla,
       h1.stc_status
FROM cadastro_chamados c
LEFT JOIN cadastro_equipamentos e ON e.equ_id = c.cha_equipamento
LEFT JOIN cadastro_tecnicos t ON t.tec_id = c.cha_tecnico
LEFT JOIN cadastro_unidades u ON u.uni_id = c.cha_unidade
LEFT JOIN cadastro_clientes cli ON cli.cli_id = u.uni_cliente
LEFT JOIN end_municipios m ON m.mun_id = u.uni_municipio
LEFT JOIN end_uf uf ON uf.uf_id = u.uni_uf
LEFT JOIN cadastro_status_chamado h1 ON h1.stc_chamado = c.cha_id
WHERE h1.stc_id = (
    SELECT MAX(h2.stc_id) FROM cadastro_status_chamado h2 WHERE h2.stc_chamado = h1.stc_chamado
) AND c.cha_id = :cha_id
GROUP BY c.cha_id
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['cha_id' => $chamadoId]);
$dadosChamado = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$dadosChamado) {
    echo "Chamado não encontrado";
    exit;
}

// Extrai dados para variáveis
extract($dadosChamado);

// Verifica se é chamado avulso
$chamadoAvulso = empty($cha_equipamento) && ($cha_avul_tipo || $cha_avul_marca || $cha_avul_modelo || $cha_avul_num_serie) ? "Sim" : "Não";
if ($chamadoAvulso === "Sim") {
    $equ_tipo = $cha_avul_tipo;
    $equ_marca = $cha_avul_marca;
    $equ_modelo = $cha_avul_modelo;
    $equ_num_serie = $cha_avul_num_serie;
}

// Mapeamento de status
$statusChamado = [
    1 => "<span class='preto'>Em análise</span>",
    2 => "<span class='azul'>Aberto</span>",
    3 => "<span class='laranja'>Pendente</span>",
    4 => "<span class='verde'>Finalizado</span>",
    5 => "<span class='vermelho'>Cancelado</span>",
];
$statusAtual = $statusChamado[$stc_status] ?? '';

// Formatação de datas
$dataCadastro = date('d/m/Y', strtotime($cha_data ?? 'now'));
$horaCadastro = date('H:i', strtotime($cha_data ?? 'now'));

// Geração do HTML
ob_start();
?>
<table align='center' border='0' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='left'>
            <div class='laudo'>
                <table class='laudo' align='center' cellspacing='0' cellpadding='3' width='1000'>
                    <tr>
                        <td colspan='2' align='right'>
                            <span class='label'>Nº OS:</span> <?php echo htmlspecialchars($cha_ano . $cha_id); ?>
                            <?php if ($chamadoAvulso === "Sim")
                                echo " (chamado avulso)"; ?>
                            <br>
                            <span class='label'>Data:</span> <?php echo date("d/m/Y"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='2' align='left' class='formtitulo'>Dados da Unidade</td>
                    </tr>
                    <tr>
                        <td colspan='2' height='10'></td>
                    </tr>
                    <tr>
                        <td width='20%' class='label'>Unidade solicitante:</td>
                        <td><?php echo htmlspecialchars($uni_nome_razao); ?></td>
                    </tr>
                    <tr>
                        <td class='label'>Endereço:</td>
                        <td><?php echo htmlspecialchars("$uni_endereco, $uni_numero $uni_comp - $uni_bairro - $mun_nome/$uf_sigla - CEP: $uni_cep"); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class='label'>Telefone:</td>
                        <td><?php echo htmlspecialchars("$uni_telefone / $uni_celular"); ?></td>
                    </tr>
                    <tr>
                        <td class='label'>Contato:</td>
                        <td><?php echo htmlspecialchars($uni_responsavel); ?></td>
                    </tr>
                    <tr>
                        <td class='label'>Email:</td>
                        <td><?php echo htmlspecialchars($uni_email); ?></td>
                    </tr>
                    <tr>
                        <td class='label'>Cidade:</td>
                        <td><?php echo htmlspecialchars($mun_nome); ?></td>
                    </tr>
                    <tr>
                        <td colspan='3' align='left' class='formtitulo'>Dados do Equipamento</td>
                    </tr>
                    <tr>
                        <td colspan='2' height='10'></td>
                    </tr>
                    <tr>
                        <td class='label'>Equipamento:</td>
                        <td><?php echo htmlspecialchars($equ_tipo); ?></td>
                    </tr>
                    <tr>
                        <td class='label'>Marca:</td>
                        <td>
                            <?php echo htmlspecialchars($equ_marca); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <span class='label'>Modelo:</span> &nbsp;
                            <?php echo htmlspecialchars($equ_modelo); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class='label'>Número Série:</td>
                        <td>
                            <?php echo htmlspecialchars($equ_num_serie); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <span class='label'>Patrimônio:</span> &nbsp;
                            <?php echo htmlspecialchars($equ_num_pat); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <span class='label'>Nosso Número:</span> &nbsp;
                            <?php echo htmlspecialchars($equ_nosso_num); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class='label'>Problema identificado:</td>
                        <td><?php echo nl2br(htmlspecialchars($cha_descricao)); ?></td>
                    </tr>
                    <tr>
                        <td class='label'>Responsável pelas informações:</td>
                        <td><?php echo htmlspecialchars($cha_responsavel); ?></td>
                    </tr>
                    <tr>
                        <td colspan='2' align='left' class='formtitulo'>Causa</td>
                    </tr>
                    <tr>
                        <td colspan='2' height='10'></td>
                    </tr>
                    <tr>
                        <td class='label' colspan='2'>
                            <input type='checkbox'> Material
                            ____________________________________________________________________________________________
                            <p><br>
                                <input type='checkbox'> Mão de Obra
                                &nbsp;_______________________________________________________________________________________
                            <p><br>
                                <input type='checkbox'> Método
                                &nbsp;____________________________________________________________________________________________
                                <br>
                                <span class='label'>Data:</span> <?= date("d/m/Y") ?>
                        </td>
                        <p><br>
                            <input type='checkbox'> Meio Ambiente
                            ________________________________________________________________________________________olspan='2'
                            align='left' class='formtitulo'>Dados da Unidade
        </td>
        <p><br>
            <input type='checkbox'> Máquina
            ______________________________________________________________________________________________
        <p><br>
            <input type='checkbox'> Medida
            _______________________________________________________________________________________________dade
            solicitante:</td>
            </td>td><?= htmlspecialchars($uni_nome_razao) ?></td>
    </tr>>
    <tr>
        <td colspan='2' align='left' class='formtitulo'>Solução Aplicada</td>
        <td><?= htmlspecialchars("$uni_endereco, $uni_numero $uni_comp - $uni_bairro - $mun_nome/$uf_sigla - CEP: $uni_cep") ?>
    <tr>
        <td colspan='2' height='10'></td>
    </tr>
    </td>
    <tr>
        <td class='label' colspan='2'>
    </tr>lass='label'>Telefone:</td>
    <input type='checkbox'> Satisfatóriatd><?= htmlspecialchars("$uni_telefone / $uni_celular") ?></td>
    ___________________________________________________________________________________________>
    <p><br>
        <input type='checkbox'> Provisória
        &nbsp;__________________________________________________________________________________________td><?= htmlspecialchars($uni_responsavel) ?>
        </td>
    <p><br>>
        <input type='checkbox'> Não satisfatória
        _______________________________________________________________________________________
        </td>td><?= htmlspecialchars($uni_email) ?></td>
        </tr>>
</table>
<table class='laudo' align='center' cellspacing='0' cellpadding='3' width='1000'>
    <tr>td><?= htmlspecialchars($mun_nome) ?></td>
        <td class='label' valign='top'>Técnico Responsável:</td>>
        <td align='center'>
            ______________________________________________________________________<br>ulo'>Dados do Equipamento</td>
        <?= htmlspecialchars($tec_nome) ?>
        </td>
    </tr>
</table>
</div>
<div class='titulo_adm'></div>d>
</td>td><?= htmlspecialchars($equ_tipo) ?></td>
</tr>>
</table>
<?php
$html = ob_get_clean();

// Geração do PDF
require_once __DIR__ . '/vendor/autoload.php';
use Mpdf\Mpdf;

$mpdf = new Mpdf([
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 25,
    'margin_bottom' => 20,
    'margin_header' => 5,
    'margin_footer' => 5,
    'orientation' => 'P'
]);

$mpdf->SetHTMLHeader('
<div class="topo">
    <img src="../imagens/logo.png" width="200"><br><br>
    <img src="../imagens/linha.png" />
</div>
');
$mpdf->SetHTMLFooter('
<div class="rodape">
    <img src="../imagens/linha.png" />
    <table align="center" class="rod" width="100%">
        <tr>
            <td colspan="2" align="center">
                <br>
                <span class="azul">Peli</span><span class="vermelho">Serv</span> Equipamentos E Serviços Odonto-Médicos Ltda<br>
                Rua Capitão Antônio Bueno Rangel, 266 - Jardim Jaraguá - São Paulo/SP - CEP: 05158-440<br>
                Fone: (11) <span class="vermelho">3901-1000</span><br>
                Email: <span class="vermelho">pelisserv@pelisserv.com.br</span> | Site: <span class="vermelho">www.pelisserv.com.br</span><br>
            </td>
        </tr>
        <tr>
            <td colspan="2" align="right">
                {PAGENO} / {nbpg}
            </td>
        </tr>
    </table>
</div>
');
$mpdf->allow_charset_conversion = true;
$mpdf->charset_in = 'UTF-8';
// Inclui o CSS externo
$css = file_get_contents(__DIR__ . '/pdf.css');
$mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);

$mpdf->WriteHTML($html);
$mpdf->Output();
exit;