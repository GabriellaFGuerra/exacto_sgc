<?php
require_once '../mod_includes/php/connect.php';
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Função para formatar a data em português
function formatarDataExtenso(DateTime $data): string
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

// Variáveis esperadas
$orc_id = $_POST['orc_id'] ?? '';
$orc_data_aprovacao = $_POST['orc_data_aprovacao'] ?? '';
$cli_nome_razao = $_POST['cli_nome_razao'] ?? '';
$tps_nome = $_POST['tps_nome'] ?? '';
$for_nome_razao = $_POST['for_nome_razao'] ?? '';
$sto_observacao = $_POST['sto_observacao'] ?? '';

$datap = formatarDataExtenso(new DateTime());

// PHPMailer
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

	// Destinatários (busca admins)
	$sql_admin = "SELECT usu_email FROM admin_usuarios WHERE usu_notificacao = 1 AND usu_email <> '' AND usu_status = 1";
	$stmt_admin = $pdo->query($sql_admin);
	$admins = $stmt_admin->fetchAll(PDO::FETCH_COLUMN);

	foreach ($admins as $email_admin) {
		$mail->addAddress($email_admin);
	}

	// Notificação no banco
	$not_nome = $_ENV['MAIL_SUBJECT'] ?? "Orçamento Aprovado";
	$not_obs = "
        <p>
            <b>N° Orçamento:</b> $orc_id<br>
            <b>Data de aprovação:</b> " . date('d/m/Y', strtotime($orc_data_aprovacao)) . "<br>
            <b>Cliente:</b> $cli_nome_razao<br>
            <b>Tipo de Serviço:</b> $tps_nome<br>
            <b>Fornecedor aprovado:</b> $for_nome_razao<br>
            <b>Observações:</b> <br>
            " . nl2br($sto_observacao) . "
        </p>
    ";

	$sql_not = "INSERT INTO notificacoes (not_nome, not_obs) VALUES (:not_nome, :not_obs)";
	$stmt_not = $pdo->prepare($sql_not);
	$stmt_not->execute([
		':not_nome' => $not_nome,
		':not_obs' => $not_obs
	]);

	// Email
	$mail->isHTML(true);
	$mail->Subject = $_ENV['MAIL_SUBJECT'] ?? "Orçamento Aprovado";
	$mail->Body = "
        <head>
        <style type='text/css'>
            .margem { padding-left:20px; padding-right:20px;}
            .margem2 { padding-top:20px; padding-bottom:20px; padding-left:20px; padding-right:20px;}
            a:link {}
            a:visited {}
            a:hover { text-decoration: underline; color:#F90F00;}
            a:active { text-decoration: none;}
            hr { color: #0066B3}
            .titulo { font-family:Calibri; color:#0066B3; font-size:15px; text-align:left; font-weight:normal; }
            .texto { font-family:Calibri; color:#666; font-size:13px; text-align:justify; font-weight:normal; }
            .interna { font-family:Calibri; color:#666; border: 1px dashed #CCC; line-height:20px; min-width:600px; font-size:13px; text-align:justify; font-weight:normal; }
            .interna td { padding:4px 10px; }
            .rodape { font-family:Calibri; color:#666; font-size:11px; text-align:justify; font-weight:normal; }
            .titulo_tabela { font-size:15px; color:#FFF; background:#666;}
            .verde { color:#81C566;}
            .azul { color:#0F72BD;}
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
                    Um orçamento foi aprovado pelo cliente no sistema.<br><br>
                    <span class='titulo'>
                        <b>Detalhes do orçamento</b>
                    </span><br>
                    <b>N° Orçamento:</b> $orc_id<br>
                    <b>Data de aprovação:</b> " . date('d/m/Y', strtotime($orc_data_aprovacao)) . "<br>
                    <b>Cliente:</b> $cli_nome_razao<br>
                    <b>Tipo de Serviço:</b> $tps_nome<br>
                    <b>Fornecedor aprovado:</b> $for_nome_razao<br>
                    <b>Observações:</b> <br>
                    " . nl2br($sto_observacao) . " <p>
                    <br>
                    <b>Atenciosamente,<br>
                    <span class=azul>{$_ENV['MAIL_SIGNATURE_COMPANY']}</span><br>
                    {$_ENV['MAIL_SIGNATURE_PHONE']}<br>
                    <span class=azul>{$_ENV['MAIL_SIGNATURE_SITE']}</span> <br><br>
                    </b>
                    <hr>
                    <span class='rodape'>
                        Enviado $datap<br><br>
                        As informações contidas nesta mensagem e nos arquivos anexados são para uso restrito, sendo seu sigilo protegido por lei, não havendo ainda garantia legal quanto à integridade de seu conteúdo. Caso não seja o destinatário, por favor desconsidere essa mensagem. O uso indevido dessas informações será tratado conforme as normas da empresa e a legislação em vigor.
                    </span>
                </td>
            </tr>
            </table>
        </body>
    ";

	$mail->send();

	echo "
        <script>
            abreMask(
                '<img src=../imagens/ok.png> Orçamento cadastrado com sucesso.<br>Aguarde o breve atendimento de nossa equipe e acompanhe o andamento do seu orçamento.<br><br>'+
                '<input value=\" Ok \" type=\"button\" class=\"close_janela\">'
            );
        </script>
    ";
} catch (Exception $e) {
	echo "
        <script>
            abreMask(
                '<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>'+
                '<input value=\" Ok \" type=\"button\" onclick=\"javascript:window.history.back();\">'
            );
        </script>
    ";
	echo "Informações do erro: {$mail->ErrorInfo}";
}
?>