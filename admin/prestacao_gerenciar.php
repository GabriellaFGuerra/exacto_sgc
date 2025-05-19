<?php
session_start();
$pagina_link = 'prestacao_gerenciar';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Funções utilitárias padronizadas
function exibirMensagem($mensagem, $url = 'prestacao_gerenciar.php?pagina=prestacao_gerenciar')
{
	$msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
	echo "<script>alert('$msg'); window.location.href = '$url';</script>";
	exit;
}
function dataParaBanco($data)
{
	if (!$data)
		return null;
	$partes = explode('/', $data);
	return (count($partes) === 3) ? "{$partes[2]}-{$partes[1]}-{$partes[0]}" : $data;
}
function dataParaBR($data)
{
	if (!$data)
		return '';
	$partes = explode('-', $data);
	return (count($partes) === 3) ? "{$partes[2]}/{$partes[1]}/{$partes[0]}" : $data;
}

// Variáveis de controle padronizadas
$acao = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? 'prestacao_gerenciar';
$autenticacao = $_GET['autenticacao'] ?? '';
$paginaNumero = max(1, intval($_GET['pag'] ?? 1));
$itensPorPagina = 10;
$primeiroRegistro = ($paginaNumero - 1) * $itensPorPagina;
$tituloPagina = "Prestação de Contas &raquo; <a href='prestacao_gerenciar.php?pagina=prestacao_gerenciar$autenticacao'>Gerenciar</a>";

// Processamento de formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($acao === "adicionar") {
		$clienteId = $_POST['pre_cliente_id'] ?? '';
		$referencia = ($_POST['pre_ref_mes'] ?? '') . '/' . ($_POST['pre_ref_ano'] ?? '');
		$dataEnvio = dataParaBanco($_POST['pre_data_envio'] ?? '');
		$enviadoPor = $_POST['pre_enviado_por'] ?? '';
		$observacoes = $_POST['pre_observacoes'] ?? '';

		$stmt = $pdo->prepare("INSERT INTO prestacao_gerenciar (pre_cliente, pre_referencia, pre_data_envio, pre_enviado_por, pre_observacoes) VALUES (?, ?, ?, ?, ?)");
		if ($stmt->execute([$clienteId, $referencia, $dataEnvio, $enviadoPor, $observacoes])) {
			exibirMensagem('Cadastro efetuado com sucesso.');
		} else {
			exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
		}
	}

	if ($acao === 'editar') {
		$prestacaoId = $_GET['pre_id'] ?? '';
		$referencia = ($_POST['pre_ref_mes'] ?? '') . '/' . ($_POST['pre_ref_ano'] ?? '');
		$dataEnvio = dataParaBanco($_POST['pre_data_envio'] ?? '');
		$enviadoPor = $_POST['pre_enviado_por'] ?? '';
		$observacoes = $_POST['pre_observacoes'] ?? '';

		$stmt = $pdo->prepare("UPDATE prestacao_gerenciar SET pre_referencia = ?, pre_data_envio = ?, pre_enviado_por = ?, pre_observacoes = ? WHERE pre_id = ?");
		if ($stmt->execute([$referencia, $dataEnvio, $enviadoPor, $observacoes, $prestacaoId])) {
			exibirMensagem('Dados alterados com sucesso.');
		} else {
			exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
		}
	}

	if ($acao === 'comprovante') {
		$prestacaoId = $_GET['pre_id'] ?? '';
		$arquivos = $_FILES['pre_comprovante'] ?? null;
		$caminho = "../admin/prestacao_comprovante/$prestacaoId/";
		if (!is_dir($caminho)) {
			mkdir($caminho, 0755, true);
		}
		$arquivoFinal = '';
		$erro = false;
		if ($arquivos && is_array($arquivos['name'])) {
			foreach ($arquivos['name'] as $indice => $nomeArquivo) {
				if ($nomeArquivo) {
					$extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
					$nomeFinal = $caminho . md5(mt_rand(1, 10000) . $nomeArquivo) . '.' . $extensao;
					if (move_uploaded_file($arquivos['tmp_name'][$indice], $nomeFinal)) {
						$arquivoFinal = $nomeFinal;
					} else {
						$erro = true;
					}
				}
			}
			if ($arquivoFinal) {
				$stmt = $pdo->prepare("UPDATE prestacao_gerenciar SET pre_comprovante = ? WHERE pre_id = ?");
				if (!$stmt->execute([$arquivoFinal, $prestacaoId])) {
					$erro = true;
				}
			}
		}
		if (!$erro) {
			exibirMensagem('Anexo enviado com sucesso.');
		} else {
			exibirMensagem('Erro ao enviar anexo.');
		}
	}
}

// Exclusão
if ($acao === 'excluir') {
	$prestacaoId = $_GET['pre_id'] ?? '';
	$stmt = $pdo->prepare("DELETE FROM prestacao_gerenciar WHERE pre_id = ?");
	if ($stmt->execute([$prestacaoId])) {
		exibirMensagem('Exclusão realizada com sucesso.');
	} else {
		exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
	}
}

// Ativação/Desativação
if ($acao === 'ativar' || $acao === 'desativar') {
	$prestacaoId = $_GET['pre_id'] ?? '';
	$status = $acao === 'ativar' ? 1 : 0;
	$stmt = $pdo->prepare("UPDATE prestacao_gerenciar SET pre_status = ? WHERE pre_id = ?");
	if ($stmt->execute([$status, $prestacaoId])) {
		$mensagem = $status ? 'Ativação realizada com sucesso' : 'Desativação realizada com sucesso';
		exibirMensagem($mensagem);
	} else {
		exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
	}
}

// Filtros
$filtroPrestacao = $_REQUEST['fil_prestacao'] ?? '';
$filtroNome = $_REQUEST['fil_nome'] ?? '';
$filtroReferencia = $_REQUEST['fil_referencia'] ?? '';
$filtroDataInicio = $_REQUEST['fil_data_inicio'] ?? '';
$filtroDataFim = $_REQUEST['fil_data_fim'] ?? '';

$where = [];
$params = [];

$where[] = "cli_status = 1 AND cli_deletado = 1 AND ucl_usuario = ?";
$params[] = $_SESSION['usuario_id'];

if ($filtroPrestacao) {
	$where[] = "pre_id = ?";
	$params[] = $filtroPrestacao;
}
if ($filtroNome) {
	$where[] = "cli_nome_razao LIKE ?";
	$params[] = "%$filtroNome%";
}
if ($filtroReferencia) {
	$where[] = "pre_referencia = ?";
	$params[] = $filtroReferencia;
}
if ($filtroDataInicio || $filtroDataFim) {
	$dataInicio = $filtroDataInicio ? dataParaBanco($filtroDataInicio) : null;
	$dataFim = $filtroDataFim ? dataParaBanco($filtroDataFim) . ' 23:59:59' : null;
	if ($dataInicio && $dataFim) {
		$where[] = "pre_data_cadastro BETWEEN ? AND ?";
		$params[] = $dataInicio;
		$params[] = $dataFim;
	} elseif ($dataInicio) {
		$where[] = "pre_data_cadastro >= ?";
		$params[] = $dataInicio;
	} elseif ($dataFim) {
		$where[] = "pre_data_cadastro <= ?";
		$params[] = $dataFim;
	}
}

$whereSql = implode(' AND ', $where);

// Listagem com paginação
if ($pagina === "prestacao_gerenciar") {
	$sql = "SELECT * FROM prestacao_gerenciar 
        LEFT JOIN (cadastro_clientes 
            INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
        ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
        WHERE $whereSql
        ORDER BY pre_data_cadastro DESC
        LIMIT $primeiroRegistro, $itensPorPagina";
	$stmt = $pdo->prepare($sql);
	$stmt->execute($params);
	$prestacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

	$sqlTotal = "SELECT COUNT(*) FROM prestacao_gerenciar 
        LEFT JOIN (cadastro_clientes 
            INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
        ON cadastro_clientes.cli_id = prestacao_gerenciar.pre_cliente
        WHERE $whereSql";
	$stmtTotal = $pdo->prepare($sqlTotal);
	$stmtTotal->execute($params);
	$totalRegistros = $stmtTotal->fetchColumn();
	$totalPaginas = ceil($totalRegistros / $itensPorPagina);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include "../css/style.php"; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <?php include '../mod_topo/topo.php'; ?>

    <?php if ($pagina === "prestacao_gerenciar"): ?>
    <div class='centro'>
        <div class='titulo'> <?= $tituloPagina ?> </div>
        <div id='botoes'><input value='Nova Prestação de Conta' type='button'
                onclick="window.location.href='prestacao_gerenciar.php?pagina=adicionar_prestacao_gerenciar<?= $autenticacao; ?>';" />
        </div>
        <div class='filtro'>
            <form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post'
                action='prestacao_gerenciar.php?pagina=prestacao_gerenciar<?= $autenticacao; ?>'>
                <input name='fil_prestacao' id='fil_prestacao' value='<?= htmlspecialchars($filtroPrestacao) ?>'
                    placeholder='N° '>
                <input name='fil_nome' id='fil_nome' value='<?= htmlspecialchars($filtroNome) ?>' placeholder='Cliente'>
                <input name='fil_referencia' id='fil_referencia' value='<?= htmlspecialchars($filtroReferencia) ?>'
                    placeholder='Referência '>
                <input type='text' name='fil_data_inicio' id='fil_data_inicio' placeholder='Data Início'
                    value='<?= htmlspecialchars($filtroDataInicio) ?>' onkeypress='return mascaraData(this,event);'>
                <input type='text' name='fil_data_fim' id='fil_data_fim' placeholder='Data Fim'
                    value='<?= htmlspecialchars($filtroDataFim) ?>' onkeypress='return mascaraData(this,event);'>
                <input type='submit' value='Filtrar'>
            </form>
        </div>
        <?php if (!empty($prestacoes)): ?>
        <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
            <tr>
                <td class='titulo_tabela'>N° Prestação de Conta</td>
                <td class='titulo_tabela'>Cliente</td>
                <td class='titulo_tabela'>Referência</td>
                <td class='titulo_tabela'>Data Envio</td>
                <td class='titulo_tabela'>Por</td>
                <td class='titulo_tabela'>Observação</td>
                <td class='titulo_tabela' align='center'>Data Cadastro</td>
                <td class='titulo_tabela' align='center'>Gerar Protocolo</td>
                <td class='titulo_tabela' align='center'>Protocolo Assinado</td>
                <td class='titulo_tabela' align='center'>Gerenciar</td>
            </tr>
            <?php $contador = 0;
					foreach ($prestacoes as $prestacao):
						$preId = $prestacao['pre_id'];
						$clienteNome = htmlspecialchars($prestacao['cli_nome_razao']);
						$referencia = htmlspecialchars($prestacao['pre_referencia']);
						$dataEnvio = $prestacao['pre_data_envio'] ? dataParaBR($prestacao['pre_data_envio']) : '';
						$enviadoPor = htmlspecialchars($prestacao['pre_enviado_por']);
						$observacoes = htmlspecialchars($prestacao['pre_observacoes']);
						$dataCadastro = $prestacao['pre_data_cadastro'] ? dataParaBR(substr($prestacao['pre_data_cadastro'], 0, 10)) : '';
						$horaCadastro = $prestacao['pre_data_cadastro'] ? substr($prestacao['pre_data_cadastro'], 11, 5) : '';
						$comprovante = $prestacao['pre_comprovante'];
						$classeLinha = $contador % 2 == 0 ? "linhaimpar" : "linhapar";
						$contador++;
						?>
            <tr class='<?= $classeLinha ?>'>
                <td><?= $preId ?></td>
                <td><?= $clienteNome ?></td>
                <td><?= $referencia ?></td>
                <td><?= $dataEnvio ?></td>
                <td><?= $enviadoPor ?></td>
                <td><?= $observacoes ?></td>
                <td align='center'><?= $dataCadastro ?><br><span class='detalhe'><?= $horaCadastro ?></span></td>
                <td align='center'><a href='prestacao_imprimir.php?pre_id=<?= $preId ?>&autenticacao'
                        target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
                <td align='center'>
                    <?php if ($comprovante): ?>
                    <a href='<?= htmlspecialchars($comprovante) ?>' target='_blank'><img src='../imagens/icon-pdf.png'
                            valign='middle'></a>
                    <?php endif; ?>
                </td>
                <td align='center'>
                    <a
                        href="prestacao_gerenciar.php?pagina=editar_prestacao_gerenciar&pre_id=<?= $preId . $autenticacao ?>"><img
                            border="0" src="../imagens/icon-editar.png"></a>
                    <a
                        onclick="if(confirm('Deseja realmente excluir a prestação <?= addslashes($preId) ?>?')){window.location.href='prestacao_gerenciar.php?pagina=prestacao_gerenciar&action=excluir&pre_id=<?= $preId . $autenticacao ?>';}"><img
                            border="0" src="../imagens/icon-excluir.png"></a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php if ($totalPaginas > 1): ?>
        <div class='paginacao' style='text-align:center; margin:20px 0;'>
            <?php for ($i = 1; $i <= $totalPaginas; $i++):
							$classe = ($i == $paginaNumero) ? "pagina-ativa" : "";
							$url = "prestacao_gerenciar.php?pagina=prestacao_gerenciar&pag=$i$autenticacao";
							?>
            <a class='<?= $classe ?>' href='<?= $url ?>'><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <br><br><br>Não há nenhuma prestação de conta cadastrada.
        <?php endif; ?>
        <div class='titulo'></div>
    </div>
    <?php endif; ?>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>