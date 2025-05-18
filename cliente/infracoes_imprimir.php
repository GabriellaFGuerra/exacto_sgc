<?php
session_start();
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

$host = "localhost";
$user = "sistemae_admin";
$senha = "infomogi123";
$dbname = "sistemae_sistema";

// Conexão usando PDO
try {
	$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $senha, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	]);
} catch (PDOException $e) {
	die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}

// Definição dos meses
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
$autenticacao = "&login=" . urlencode($login) . "&n=" . urlencode($n);
$pagina = $_GET['pagina'] ?? '';
$inf_id = $_GET['inf_id'] ?? '';

// Consulta segura
$sql = "SELECT * FROM infracoes_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
        WHERE inf_id = :inf_id AND inf_cliente = :cliente_id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':inf_id', $inf_id, PDO::PARAM_INT);
$stmt->bindParam(':cliente_id', $_SESSION['cliente_id'], PDO::PARAM_INT);
$stmt->execute();
$registro = $stmt->fetch();

if ($registro) {
	$inf_data = date("d/m/Y", strtotime($registro['inf_data']));
}

ob_start(); // Inicia o buffer de saída
?>

<style>
	.titulo_adm {
		width: 960px;
		margin: auto;
		font-size: 18px;
		color: #999;
		text-align: left;
		padding: 10px 10px;
	}

	.laudo {
		font-family: "Calibri";
		font-size: 13px;
		padding: 20px 10px;
		border-radius: 10px;
	}

	.titulo_tabela {
		font-size: 13px;
		font-family: "Calibri";
		background: #EEE;
	}

	.bordatabela {
		border: 1px solid #DADADA;
		font-size: 11px;
		color: #666;
		border-radius: 2px;
	}

	.rodape {
		margin: auto;
		text-align: left;
		padding: 15px 0;
		font-family: "Calibri";
	}
</style>

<table align="center" border="0">
	<tr>
		<td align="left">
			<div class="laudo">
				<table class="laudo" align="center" cellspacing="0" cellpadding="5" width="1000">
					<tr>
						<td colspan="3" class="titulo_tabela">Cidade e Data:
							<?php echo htmlspecialchars($registro['inf_cidade'] ?? '') . ', ' . htmlspecialchars($inf_data ?? ''); ?>
						</td>
					</tr>
					<tr>
						<td><b>Proprietário:</b> <?php echo htmlspecialchars($registro['inf_proprietario'] ?? ''); ?>
						</td>
						<td><b>Unidade:</b> <?php echo htmlspecialchars($registro['inf_apto'] ?? ''); ?></td>
						<td><b>Bloco:</b> <?php echo htmlspecialchars($registro['inf_bloco'] ?? ''); ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Endereço:</b>
							<?php echo htmlspecialchars($registro['inf_endereco'] ?? ''); ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Email:</b> <?php echo htmlspecialchars($registro['inf_email'] ?? ''); ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Descrição da Irregularidade:</b>
							<?php echo htmlspecialchars($registro['inf_desc_irregularidade'] ?? ''); ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Assunto:</b> <?php echo htmlspecialchars($registro['inf_assunto'] ?? ''); ?>
						</td>
					</tr>
					<tr>
						<td colspan="3"><b>Descrição do Artigo:</b>
							<?php echo htmlspecialchars($registro['inf_desc_artigo'] ?? ''); ?></td>
					</tr>
					<tr>
						<td colspan="3"><b>Notificação Disciplinar:</b>
							<?php echo htmlspecialchars($registro['inf_desc_notificacao'] ?? ''); ?></td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>

<?php
$html = ob_get_clean();

// Geração do PDF usando a versão mais recente do mPDF
require_once __DIR__ . '/vendor/autoload.php';
use Mpdf\Mpdf;

$mpdf = new Mpdf();
$mpdf->SetTitle('Exacto Adm | Imprimir Prestação de Contas');
$mpdf->SetHTMLHeader('<div class="topo2"><img src="' . htmlspecialchars($registro['cli_foto'] ?? '') . '" height="100"></div><div class="topo2">' . htmlspecialchars($registro['inf_tipo'] ?? '') . '<br><span class="cliente">' . htmlspecialchars($registro['cli_nome_razao'] ?? '') . '</span></div><div class="topo2"><br>Nº ' . str_pad(htmlspecialchars($registro['inf_id'] ?? ''), 3, "0", STR_PAD_LEFT) . '/' . htmlspecialchars($registro['inf_ano'] ?? '') . '</div>');
$mpdf->SetHTMLFooter('<div class="rodape"><br>Atenciosamente,<br>' . htmlspecialchars($registro['cli_nome_razao'] ?? '') . '</div>');
$mpdf->WriteHTML($html);
$mpdf->Output('Orçamento_' . str_pad(htmlspecialchars($registro['inf_id'] ?? ''), 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit();
?>