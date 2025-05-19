<?php
session_start();
$pagina_link = 'cadastro_tipos_docs';
require_once '../mod_includes/php/connect.php';

// Função para exibir mensagens e redirecionar
function exibirMensagem(string $mensagem): void
{
	$mensagemSegura = htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8');
	echo "<script>alert('{$mensagemSegura}'); window.location.href = 'cadastro_tipos_docs.php?pagina=cadastro_tipos_docs';</script>";
	exit;
}

// Variáveis de controle
$pagina = $_GET['pagina'] ?? '';
$action = $_GET['action'] ?? '';
$autenticacao = $_GET['autenticacao'] ?? '';
$pag = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
$titulo = 'Cadastros - Tipos de Documento';
$itensPorPagina = 20;
$offset = ($pag - 1) * $itensPorPagina;
$pageBreadcrumb = "Cadastros &raquo; <a href='cadastro_tipos_docs.php?pagina=cadastro_tipos_docs$autenticacao'>Tipos de Documento</a>";

// CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$tpd_nome = trim($_POST['tpd_nome'] ?? '');

	if ($action === 'adicionar') {
		$stmt = $pdo->prepare('INSERT INTO cadastro_tipos_docs (tpd_nome) VALUES (:tpd_nome)');
		if ($stmt->execute(['tpd_nome' => $tpd_nome])) {
			exibirMensagem('Cadastro efetuado com sucesso.');
		} else {
			exibirMensagem('Erro ao efetuar cadastro, por favor tente novamente.');
		}
	}

	if ($action === 'editar') {
		$tpd_id = (int) ($_GET['tpd_id'] ?? 0);
		$stmt = $pdo->prepare('UPDATE cadastro_tipos_docs SET tpd_nome = :tpd_nome WHERE tpd_id = :tpd_id');
		if ($stmt->execute(['tpd_nome' => $tpd_nome, 'tpd_id' => $tpd_id])) {
			exibirMensagem('Dados alterados com sucesso.');
		} else {
			exibirMensagem('Erro ao alterar dados, por favor tente novamente.');
		}
	}
}

if ($action === 'excluir' && isset($_GET['tpd_id'])) {
	$tpd_id = (int) $_GET['tpd_id'];
	try {
		$stmt = $pdo->prepare('DELETE FROM cadastro_tipos_docs WHERE tpd_id = :tpd_id');
		$stmt->execute(['tpd_id' => $tpd_id]);
		exibirMensagem('Exclusão realizada com sucesso');
	} catch (PDOException $e) {
		if ($e->getCode() == '23000') {
			exibirMensagem('Este tipo de documento não pode ser excluído pois está relacionado com outros registros.');
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
    <title><?php echo $titulo; ?></title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <script src="../mod_includes/js/funcoes.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
    <?php
	include '../mod_includes/php/funcoes-jquery.php';
	require_once '../mod_includes/php/verificalogin.php';
	include '../mod_topo/topo.php';
	require_once '../mod_includes/php/verificapermissao.php';

	// Formulário de edição
	if ($pagina === 'editar_cadastro_tipos_docs' && isset($_GET['tpd_id'])) {
		$tpd_id = (int) ($_GET['tpd_id'] ?? 0);

		// Busca os dados para preencher o formulário
		$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_docs WHERE tpd_id = :tpd_id');
		$stmt->execute(['tpd_id' => $tpd_id]);
		$doc = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($doc) {
			$tpd_nome = htmlspecialchars($doc['tpd_nome']);
			?>
    <form name="form_cadastro_tipos_docs" id="form_cadastro_tipos_docs" enctype="multipart/form-data" method="post"
        action="cadastro_tipos_docs.php?pagina=editar_cadastro_tipos_docs&tpd_id=<?php echo $tpd_id . $autenticacao; ?>">
        <div class="centro">
            <div class="titulo"> <?php echo $pageBreadcrumb; ?> &raquo; Editar: <?php echo $tpd_nome; ?> </div>
            <table align="center" cellspacing="0">
                <tr>
                    <td align="left">
                        <input name="tpd_nome" id="tpd_nome" value="<?php echo $tpd_nome; ?>"
                            placeholder="Nome do Documento" required>
                        <p>
                            <center>
                                <div id="erro" align="center">&nbsp;</div>
                                <input type="submit" id="bt_cadastro_tipos_docs"
                                    value="Salvar" />&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="button" id="botao_cancelar"
                                    onclick="window.location.href='cadastro_tipos_docs.php?pagina=cadastro_tipos_docs<?php echo $autenticacao; ?>';"
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
	if ($pagina === 'cadastro_tipos_docs' || $pagina == '') {
		$stmtTotal = $pdo->query('SELECT COUNT(*) FROM cadastro_tipos_docs');
		$totalRegistros = (int) $stmtTotal->fetchColumn();

		$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_docs ORDER BY tpd_nome ASC LIMIT :offset, :limit');
		$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
		$stmt->bindValue(':limit', $itensPorPagina, PDO::PARAM_INT);
		$stmt->execute();
		$docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
		?>
    <div class="centro">
        <div class="titulo"> <?php echo $pageBreadcrumb; ?> </div>
        <div id="botoes">
            <input value="Novo Documento" type="button"
                onclick="window.location.href='cadastro_tipos_docs.php?pagina=adicionar_cadastro_tipos_docs<?php echo $autenticacao; ?>';" />
        </div>
        <?php if ($totalRegistros > 0): ?>
        <table align="center" width="100%" border="0" cellspacing="0" cellpadding="10" class="bordatabela">
            <tr>
                <td class="titulo_tabela">Documento</td>
                <td class="titulo_tabela" align="center">Gerenciar</td>
            </tr>
            <?php foreach ($docs as $index => $doc):
						$tpd_id = $doc['tpd_id'];
						$tpd_nome = htmlspecialchars($doc['tpd_nome']);
						$rowClass = $index % 2 === 0 ? 'linhaimpar' : 'linhapar';
						?>
            <tr class="<?php echo $rowClass; ?>">
                <td><?php echo $tpd_nome; ?></td>
                <td align="center">
                    <a
                        href="cadastro_tipos_docs.php?pagina=editar_cadastro_tipos_docs&tpd_id=<?php echo $tpd_id . $autenticacao; ?>">
                        <img border="0" src="../imagens/icon-editar.png" alt="Editar">
                    </a>
                    &nbsp;
                    <a href="javascript:void(0);" onclick="
						if(confirm('Deseja realmente excluir o tipo de documento &quot;<?php echo $tpd_nome; ?>&quot;?')){window.location.href='cadastro_tipos_docs.php?pagina=cadastro_tipos_docs&action=excluir&tpd_id=<?php echo $tpd_id . $autenticacao; ?>';}
					">
                        <img border="0" src="../imagens/icon-excluir.png" alt="Excluir">
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php
				$totalPaginas = ceil($totalRegistros / $itensPorPagina);
				if ($totalPaginas > 1): ?>
        <div class="paginacao">
            <?php for ($i = 1; $i <= $totalPaginas; $i++):
							$active = $i == $pag ? "style='font-weight:bold;'" : "";
							$url = "cadastro_tipos_docs.php?pagina=cadastro_tipos_docs&pag=$i$autenticacao";
							?>
            <a href="<?php echo $url; ?>" <?php echo $active; ?>><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <br><br><br>Não há nenhum tipo de documento cadastrado.
        <?php endif; ?>
        <div class="titulo"></div>
    </div>
    <?php
	}

	// Formulário de adição
	if ($pagina === 'adicionar_cadastro_tipos_docs') {
		?>
    <form name="form_cadastro_tipos_docs" id="form_cadastro_tipos_docs" enctype="multipart/form-data" method="post"
        action="cadastro_tipos_docs.php?pagina=cadastro_tipos_docs&action=adicionar<?php echo $autenticacao; ?>">
        <div class="centro">
            <div class="titulo"> <?php echo $pageBreadcrumb; ?> &raquo; Adicionar </div>
            <table align="center" cellspacing="0" width="500">
                <tr>
                    <td align="center">
                        <input name="tpd_nome" id="tpd_nome" placeholder="Nome do Documento" required>
                        <p>
                            <center>
                                <div id="erro" align="center">&nbsp;</div>
                                <input type="submit" id="bt_cadastro_tipos_docs"
                                    value="Salvar" />&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="button" id="botao_cancelar"
                                    onclick="window.location.href='cadastro_tipos_docs.php?pagina=cadastro_tipos_docs<?php echo $autenticacao; ?>';"
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
	?>
</body>

</html>