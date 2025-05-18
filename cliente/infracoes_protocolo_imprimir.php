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
        WHERE inf_id = :inf_id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':inf_id', $inf_id, PDO::PARAM_INT);
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

<?php
session_start();
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

$host = "localhost";
$user = "sistemae_admin";
$senha = "infomogi123";
$dbname = "sistemae_sistema";

// Conexão segura com PDO
try {
	$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $senha, [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	]);
} catch (PDOException $e) {
	die('Erro ao conectar ao banco de dados: ' . $e->getMessage());
}

// Obtendo os dados
$inf_id = $_GET['inf_id'] ?? '';

$sql = "SELECT * FROM infracoes_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
        WHERE inf_id = :inf_id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':inf_id', $inf_id, PDO::PARAM_INT);
$stmt->execute();
$registro = $stmt->fetch();

if (!$registro) {
	die('Registro não encontrado.');
}

$inf_data = date("d/m/Y", strtotime($registro['inf_data']));
$inf_tipo_texto = match ($registro['inf_tipo']) {
	"Notificação de advertência por infração disciplinar" => "Advertência por infração",
	"Multa por Infração Interna" => "Multa por infração",
	"Notificação de ressarcimento" => "Notificação de ressarcimento",
	"Comunicação interna" => "Comunicação interna",
	default => "Não especificado"
};

ob_start(); // Inicia o buffer de saída
?>

<style>
	.titulo_adm {
		width: 960px;
		margin: auto;
		font-size: 18px;
		color: #999;
		text-align: left;
		padding: 10px;
	}

	.laudo {
		font-family: "Calibri";
		font-size: 13px;
		padding: 20px;
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
				<table class="bordatabela" cellspacing="0" cellpadding="5" width="1000">
					<tr>
						<td colspan="4" class="label2" align="center">A/C
							<?php echo htmlspecialchars($registro['inf_proprietario']); ?>
						</td>
					</tr>
					<tr>
						<td class="label" align="right">Data entrega:</td>
						<td><?php echo $inf_data; ?></td>
						<td class="label" align="right">N°:</td>
						<td><?php echo str_pad($registro['inf_id'], 3, "0", STR_PAD_LEFT) . "/" . htmlspecialchars($registro['inf_ano']); ?>
						</td>
					</tr>
					<tr>
						<td class="label" align="right">Referente a entrega de:</td>
						<td colspan="3"><?php echo htmlspecialchars($inf_tipo_texto); ?></td>
					</tr>
					<tr>
						<td class="label" align="right">Nome do condomínio:</td>
						<td colspan="3"><?php echo htmlspecialchars($registro['cli_nome_razao']); ?></td>
					</tr>
					<tr>
						<td class="label" align="right">Apto:</td>
						<td><?php echo htmlspecialchars($registro['inf_apto']); ?></td>
						<td class="label" align="right">Bloco:</td>
						<td><?php echo htmlspecialchars($registro['inf_bloco']); ?></td>
					</tr>
					<tr>
						<td colspan="4" align="center" class="italic">
							Recebi em _______/_______/____________<br><br><br>
							________________________________________________<br>Nome legível
						</td>
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
$mpdf->SetHTMLHeader('<div class="topo"><img src="../imagens/logo.png" width="200"><br><br></div>');
$mpdf->SetHTMLFooter('<div class="rodape"><span class="azul">Exacto Assessoria e Administração</span><br>Rua Prof. Emilio Augusto Ferreira, 32 - Vila Oliveira, Mogi das Cruzes/SP<br>Fone: (11) <span class="verde">4791-9220</span><br>Email: <span class="azul">exacto@exactoadm.com.br</span> | Site: <span class="azul">www.exactoadm.com.br</span></div>');
$mpdf->WriteHTML($html);
$mpdf->Output('Protocolo_' . str_pad(htmlspecialchars($registro['inf_id']), 6, '0', STR_PAD_LEFT) . '.pdf', 'I');
exit();
?>