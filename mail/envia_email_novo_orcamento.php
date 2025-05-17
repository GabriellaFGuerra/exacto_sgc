<script>
	abreMask('<img src="../imagens/carregando.gif" border="0"><p>Por favor aguarde, enviando email.</p>');
	blink('p');
</script>
<?php
// CONFERIR DATA
		$agora = time();
		$data = getdate($agora);
		$dia_semana = $data[wday];
		$dia_mes = $data[mday];
		$mes = $data[mon];
		$ano = $data[year];
		switch ($dia_semana)
		{
	        case 0:
                $dia_semana = "Domingo";
	        break;
	        case 1:
                $dia_semana = "Segunda-feira";
	        break;
	        case 2:
                $dia_semana = "Terça-feira";
	        break;
	        case 3:
                $dia_semana = "Quarta-feira";
	        break;
	        case 4:
                $dia_semana = "Quinta-feira";
	        break;
	        case 5:
                $dia_semana = "Sexta-feira";
	        break;
	        case 6:
                $dia_semana = "Sábado";
	        break;
		}
		switch ($mes)
		{
        	case 1:
                $mes = "Janeiro";
        	break;
        	case 2:
                $mes = "Fevereiro";
        	break;
        	case 3:
                $mes = "Março";
        	break;
        	case 4:
                $mes = "Abril";
        	break;
        	case 5:
                $mes = "Maio";
        	break;
        	case 6:
                $mes = "Junho";
        	break;
        	case 7:
                $mes = "Julho";
        	break;
        	case 8:
                $mes = "Agosto";
        	break;
        	case 9:
                $mes = "Setembro";
        	break;
        	case 10:
                $mes = "Outubro";
        	break;
        	case 11:
                $mes = "Novembro";
        	break;
        	case 12:
                $mes = "Dezembro";
 			break;
		}
		$datap = $dia_semana.', '.$dia_mes.' de '.$mes.' de '.$ano;


// Inclui o arquivo class.phpmailer.php localizado na pasta phpmailer
require_once("../mod_includes/php/phpmailer/class.phpmailer.php");
 
// Inicia a classe PHPMailer
$mail = new PHPMailer();
// Define os dados do servidor e tipo de conexão
// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$mail->IsSMTP();
$mail->Host = "mail.sistemaexacto.com.br"; // Endereço do servidor SMTP (caso queira utilizar a autenticação, utilize o host smtp.seudomínio.com.br)
$mail->SMTPAuth = true; // Usa autenticação SMTP? (opcional)
$mail->Username = 'autenticacao@sistemaexacto.com.br'; // Usuário do servidor SMTP
$mail->Password = 'info2012mogi'; // Senha do servidor SMTP


// Define o remetente
// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$mail->From = "noreply@sistemaexacto.com.br"; // Seu e-mail
$mail->Sender = "autenticacao@sistemaexacto.com.br"; // Seu e-mail
$mail->FromName = "ExactoAdm"; // Seu nome
$mail->SMTPDebug = 1;

 
// Define os destinatário(s)
// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
//$mail->AddAddress('gustavo@mogicomp.com.br');

$sql_admin = "SELECT * FROM admin_usuarios
			  WHERE usu_notificacao = 1 AND usu_email <> '' AND usu_status = 1 ";
$query_admin = mysql_query($sql_admin,$conexao);
$rows_admin = mysql_num_rows($query_admin);
if($rows_admin > 0)
{
	for($x=0; $x<$rows_admin; $x++)
	{
		$email_admin = mysql_result($query_admin,$x,'usu_email');
		$mail->AddAddress($email_admin);
	}
}



$not_nome = "SGO - Novo Orçamento Realizado";
$not_obs = "
            <p><b>Cliente:</b> $cli_nome_razao<br>
			<b>Tipo de Serviço:</b> $orc_tipo_servico_cliente<br>
			<b>Observações:</b> <br> 
			".nl2br($orc_observacoes)."</p>
";

$sql_not = "INSERT INTO notificacoes (
	not_nome,
	not_obs
	) 
	VALUES 
	(
	'$not_nome',
	'$not_obs'
	)";
	
mysql_query($sql_not,$conexao);



// Define os dados técnicos da Mensagem
// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$mail->IsHTML(true); // Define que o e-mail será enviado como HTML

//$mail->CharSet = 'iso-8859-1'; // Charset da mensagem (opcional)
 
// Define a mensagem (Texto e Assunto)
// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
$mail->Subject  = "".utf8_decode("SGO - Novo Orçamento Realizado").""; // Assunto da mensagem

$mail->Body = utf8_decode("
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
				.interna	{ font-family:Calibri; color:#666; border: 1px dashed #CCC; line-height:20px; min-width:600px; font-size:13px; text-align:justify; font-weight:normal; }
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
							Um novo orçamento foi solicitado no sistema.<br><br>
							<span class='titulo'>
								<b>Detalhes do orçamento</b>
							</span><br>
							<b>Cliente:</b> $cli_nome_razao<br>
							<b>Tipo de Serviço:</b> $orc_tipo_servico_cliente<br>
							<b>Observações:</b> <br> 
							".nl2br($orc_observacoes)." <p>
							<br>
							<b>Atenciosamente,<br>
							<span class=azul>Exa<span class=verde>c</span>to</span> Assessoria e Administração<br>
							(11) <span class=verde>4791-9220</span><br>
							<span class=azul>www.exactoadm.com.br</span> <br><br>
							</b>
							<hr>
							<span class='rodape'>
								Enviado ".$datap."<br><br>
								As informações contidas nesta mensagem e nos arquivos anexados são para uso restrito, sendo seu sigilo protegido por lei, não havendo ainda garantia legal quanto à integridade de seu conteúdo. Caso não seja o destinatário, por favor desconsidere essa mensagem. O uso indevido dessas informações será tratado conforme as normas da empresa e a legislação em vigor.
							</font>
						</td>
					</tr>
					</table>
				</body>
						");
						/*$mail->AltBody = 'Este é o corpo da mensagem de teste, em Texto Plano! \r\n 
						<IMG src="http://seudomínio.com.br/imagem.jpg" alt=":)"  class="wp-smiley"> ';*/
						 
						// Define os anexos (opcional)
						// =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
						//$mail->AddAttachment("/home/login/documento.pdf", "novo_nome.pdf");  // Insere um anexo
						 
						// Envia o e-mail
						$enviado = $mail->Send();
												
						// Limpa os destinatários e os anexos
						$mail->ClearAllRecipients();
						$mail->ClearAttachments();
						if ($enviado)
						{
						 	echo "
							<SCRIPT language='JavaScript'>
								abreMask(
								'<img src=../imagens/ok.png> Orçamento cadastrado com sucesso.<br>Aguarde o breve atendimento de nossa equipe e acompanhe o andamento do seu orçamento.<br><br>'+
								'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
							</SCRIPT>
								";					
						}
						else
						{
							echo "
							<SCRIPT language='JavaScript'>
								abreMask(
								'<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>'+
								'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
							</SCRIPT>
								"; 
							echo "Informações do erro: " . $mail->ErrorInfo;
							
						}
						


?>