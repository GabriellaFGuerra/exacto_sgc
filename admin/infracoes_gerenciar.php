<?php
session_start();
$pagina_link = 'infracoes_gerenciar';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';
include '../mod_topo/topo.php';

// Funções utilitárias padronizadas
function exibirMensagem($mensagem, $url = 'infracoes_gerenciar.php?pagina=infracoes_gerenciar')
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

// Parâmetros da requisição
$acao = $_GET['action'] ?? '';
$pagina = $_GET['pagina'] ?? 'infracoes_gerenciar';
$autenticacao = $_GET['autenticacao'] ?? '';
$paginaNumero = max(1, intval($_GET['pag'] ?? 1));

// Página de navegação
$tituloPagina = "Infrações &raquo; <a href='infracoes_gerenciar.php?pagina=infracoes_gerenciar$autenticacao'>Gerenciar</a>";

// Processamento de formulários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if ($acao === "adicionar" || $acao === "duplicar") {
		$stmt = $pdo->prepare(
			"INSERT INTO infracoes_gerenciar (
                inf_cliente, inf_tipo, inf_ano, inf_cidade, inf_data, inf_proprietario, inf_apto, inf_bloco, inf_endereco, inf_email, inf_desc_irregularidade, inf_assunto, inf_desc_artigo, inf_desc_notificacao
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
		);
		$ok = $stmt->execute([
			$_POST['inf_cliente_id'] ?? null,
			$_POST['inf_tipo'] ?? '',
			date("Y"),
			$_POST['inf_cidade'] ?? '',
			dataParaBanco($_POST['inf_data'] ?? ''),
			$_POST['inf_proprietario'] ?? '',
			$_POST['inf_apto'] ?? '',
			$_POST['inf_bloco'] ?? '',
			$_POST['inf_endereco'] ?? '',
			$_POST['inf_email'] ?? '',
			$_POST['inf_desc_irregularidade'] ?? '',
			$_POST['inf_assunto'] ?? '',
			$_POST['inf_desc_artigo'] ?? '',
			$_POST['inf_desc_notificacao'] ?? ''
		]);
		if ($ok) {
			exibirMensagem('Cadastro efetuado com sucesso.', 'infracoes_gerenciar.php?pagina=infracoes_gerenciar' . $autenticacao);
		} else {
			exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
		}
	}

	if ($acao === 'editar') {
		$id = $_GET['inf_id'] ?? null;
		$stmt = $pdo->prepare(
			"UPDATE infracoes_gerenciar SET
                inf_tipo = ?, inf_cidade = ?, inf_data = ?, inf_proprietario = ?, inf_apto = ?, inf_bloco = ?, inf_endereco = ?, inf_email = ?, inf_desc_irregularidade = ?, inf_assunto = ?, inf_desc_artigo = ?, inf_desc_notificacao = ?
                WHERE inf_id = ?"
		);
		$ok = $stmt->execute([
			$_POST['inf_tipo'] ?? '',
			$_POST['inf_cidade'] ?? '',
			dataParaBanco($_POST['inf_data'] ?? ''),
			$_POST['inf_proprietario'] ?? '',
			$_POST['inf_apto'] ?? '',
			$_POST['inf_bloco'] ?? '',
			$_POST['inf_endereco'] ?? '',
			$_POST['inf_email'] ?? '',
			$_POST['inf_desc_irregularidade'] ?? '',
			$_POST['inf_assunto'] ?? '',
			$_POST['inf_desc_artigo'] ?? '',
			$_POST['inf_desc_notificacao'] ?? '',
			$id
		]);
		if ($ok) {
			exibirMensagem('Dados alterados com sucesso.', 'infracoes_gerenciar.php?pagina=infracoes_gerenciar' . $autenticacao);
		} else {
			exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
		}
	}
}

// Exclusão
if ($acao === 'excluir' && isset($_GET['inf_id'])) {
	$stmt = $pdo->prepare("DELETE FROM infracoes_gerenciar WHERE inf_id = ?");
	$ok = $stmt->execute([$_GET['inf_id']]);
	if ($ok) {
		exibirMensagem('Exclusão realizada com sucesso.', 'infracoes_gerenciar.php?pagina=infracoes_gerenciar' . $autenticacao);
	} else {
		exibirMensagem('Este item não pode ser excluído pois está relacionado com alguma tabela.');
	}
}

// Filtros
$itensPorPagina = 10;
$primeiroRegistro = ($paginaNumero - 1) * $itensPorPagina;

$filtroNome = $_REQUEST['fil_nome'] ?? '';
$filtroBloco = $_REQUEST['fil_bloco'] ?? '';
$filtroAssunto = $_REQUEST['fil_assunto'] ?? '';
$filtroApto = $_REQUEST['fil_apto'] ?? '';
$filtroProprietario = $_REQUEST['fil_proprietario'] ?? '';
$filtroTipo = $_REQUEST['fil_inf_tipo'] ?? '';

$where = [];
$params = [$_SESSION['usuario_id']];

if ($filtroNome) {
	$where[] = "cli_nome_razao LIKE ?";
	$params[] = "%$filtroNome%";
}
if ($filtroBloco) {
	$where[] = "inf_bloco LIKE ?";
	$params[] = "%$filtroBloco%";
}
if ($filtroAssunto) {
	$where[] = "inf_assunto LIKE ?";
	$params[] = "%$filtroAssunto%";
}
if ($filtroApto) {
	$where[] = "inf_apto LIKE ?";
	$params[] = "%$filtroApto%";
}
if ($filtroProprietario) {
	$where[] = "inf_proprietario LIKE ?";
	$params[] = "%$filtroProprietario%";
}
if ($filtroTipo) {
	$where[] = "inf_tipo = ?";
	$params[] = $filtroTipo;
}

$whereSql = $where ? implode(' AND ', $where) : '1=1';

// Consulta para paginação
$sqlTotal = "SELECT COUNT(*) FROM infracoes_gerenciar
    LEFT JOIN (cadastro_clientes
        INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
    ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    WHERE ucl_usuario = ? AND $whereSql";
$stmtTotal = $pdo->prepare($sqlTotal);
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetchColumn();
$totalPaginas = ceil($totalRegistros / $itensPorPagina);

// Consulta principal
$sql = "SELECT infracoes_gerenciar.*, cli_nome_razao
    FROM infracoes_gerenciar
    LEFT JOIN (cadastro_clientes
        INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id)
    ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente
    WHERE ucl_usuario = ? AND $whereSql
    ORDER BY inf_data DESC
    LIMIT $primeiroRegistro, $itensPorPagina";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$infracoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Infrações</title>
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include "../css/style.php"; ?>
    <script src="../mod_includes/js/funcoes.js"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <?php include '../mod_topo/topo.php'; ?>

    <?php if ($pagina === "infracoes_gerenciar"): ?>
    <div class='centro'>
        <div class='titulo'>
            <?= $tituloPagina ?>
        </div>
        <div id='botoes'><input value='Nova Infração' type='button'
                onclick="window.location.href='infracoes_gerenciar.php?pagina=adicionar_infracoes_gerenciar<?= $autenticacao; ?>';" />
        </div>
        <div class='filtro'>
            <form name='form_filtro' id='form_filtro' method='post'
                action='infracoes_gerenciar.php?pagina=infracoes_gerenciar<?= $autenticacao; ?>'>
                <input name='fil_nome' value='<?= htmlspecialchars($filtroNome) ?>' placeholder=' Cliente'>
                <input name='fil_bloco' value='<?= htmlspecialchars($filtroBloco) ?>' placeholder='Bloco/Quadra'>
                <input name='fil_apto' value='<?= htmlspecialchars($filtroApto) ?>' placeholder='Apto.'>
                <input name='fil_proprietario' value='<?= htmlspecialchars($filtroProprietario) ?>'
                    placeholder='Proprietário'>
                <input name='fil_assunto' value='<?= htmlspecialchars($filtroAssunto) ?>' placeholder='Assunto'>
                <select name='fil_inf_tipo'>
                    <option value=' <?= htmlspecialchars($filtroTipo) ?>'>
                        <?= $filtroTipo ?: "Tipo de Infração" ?>
                    </option>
                    <option value='Notificação de advertência por infração disciplinar'>Notificação de advertência por
                        infração disciplinar</option>
                    <option value='Multa por Infração Interna'>Multa por Infração Interna</option>
                    <option value='Notificação de ressarcimento'>Notificação de ressarcimento</option>
                    <option value='Comunicação interna'>Comunicação interna</option>
                    <option value=''>Todos</option>
                </select>
                <input type='submit' value='Filtrar'>
            </form>
        </div>
        <?php if ($infracoes): ?>
        <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
            <tr>
                <td class='titulo_tabela'>N.</td>
                <td class='titulo_tabela'>Cliente</td>
                <td class='titulo_tabela'>Tipo</td>
                <td class='titulo_tabela'>Assunto</td>
                <td class='titulo_tabela'>Proprietário</td>
                <td class='titulo_tabela'>Bloco/Quadra/Ap</td>
                <td class='titulo_tabela'>Data</td>
                <td class='titulo_tabela' align='center'>Gerar advertência/multa</td>
                <td class='titulo_tabela' align='center'>Gerar protocolo</td>
                <td class='titulo_tabela' align='center'>Gerenciar</td>
            </tr>
            <?php $c = 0;
					foreach ($infracoes as $infracao):
						$classeLinha = $c++ % 2 ? "linhapar" : "linhaimpar";
						$id = $infracao['inf_id'];
						$ano = $infracao['inf_ano'];
						$cliente = htmlspecialchars($infracao['cli_nome_razao']);
						$tipo = htmlspecialchars($infracao['inf_tipo']);
						$assunto = htmlspecialchars($infracao['inf_assunto']);
						$bloco = htmlspecialchars($infracao['inf_bloco']);
						$apto = htmlspecialchars($infracao['inf_apto']);
						$proprietario = htmlspecialchars($infracao['inf_proprietario']);
						$data = dataParaBR($infracao['inf_data']);
						?>
            <tr class='<?= $classeLinha ?>'>
                <td><?= str_pad($id, 3, "0", STR_PAD_LEFT) . "/$ano" ?></td>
                <td><?= $cliente ?></td>
                <td><?= $tipo ?></td>
                <td><?= $assunto ?></td>
                <td><?= $proprietario ?></td>
                <td><?= $bloco ?>/
                    <?= $apto ?>
                </td>
                <td>
                    <?= $data ?>
                </td>
                <td align='center'><a href='infracoes_imprimir.php?inf_id=<?= $id ?>&autenticacao' target='_blank'><img
                            src='../imagens/icon-pdf.png' valign='middle'></a></td>
                <td align='center'><a href='infracoes_protocolo_imprimir.php?inf_id=<?= $id ?>&autenticacao'
                        target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a></td>
                <td align='center'>
                    <div id="normal-button-<?= $id ?>" class="settings-button"><img
                            src="../imagens/icon-cog-small.png" /></div>
                    <div id="user-options-<?= $id ?>" class="toolbar-icons" style="display: none;">
                        <a
                            href="infracoes_gerenciar.php?pagina=editar_infracoes_gerenciar&inf_id=<?= $id . $autenticacao ?>"><img
                                border="0" src="../imagens/icon-editar.png"></a>
                        <a
                            onclick="if(confirm('Deseja realmente excluir a infração <?= addslashes($cliente) ?>?')){window.location.href='infracoes_gerenciar.php?pagina=infracoes_gerenciar&action=excluir&inf_id=<?= $id . $autenticacao ?>';}"><img
                                border="0" src="../imagens/icon-excluir.png"></a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
				if ($totalPaginas > 1) {
					echo "<div class='paginacao' style='text-align:center; margin:20px 0;'>";
					for ($i = 1; $i <= $totalPaginas; $i++) {
						$classe = ($i == $paginaNumero) ? "pagina-ativa" : "";
						$url = "infracoes_gerenciar.php?pagina=infracoes_gerenciar&pag=$i$autenticacao";
						echo "<a class='$classe' href='$url'>$i</a> ";
					}
					echo "</div>";
				}
				?>
        <?php else: ?>
        <br><br><br>Não há nenhuma infração cadastrada.
        <?php endif; ?>
        <div class='titulo'></div>
    </div>
    <?php endif; ?>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>