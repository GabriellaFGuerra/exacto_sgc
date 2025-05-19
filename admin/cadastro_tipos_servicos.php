<?php
session_start();
$pagina_link = 'cadastro_tipos_servicos';

// Inclusão dos arquivos essenciais
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Variáveis de controle
$autenticacao = $_GET['autenticacao'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$action = $_GET['action'] ?? '';
$pag = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
$titulo = 'Cadastros - Tipos de Serviço';
$pageBreadcrumb = "Cadastros &raquo; <a href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos$autenticacao'>Tipos de Serviço</a>";
$itensPorPagina = 20;
$offset = ($pag - 1) * $itensPorPagina;

// Função para exibir mensagens e redirecionar
function exibirMensagem($mensagem)
{
	$msg = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
	echo "<script>alert('$msg'); window.location.href = 'cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos';</script>";
	exit;
}

// CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$tps_nome = trim($_POST['tps_nome'] ?? '');

	if ($action === 'adicionar') {
		$stmt = $pdo->prepare('INSERT INTO cadastro_tipos_servicos (tps_nome) VALUES (:tps_nome)');
		if ($stmt->execute(['tps_nome' => $tps_nome])) {
			exibirMensagem('Cadastro efetuado com sucesso.');
		} else {
			exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
		}
	}

	if ($action === 'editar') {
		$tps_id = (int) ($_GET['tps_id'] ?? 0);
		$stmt = $pdo->prepare('UPDATE cadastro_tipos_servicos SET tps_nome = :tps_nome WHERE tps_id = :tps_id');
		if ($stmt->execute(['tps_nome' => $tps_nome, 'tps_id' => $tps_id])) {
			exibirMensagem('Dados alterados com sucesso.');
		} else {
			exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
		}
	}
}

if ($action === 'excluir' && isset($_GET['tps_id'])) {
	$tps_id = (int) $_GET['tps_id'];
	try {
		$stmt = $pdo->prepare('DELETE FROM cadastro_tipos_servicos WHERE tps_id = :tps_id');
		$stmt->execute(['tps_id' => $tps_id]);
		exibirMensagem('Exclusão realizada com sucesso');
	} catch (PDOException $e) {
		if ($e->getCode() == '23000') {
			exibirMensagem('Este tipo de serviço não pode ser excluído pois está relacionado com outros registros.');
		} else {
			exibirMensagem('Erro ao excluir. Detalhes: ' . $e->getMessage());
		}
	}
	exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?php echo $titulo; ?>
    </title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php
include '../css/style.php';
require_once '../mod_includes/php/funcoes-jquery.php';
?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php include '../mod_topo/topo.php'; ?>
    <div class="centro">
        <?php
// Formulário de edição
if ($pagina === 'editar_cadastro_tipos_servicos' && isset($_GET['tps_id'])) {
	$tps_id = (int) ($_GET['tps_id'] ?? 0);

	// Busca os dados para preencher o formulário
	$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_servicos WHERE tps_id = :tps_id');
	$stmt->execute(['tps_id' => $tps_id]);
	$servico = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($servico) {
		$tps_nome = htmlspecialchars($servico['tps_nome']);
		?>
        <form name="form_cadastro_tipos_servicos" id="form_cadastro_tipos_servicos" enctype="multipart/form-data"
            method="post"
            action="cadastro_tipos_servicos.php?pagina=editar_cadastro_tipos_servicos&action=editar&tps_id=<?php echo $tps_id . $autenticacao; ?>">
            <div class="centro">
                <div class="titulo">
                    <?php echo $pageBreadcrumb; ?> &raquo; Editar:
                    <?php echo $tps_nome; ?>
                </div>
                <table align="center" cellspacing="0">
                    <tr>
                        <td align="left">
                            <input name="tps_nome" id="tps_nome" value="<?php echo $tps_nome; ?>"
                                placeholder="Nome do Serviço" required>
                            <p>
                                <center>
                                    <div id="erro" align="center">&nbsp;</div>
                                    <input type="submit" id="bt_cadastro_tipos_servicos"
                                        value="Salvar" />&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="button" id="botao_cancelar"
                                        onclick="window.location.href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos<?php echo $autenticacao; ?>';"
                                        value="Cancelar" />
                                </center>
                        </td>
                    </tr>
                </table>
                <div class="titulo"></div>
            </div>
        </form>
        <?php
	}
	include '../mod_rodape/rodape.php';
	echo '</body></html>';
	exit;
}

// Listagem principal
if ($pagina === 'cadastro_tipos_servicos' || $pagina == '') {
	$stmtTotal = $pdo->query('SELECT COUNT(*) FROM cadastro_tipos_servicos');
	$totalRegistros = (int) $stmtTotal->fetchColumn();

	$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome ASC LIMIT :offset, :limit');
	$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
	$stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
	$stmt->execute();
	$servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

	echo "
    <div class='titulo'> $pageBreadcrumb </div>
    <div id='botoes'>
        <input value='Novo Serviço' type='button' onclick=\"window.location.href='cadastro_tipos_servicos.php?pagina=adicionar_cadastro_tipos_servicos$autenticacao';\" />
    </div>
    ";

	if ($totalRegistros > 0) {
		echo "
        <table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
            <tr>
                <td class='titulo_tabela'>Serviço</td>
                <td class='titulo_tabela' align='center'>Gerenciar</td>
            </tr>";
		foreach ($servicos as $index => $servico) {
			$tps_id = $servico['tps_id'];
			$tps_nome = htmlspecialchars($servico['tps_nome']);
			$rowClass = $index % 2 === 0 ? 'linhaimpar' : 'linhapar';
			echo "
            <tr class='$rowClass'>
                <td>$tps_nome</td>
                <td align='center'>
                    <a href='cadastro_tipos_servicos.php?pagina=editar_cadastro_tipos_servicos&tps_id=$tps_id$autenticacao'>
                        <img border='0' src='../imagens/icon-editar.png' alt='Editar'>
                    </a>
                    &nbsp;
                    <a href=\"javascript:void(0);\" onclick=\"
                        if(confirm('Deseja realmente excluir o tipo de serviço &quot;$tps_nome&quot;?')){window.location.href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos&action=excluir&tps_id=$tps_id$autenticacao';}
                    \">
                        <img border='0' src='../imagens/icon-excluir.png' alt='Excluir'>
                    </a>
                </td>
            </tr>";
		}
		echo '</table>';

		// Paginação
		$totalPaginas = ceil($totalRegistros / $itensPorPagina);
		if ($totalPaginas > 1) {
			echo "<div class='paginacao'>";
			for ($i = 1; $i <= $totalPaginas; $i++) {
				$active = $i == $pag ? "style='font-weight:bold;'" : "";
				$url = "cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos&pag=$i$autenticacao";
				echo "<a href='$url' $active>$i</a> ";
			}
			echo "</div>";
		}
	} else {
		echo '<br><br><br>Não há nenhum tipo de serviço cadastrado.';
	}
	echo "<div class='titulo'></div>";
}

// Formulário de adição
if ($pagina === 'adicionar_cadastro_tipos_servicos') {
	?>
        <form name="form_cadastro_tipos_servicos" id="form_cadastro_tipos_servicos" enctype="multipart/form-data"
            method="post"
            action="cadastro_tipos_servicos.php?pagina=adicionar_cadastro_tipos_servicos&action=adicionar<?php echo $autenticacao; ?>">
            <div class="centro">
                <div class="titulo">
                    <?php echo $pageBreadcrumb; ?> &raquo; Adicionar
                </div>
                <table align="center" cellspacing="0" width="500">
                    <tr>
                        <td align="center">
                            <input name="tps_nome" id="tps_nome" placeholder="Nome do Serviço" required>
                            <p>
                                <center>
                                    <div id="erro" align="center">&nbsp;</div>
                                    <input type="submit" id="bt_cadastro_tipos_servicos"
                                        value="Salvar" />&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="button" id="botao_cancelar"
                                        onclick="window.location.href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos<?php echo $autenticacao; ?>';"
                                        value="Cancelar" />
                                </center>
                        </td>
                    </tr>
                </table>
                <div class="titulo"></div>
            </div>
        </form>
        <?php
}
?>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>