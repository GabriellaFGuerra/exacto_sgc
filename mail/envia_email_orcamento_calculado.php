<?php
require_once '../mod_includes/php/connect.php';
require_once '../vendor/autoload.php'; // PHPMailer via Composer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Função para obter data formatada em português
function dataFormatada(): string
{
	$dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
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
	$agora = new DateTime();
	$diaSemana = $dias[$agora->format('w')];
	$diaMes = $agora->format('j');
	$mes = $meses[(int) $agora->format('n')];
	$ano = $agora->format('Y');
	return "$diaSemana, $diaMes de $mes de $ano";
}

// Variáveis esperadas (exemplo, ajuste conforme necessário)
$orc_cliente = $_POST['orc_cliente'] ?? null;
$orc_id = $_POST['orc_id'] ?? null;
$tps_nome = $_POST['tps_nome'] ?? '';
$orc_observacoes = $_POST['orc_observacoes'] ?? '';
$cli_nome_razao = $_POST['cli_nome_razao'] ?? '';

// Busca e-mails dos clientes
$stmt = $pdo->prepare("SELECT cli_email FROM cadastro_clientes WHERE cli_email <> '' AND cli_id = :cli_id");
$stmt->execute(['cli_id' => $orc_cliente]);
$emails = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Notificação
$not_nome = "Orçamento Calculado";
$not_obs = "
	<p>
		O orçamento N° <b>$orc_id</b> foi calculado.<br>
		<b>Tipo de Serviço:</b> $tps_nome<br>
		<b>Observações:</b> <br> 
		" . nl2br($orc_observacoes) . "
	</p>
";
$stmt = $pdo->prepare("INSERT INTO notificacoes (not_nome, not_obs) VALUES (:not_nome, :not_obs)");
$stmt->execute(['not_nome' => $not_nome, 'not_obs' => $not_obs]);

// Monta o corpo do e-mail
$datap = dataFormatada();
$body = "
	<head>
		<style type='text/css'>
			.margem { padding-left:20px; padding-right:20px;}
			.margem2 { padding-top:20px; padding-bottom:20px; padding-left:20px; padding-right:20px;}
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
					<span class='titulo'><b>Olá $cli_nome_razao</b></span><br><br>
					O orçamento N° <b>$orc_id</b> foi calculado.<br><br>
					<span class='titulo'><b>Detalhes do orçamento</b></span><br>
					<b>Tipo de Serviço:</b> $tps_nome<br>
					<b>Observações:</b> <br> 
					" . nl2br($orc_observacoes) . " <p>
					<br>
					<a href='http://" . $_SERVER['HTTP_HOST'] . "/sistema/'>Clique aqui</a> para acessar o sistema e verificar o orçamento.
					<p><br>
					<b>Atenciosamente,<br>
					<span class='azul'>Exa<span class='verde'>c</span>to</span> Assessoria e Administração<br>
					(11) <span class='verde'>4791-9220</span><br>
					<span class='azul'>www.exactoadm.com.br</span> <br><br>
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

// Envio do e-mail
$mail = new PHPMailer(true);

try {
	// Configuração do servidor SMTP
	$mail->isSMTP();
	$mail->Host = 'mail.sistemaexacto.com.br';
	$mail->SMTPAuth = true;
	$mail->Username = 'autenticacao@sistemaexacto.com.br';
	$mail->Password = 'info2012mogi';
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port = 587;

	// Remetente
	$mail->setFrom('noreply@sistemaexacto.com.br', 'ExactoAdm');
	$mail->addReplyTo('autenticacao@sistemaexacto.com.br', 'ExactoAdm');

	// Destinatários
	foreach ($emails as $email) {
		$mail->addAddress($email);
	}

	// Conteúdo
	$mail->isHTML(true);
	$mail->Subject = 'Orçamento Calculado';
	$mail->Body = $body;

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