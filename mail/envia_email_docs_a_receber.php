<?php
require_once '../mod_includes/php/connect.php';
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Função para formatar a data por extenso em português
function dataPorExtenso(DateTime $data): string
{
	$diasSemana = [
		'Domingo',
		'Segunda-feira',
		'Terça-feira',
		'Quarta-feira',
		'Quinta-feira',
		'Sexta-feira',
		'Sábado'
	];
	$meses = [
		1 => 'Janeiro',
		2 => 'Fevereiro',
		3 => 'Março',
		4 => 'Abril',
		5 => 'Maio',
		6 => 'Junho',
		7 => 'Julho',
		8 => 'Agosto',
		9 => 'Setembro',
		10 => 'Outubro',
		11 => 'Novembro',
		12 => 'Dezembro'
	];
	$diaSemana = $diasSemana[(int) $data->format('w')];
	$dia = $data->format('d');
	$mes = $meses[(int) $data->format('n')];
	$ano = $data->format('Y');
	return "{$diaSemana}, {$dia} de {$mes} de {$ano}";
}

// Exibe máscara de carregando (JS)
echo <<<HTML
<script>
    abreMask('<img src="../imagens/carregando.gif" border="0"><p>Por favor aguarde, enviando email.</p>');
    blink('p');
</script>
HTML;

// Data atual
$hoje = new DateTime();
$dataPorExtenso = dataPorExtenso($hoje);

// Busca emails dos administradores
$sql = "SELECT usu_email FROM admin_usuarios WHERE usu_notificacao = 1 AND usu_email <> '' AND usu_status = 1";
$stmt = $pdo->query($sql);
$emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Dados dos documentos (exemplo, substitua pelos dados reais)
$tipo_doc = $tipo_doc ?? '';
$clientes = $clientes ?? '';
$data_emissao = $data_emissao ?? '';
$periodicidade = $periodicidade ?? '';
$data_vencimento = $data_vencimento ?? '';
$data_venc_formatada = $hoje->format('d/m/Y');

// Monta corpo do email usando variáveis do .env
$body = <<<HTML
<head>
<style type='text/css'>
.margem 	{ padding-left:20px; padding-right:20px;}
.margem2 	{ padding-top:20px; padding-bottom:20px; padding-left:20px; padding-right:20px;}
a:link 		{}
a:visited 	{}
a:hover 	{ text-decoration: underline; color:#F90F00;}
a:active 	{ text-decoration: none;}
hr 			{ color: #0066B3}
.titulo		{ font-family:Calibri; color:#0066B3; font-size:15px; text-align:left; font-weight:normal; } 
.texto		{ font-family:Calibri; color:#666; font-size:13px; text-align:justify; font-weight:normal; }
.interna	{ font-family:Calibri; color:#666; border: 1px solid #EEE; line-height:20px; min-width:600px; font-size:13px; text-align:justify; font-weight:normal; }
.interna td	{ padding:4px 10px; }
.rodape		{ font-family:Calibri; color:#666; font-size:11px; text-align:justify; font-weight:normal; }
.titulo_tabela	{ font-size:15px; color:#FFF; background:#666;}
.verde			{ color:#81C566;}
.azul			{ color:#0F72BD;}
.vermelho { color: #DC202B;}
.cinza { color: #A3A5A8;}
</style>
</head>
<body class='fundo'>
    <table class='texto' align='center' border='0' width='100%' cellspacing='0' cellpadding='0'>
    <tr>
        <td align='left'>
            <span class='titulo'>
                <b>Olá Administrador(es) </b>
            </span><br><br>
            Os seguintes documentos vencerão em {$data_venc_formatada}.<br><br>
            <table class='interna' cellspacing='0'>
            <tr>
                <td class='titulo_tabela'>Tipo Doc</td>
                <td class='titulo_tabela'>Cliente</td>
                <td class='titulo_tabela'>Data Emissão</td>
                <td class='titulo_tabela'>Periodicidade</td>
                <td class='titulo_tabela'>Data Venc.</td>
            </tr>
            <tr>
                <td><b>{$tipo_doc}</b></td>
                <td><b>{$clientes}</b></td>
                <td><b>{$data_emissao}</b></td>
                <td><b>{$periodicidade}</b></td>
                <td class='vermelho'><b>{$data_vencimento}</b></td>								
            </tr>
            </table>
            <br>
            <b>Atenciosamente,<br>
            <span class=azul>{$_ENV['MAIL_SIGNATURE_COMPANY']}</span><br>
            {$_ENV['MAIL_SIGNATURE_PHONE']}<br>
            <span class=azul>{$_ENV['MAIL_SIGNATURE_SITE']}</span> <br><br>
            </b>
            <hr>
            <span class='rodape'>
                Enviado {$dataPorExtenso}<br><br>
                As informações contidas nesta mensagem e nos arquivos anexados são para uso restrito, sendo seu sigilo protegido por lei, não havendo ainda garantia legal quanto à integridade de seu conteúdo. Caso não seja o destinatário, por favor desconsidere essa mensagem. O uso indevido dessas informações será tratado conforme as normas da empresa e a legislação em vigor.
            </span>
        </td>
    </tr>
    </table>
</body>
HTML;

// Configuração do PHPMailer
$mail = new PHPMailer(true);

try {
	// Configurações do servidor SMTP usando variáveis do .env
	$mail->isSMTP();
	$mail->Host = $_ENV['MAIL_HOST'];
	$mail->SMTPAuth = true;
	$mail->Username = $_ENV['MAIL_USERNAME'];
	$mail->Password = $_ENV['MAIL_PASSWORD'];
	$mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
	$mail->Port = $_ENV['MAIL_PORT'];

	// Remetente
	$mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
	$mail->addReplyTo($_ENV['MAIL_REPLYTO'], $_ENV['MAIL_REPLYTO_NAME']);

	// Destinatários
	foreach ($emails as $email) {
		$mail->addAddress($email);
	}

	// Conteúdo do email
	$mail->isHTML(true);
	$mail->Subject = $_ENV['MAIL_SUBJECT'];
	$mail->Body = $body;

	$mail->send();
	// Email enviado com sucesso
} catch (Exception $e) {
	echo "Erro ao enviar email: {$mail->ErrorInfo}";
}
?>