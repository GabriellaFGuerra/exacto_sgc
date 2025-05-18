<?php
session_start();
$pagina_link = 'cadastro_clientes_inativos';
include('../mod_includes/php/connect.php'); // $pdo deve estar disponível aqui
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title><?php echo $titulo ?? ''; ?></title>
	<meta name="author" content="MogiComp">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link rel="shortcut icon" href="../imagens/favicon.png">
	<?php include("../css/style.php"); ?>
	<script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
	<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
	<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
	<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
	<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
</head>

<body>
	<?php
	include('../mod_includes/php/funcoes-jquery.php');
	require_once('../mod_includes/php/verificalogin.php');
	include("../mod_topo/topo.php");
	require_once('../mod_includes/php/verificapermissao.php');

	$action = $_GET['action'] ?? '';
	$pagina = $_GET['pagina'] ?? '';
	$pag = isset($_GET['pag']) ? (int) $_GET['pag'] : 1;
	$autenticacao = $_GET['autenticacao'] ?? '';

	$page = "Cadastros &raquo; <a href='cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos$autenticacao'>Clientes Inativos</a>";

	if ($action === "adicionar") {
		$cli_nome_razao = $_POST['cli_nome_razao'] ?? '';
		$cli_cnpj = $_POST['cli_cnpj'] ?? '';
		$cli_cep = $_POST['cli_cep'] ?? '';
		$cli_uf = $_POST['cli_uf'] ?? '';
		$cli_municipio = $_POST['cli_municipio'] ?? '';
		$cli_bairro = $_POST['cli_bairro'] ?? '';
		$cli_endereco = $_POST['cli_endereco'] ?? '';
		$cli_numero = $_POST['cli_numero'] ?? '';
		$cli_comp = $_POST['cli_comp'] ?? '';
		$cli_telefone = $_POST['cli_telefone'] ?? '';
		$cli_email = $_POST['cli_email'] ?? '';
		$cli_senha = password_hash($_POST['cli_senha'] ?? '', PASSWORD_DEFAULT);
		$cli_status = $_POST['cli_status'] ?? 0;

		$stmt = $pdo->prepare("INSERT INTO cadastro_clientes (
		cli_nome_razao, cli_cnpj, cli_cep, cli_uf, cli_municipio, cli_bairro, cli_endereco, cli_numero, cli_comp, cli_telefone, cli_email, cli_senha, cli_status
	) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$ok = $stmt->execute([
			$cli_nome_razao,
			$cli_cnpj,
			$cli_cep,
			$cli_uf,
			$cli_municipio,
			$cli_bairro,
			$cli_endereco,
			$cli_numero,
			$cli_comp,
			$cli_telefone,
			$cli_email,
			$cli_senha,
			$cli_status
		]);
		if ($ok) {
			$ultimo_id = $pdo->lastInsertId();
			$caminho = "../admin/clientes/";
			$arquivo = '';
			if (!empty($_FILES['cli_foto']['name'][0])) {
				if (!file_exists($caminho)) {
					mkdir($caminho, 0755, true);
				}
				$nomeArquivo = $_FILES['cli_foto']['name'][0];
				$nomeTemporario = $_FILES['cli_foto']['tmp_name'][0];
				$extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
				$arquivo = $caminho . md5(mt_rand(1, 10000) . $nomeArquivo) . '.' . $extensao;
				move_uploaded_file($nomeTemporario, $arquivo);
				$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?");
				$stmt->execute([$arquivo, $ultimo_id]);
			}
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br>'+
			'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
		";
		}
	}

	if ($action === 'editar') {
		$cli_id = $_GET['cli_id'] ?? 0;
		$cli_nome_razao = $_POST['cli_nome_razao'] ?? '';
		$cli_cnpj = $_POST['cli_cnpj'] ?? '';
		$cli_cep = $_POST['cli_cep'] ?? '';
		$cli_uf = $_POST['cli_uf'] ?? '';
		$cli_municipio = $_POST['cli_municipio'] ?? '';
		$cli_bairro = $_POST['cli_bairro'] ?? '';
		$cli_endereco = $_POST['cli_endereco'] ?? '';
		$cli_numero = $_POST['cli_numero'] ?? '';
		$cli_comp = $_POST['cli_comp'] ?? '';
		$cli_telefone = $_POST['cli_telefone'] ?? '';
		$cli_email = $_POST['cli_email'] ?? '';
		$cli_senha_post = $_POST['cli_senha'] ?? '';
		$cli_status = $_POST['cli_status'] ?? 0;

		$stmt = $pdo->prepare("SELECT cli_senha FROM cadastro_clientes WHERE cli_id = ?");
		$stmt->execute([$cli_id]);
		$row = $stmt->fetch(PDO::FETCH_ASSOC);
		$senhacompara = $row['cli_senha'] ?? '';

		// Se a senha não mudou, mantém a antiga
		if (password_verify($cli_senha_post, $senhacompara)) {
			$cli_senha = $senhacompara;
		} else {
			$cli_senha = password_hash($cli_senha_post, PASSWORD_DEFAULT);
		}

		$stmt = $pdo->prepare("UPDATE cadastro_clientes SET
		cli_nome_razao = ?, cli_cnpj = ?, cli_cep = ?, cli_uf = ?, cli_municipio = ?, cli_bairro = ?, cli_endereco = ?, cli_numero = ?, cli_comp = ?, cli_telefone = ?, cli_email = ?, cli_senha = ?, cli_status = ?
		WHERE cli_id = ?");
		$ok = $stmt->execute([
			$cli_nome_razao,
			$cli_cnpj,
			$cli_cep,
			$cli_uf,
			$cli_municipio,
			$cli_bairro,
			$cli_endereco,
			$cli_numero,
			$cli_comp,
			$cli_telefone,
			$cli_email,
			$cli_senha,
			$cli_status,
			$cli_id
		]);

		if ($ok) {
			$caminho = "../admin/clientes/";
			if (!empty($_FILES['cli_foto']['name'][0])) {
				if (!file_exists($caminho)) {
					mkdir($caminho, 0755, true);
				}
				$nomeArquivo = $_FILES['cli_foto']['name'][0];
				$nomeTemporario = $_FILES['cli_foto']['tmp_name'][0];
				$extensao = pathinfo($nomeArquivo, PATHINFO_EXTENSION);
				$arquivo = $caminho . md5(mt_rand(1, 10000) . $nomeArquivo) . '.' . $extensao;

				// Remove foto antiga
				$stmt = $pdo->prepare("SELECT cli_foto FROM cadastro_clientes WHERE cli_id = ?");
				$stmt->execute([$cli_id]);
				$row = $stmt->fetch(PDO::FETCH_ASSOC);
				if (!empty($row['cli_foto']) && file_exists($row['cli_foto'])) {
					unlink($row['cli_foto']);
				}

				move_uploaded_file($nomeTemporario, $arquivo);
				$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_foto = ? WHERE cli_id = ?");
				$stmt->execute([$arquivo, $cli_id]);
			}
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br>'+
			'<input value=\' Ok \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');
		</SCRIPT>
		";
		}
	}

	if ($action === 'excluir') {
		$cli_id = $_GET['cli_id'] ?? 0;
		$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_deletado = 0 WHERE cli_id = ?");
		$ok = $stmt->execute([$cli_id]);
		if ($ok) {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Este item não pode ser excluído pois está relacionado com alguma tabela.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
		}
	}

	if ($action === 'ativar') {
		$cli_id = $_GET['cli_id'] ?? 0;
		$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_status = 1 WHERE cli_id = ?");
		$ok = $stmt->execute([$cli_id]);
		if ($ok) {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Ativação realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
		}
	}

	if ($action === 'desativar') {
		$cli_id = $_GET['cli_id'] ?? 0;
		$stmt = $pdo->prepare("UPDATE cadastro_clientes SET cli_status = 0 WHERE cli_id = ?");
		$ok = $stmt->execute([$cli_id]);
		if ($ok) {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/ok.png> Desativação realizada com sucesso<br><br>'+
			'<input value=\' OK \' type=\'button\' class=\'close_janela\'>' );
		</SCRIPT>
		";
		} else {
			echo "
		<SCRIPT language='JavaScript'>
			abreMask(
			'<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br>'+
			'<input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');
		</SCRIPT>
		";
		}
	}

	$num_por_pagina = 10;
	$primeiro_registro = ($pag - 1) * $num_por_pagina;
	$fil_nome = $_REQUEST['fil_nome'] ?? '';
	$fil_cli_cnpj = str_replace([".", "-"], "", $_REQUEST['fil_cli_cnpj'] ?? '');

	$nome_query = $fil_nome !== '' ? " AND cli_nome_razao LIKE :fil_nome " : "";
	$cnpj_query = $fil_cli_cnpj !== '' ? " AND REPLACE(REPLACE(cli_cnpj, '.', ''), '-', '') LIKE :fil_cli_cnpj " : "";

	$sql = "SELECT * FROM cadastro_clientes 
		INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
		WHERE cli_status = 0 AND cli_deletado = 1 AND ucl_usuario = :usuario_id $nome_query $cnpj_query
		ORDER BY cli_nome_razao ASC
		LIMIT $primeiro_registro, $num_por_pagina";
	$stmt = $pdo->prepare($sql);
	$stmt->bindValue(':usuario_id', $_SESSION['usuario_id']);
	if ($fil_nome !== '')
		$stmt->bindValue(':fil_nome', "%$fil_nome%");
	if ($fil_cli_cnpj !== '')
		$stmt->bindValue(':fil_cli_cnpj', "%$fil_cli_cnpj%");
	$stmt->execute();
	$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

	if ($pagina === "cadastro_clientes_inativos") {
		echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div class='filtro'>
			<form name='form_filtro' id='form_filtro' enctype='multipart/form-data' method='post' action='cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos$autenticacao'>
			<input name='fil_nome' id='fil_nome' value='$fil_nome' placeholder='Nome/Razão Social'>
			<input type='text' name='fil_cli_cnpj' id='fil_cli_cnpj' placeholder='C.N.P.J' value='$fil_cli_cnpj'>						
			<input type='submit' value='Filtrar'> 
			</form>
		</div>
	";
		if (count($clientes) > 0) {
			echo "
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Logo</td>
				<td class='titulo_tabela'>Razão Social</td>
				<td class='titulo_tabela'>CNPJ</td>
				<td class='titulo_tabela'>Telefone</td>
				<td class='titulo_tabela'>Email</td>
				<td class='titulo_tabela'>Status</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>";
			$c = 0;
			foreach ($clientes as $cliente) {
				$cli_id = $cliente['cli_id'];
				$cli_nome_razao = htmlspecialchars($cliente['cli_nome_razao']);
				$cli_cnpj = htmlspecialchars($cliente['cli_cnpj']);
				$cli_telefone = htmlspecialchars($cliente['cli_telefone']);
				$cli_email = htmlspecialchars($cliente['cli_email']);
				$cli_foto = $cliente['cli_foto'] ?: '../imagens/nophoto.png';
				$cli_status = $cliente['cli_status'];
				$c1 = $c % 2 == 0 ? "linhaimpar" : "linhapar";
				$c++;

				echo "
			<script type='text/javascript'>
				jQuery(document).ready(function($) {
					$('#normal-button-$cli_id').toolbar({content: '#user-options-$cli_id', position: 'top', hideOnClick: true});
				});
			</script>
			<div id='user-options-$cli_id' class='toolbar-icons' style='display: none;'>";
				if ($cli_status == 1) {
					echo "<a href='cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=desativar&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
				} else {
					echo "<a href='cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=ativar&cli_id=$cli_id$autenticacao'><img border='0' src='../imagens/icon-ativa-desativa.png'></a>";
				}
				echo "
				<a onclick=\"
					abreMask(
						'Deseja realmente excluir o cliente <b>$cli_nome_razao</b>?<br><br>'+
						'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\'cadastro_clientes_inativos.php?pagina=cadastro_clientes_inativos&action=excluir&cli_id=$cli_id$autenticacao\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\' Não \' type=\'button\' class=\'close_janela\'>' );
					\">
					<img border='0' src='../imagens/icon-excluir.png'>
				</a>
			</div>
			";
				echo "<tr class='$c1'>
				<td><img src='" . htmlspecialchars($cli_foto) . "' width='100'></td>
				<td>$cli_nome_razao</td>
				<td>$cli_cnpj</td>
				<td>$cli_telefone</td>
				<td>$cli_email</td>
				<td align=center>";
				if ($cli_status == 1) {
					echo "<img border='0' src='../imagens/icon-ativo.png' width='15' height='15'>";
				} else {
					echo "<img border='0' src='../imagens/icon-inativo.png' width='15' height='15'>";
				}
				echo "
				</td>
				<td align=center><div id='normal-button-$cli_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
			</tr>";
			}
			echo "</table>";
			$variavel = "&pagina=cadastro_clientes_inativos$autenticacao";
			include("../mod_includes/php/paginacao.php");
		} else {
			echo "<br><br><br>Não há nenhum cliente inativo.";
		}
		echo "
	<div class='titulo'>  </div>				
	</div>";
	}

	include('../mod_rodape/rodape.php');
	?>
</body>

</html>