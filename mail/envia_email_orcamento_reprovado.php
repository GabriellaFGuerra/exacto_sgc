<?php
require_once __DIR__ . '/../mod_includes/php/connect.php';
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Função para obter data formatada em português
function getFormattedDate(): string
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

// Função para buscar emails dos administradores
function getAdminEmails(PDO $pdo): array
{
	$stmt = $pdo->prepare("SELECT usu_email FROM admin_usuarios WHERE usu_notificacao = 1 AND usu_email <> '' AND usu_status = 1");
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Função para inserir notificação
function insertNotification(PDO $pdo, string $nome, string $obs): void
{
	$stmt = $pdo->prepare("INSERT INTO notificacoes (not_nome, not_obs) VALUES (:nome, :obs)");
	$stmt->execute([':nome' => $nome, ':obs' => $obs]);
}


// Variáveis vindas de outro contexto (exemplo)
$orc_id = $_POST['orc_id'] ?? '';
$orc_data_reprovacao = $_POST['orc_data_reprovacao'] ?? '';
$cli_nome_razao = $_POST['cli_nome_razao'] ?? '';
$tps_nome = $_POST['tps_nome'] ?? '';
$sto_observacao = $_POST['sto_observacao'] ?? '';

// Monta corpo da notificação
$not_nome = "Orçamento Reprovado";
$not_obs = "
	<p>
		<b>N° Orçamento:</b> $orc_id<br>
		<b>Data de reprovação:</b> " . date('d/m/Y', strtotime($orc_data_reprovacao)) . "<br>
		<b>Cliente:</b> $cli_nome_razao<br>
		<b>Tipo de Serviço:</b> $tps_nome<br>
		<b>Observações:</b> <br>" . nl2br(htmlspecialchars($sto_observacao)) . "
	</p>
";

// Insere notificação
insertNotification($pdo, $not_nome, $not_obs);

// Busca emails dos administradores
$adminEmails = getAdminEmails($pdo);

// Configuração do PHPMailer
$mail = new PHPMailer(true);

try {
	$mail->isSMTP();
	$mail->Host = 'mail.sistemaexacto.com.br';
	$mail->SMTPAuth = true;
	$mail->Username = 'autenticacao@sistemaexacto.com.br';
	$mail->Password = 'info2012mogi';
	$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
	$mail->Port = 587;

	$mail->setFrom('noreply@sistemaexacto.com.br', 'ExactoAdm');
	$mail->addReplyTo('autenticacao@sistemaexacto.com.br', 'ExactoAdm');

	foreach ($adminEmails as $email) {
		$mail->addAddress($email);
	}

	$mail->isHTML(true);
	$mail->CharSet = 'UTF-8';
	$mail->Subject = 'Orçamento Reprovado';

	$datap = getFormattedDate();

	$mail->Body = "
		<head>
			<style type='text/css'>
				.titulo { font-family:Calibri; color:#0066B3; font-size:15px; text-align:left; font-weight:normal; }
				.texto { font-family:Calibri; color:#666; font-size:13px; text-align:justify; font-weight:normal; }
				.azul { color:#0F72BD;}
				.verde { color:#81C566;}
				.rodape { font-family:Calibri; color:#666; font-size:11px; text-align:justify; font-weight:normal; }
			</style>
		</head>
		<body>
			<table class='texto' align='center' border='0' width='100%' cellspacing='0' cellpadding='0'>
				<tr>
					<td align='left'>
						<span class='titulo'><b>Olá Administrador(es)</b></span><br><br>
						Um orçamento foi reprovado pelo cliente no sistema.<br><br>
						<span class='titulo'><b>Detalhes do orçamento</b></span><br>
						<b>N° Orçamento:</b> $orc_id<br>
						<b>Data de reprovação:</b> " . date('d/m/Y', strtotime($orc_data_reprovacao)) . "<br>
						<b>Cliente:</b> $cli_nome_razao<br>
						<b>Tipo de Serviço:</b> $tps_nome<br>
						<b>Observações:</b> <br>" . nl2br(htmlspecialchars($sto_observacao)) . "<br><br>
						<b>Atenciosamente,<br>
						<span class='azul'>Exa<span class='verde'>c</span>to</span> Assessoria e Administração<br>
						(11) <span class='verde'>4791-9220</span><br>
						<span class='azul'>www.exactoadm.com.br</span><br><br>
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

	echo json_encode([
		'success' => true,
		'message' => 'Orçamento cadastrado com sucesso. Aguarde o breve atendimento de nossa equipe e acompanhe o andamento do seu orçamento.'
	]);
} catch (Exception $e) {
	echo json_encode([
		'success' => false,
		'message' => 'Erro ao enviar o e-mail: ' . $mail->ErrorInfo
	]);
}