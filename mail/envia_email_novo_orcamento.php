<?php
require_once '../mod_includes/php/connect.php';
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Função para formatar data em português
function formatarDataPtBr(): string
{
	setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'Portuguese_Brazil.1252');
	$date = new DateTime();
	$dias = [
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
	$diaSemana = $dias[(int) $date->format('w')];
	$diaMes = $date->format('d');
	$mes = $meses[(int) $date->format('n')];
	$ano = $date->format('Y');
	return "$diaSemana, $diaMes de $mes de $ano";
}

// Exibe mensagem de carregamento
echo "<script>
        abreMask('<img src=\"../imagens/carregando.gif\" border=\"0\"><p>Por favor aguarde, enviando email.</p>');
        blink('p');
      </script>";

// Dados do orçamento
$cli_nome_razao = $_POST['cli_nome_razao'] ?? '';
$orc_tipo_servico_cliente = $_POST['orc_tipo_servico_cliente'] ?? '';
$orc_observacoes = $_POST['orc_observacoes'] ?? '';

// Data formatada
$datap = formatarDataPtBr();

// Busca e-mails dos administradores
$stmt = $pdo->prepare("SELECT usu_email FROM admin_usuarios WHERE usu_notificacao = 1 AND usu_email <> '' AND usu_status = 1");
$stmt->execute();
$emailsAdmin = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Insere notificação no banco
$not_nome = "SGO - Novo Orçamento Realizado";
$not_obs = "<p><b>Cliente:</b> {$cli_nome_razao}<br><b>Tipo de Serviço:</b> {$orc_tipo_servico_cliente}<br><b>Observações:</b><br>" . nl2br($orc_observacoes) . "</p>";
$stmt = $pdo->prepare("INSERT INTO notificacoes (not_nome, not_obs) VALUES (:not_nome, :not_obs)");
$stmt->execute([':not_nome' => $not_nome, ':not_obs' => $not_obs]);

// Monta corpo do e-mail
$orc_observacoes_html = nl2br($orc_observacoes);
$body = <<<HTML
<head>
<meta charset="UTF-8">
<style>
    .titulo { font-family:Calibri; color:#0066B3; font-size:15px; text-align:left; font-weight:normal; }
    .texto { font-family:Calibri; color:#666; font-size:13px; text-align:justify; font-weight:normal; }
    .rodape { font-family:Calibri; color:#666; font-size:11px; text-align:justify; font-weight:normal; }
    .azul { color:#0F72BD;}
    .verde { color:#81C566;}
</style>
</head>
<body>
    <table class='texto' align='center' border='0' width='100%' cellspacing='0' cellpadding='0'>
        <tr>
            <td align='left'>
                <span class='titulo'><b>Olá Administrador(es)</b></span><br><br>
                Um novo orçamento foi solicitado no sistema.<br><br>
                <span class='titulo'><b>Detalhes do orçamento</b></span><br>
                <b>Cliente:</b> {$cli_nome_razao}<br>
                <b>Tipo de Serviço:</b> {$orc_tipo_servico_cliente}<br>
                <b>Observações:</b> <br> {$orc_observacoes_html} <p>
                <br>
                <b>Atenciosamente,<br>
                <span class='azul'>Exa<span class='verde'>c</span>to</span> Assessoria e Administração<br>
                (11) <span class='verde'>4791-9220</span><br>
                <span class='azul'>www.exactoadm.com.br</span><br><br></b>
                <hr>
                <span class='rodape'>Enviado {$datap}<br><br>
                As informações contidas nesta mensagem e nos arquivos anexados são para uso restrito...</span>
            </td>
        </tr>
    </table>
</body>
HTML;

// Configuração do PHPMailer
$mail = new PHPMailer(true);

try {
	// Configuração SMTP
	$mail->isSMTP();
	$mail->Host = 'mail.sistemaexacto.com.br';
	$mail->SMTPAuth = true;
	$mail->Username = 'autenticacao@sistemaexacto.com.br';
	$mail->Password = 'info2012mogi';
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port = 587;

	// Configuração do remetente e destinatários
	$mail->setFrom('noreply@sistemaexacto.com.br', 'ExactoAdm');
	$mail->addReplyTo('autenticacao@sistemaexacto.com.br', 'ExactoAdm');

	foreach ($emailsAdmin as $email) {
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$mail->addAddress($email);
		}
	}

	// Conteúdo do e-mail
	$mail->isHTML(true);
	$mail->Subject = 'SGO - Novo Orçamento Realizado';
	$mail->Body = $body;

	$mail->send();

	echo "<script>
            abreMask('<img src=../imagens/ok.png> Orçamento cadastrado com sucesso.<br>Aguarde o breve atendimento de nossa equipe.<br><br>
            <input value=\" Ok \" type=\"button\" class=\"close_janela\">');
          </script>";
} catch (Exception $e) {
	echo "<script>
            abreMask('<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>
            <input value=\" Ok \" type=\"button\" onclick=\"javascript:window.history.back();\">');
          </script>";
	echo "Erro ao enviar e-mail: {$mail->ErrorInfo}";
}
?>