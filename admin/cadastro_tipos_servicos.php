<?php
session_start();
$pagina_link = 'cadastro_tipos_servicos';
include '../mod_includes/php/connect.php';

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title><?php echo $titulo ?? ''; ?></title>
    <meta name="author" content="MogiComp">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/funcoes.js" type="text/javascript"></script>
    <script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
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

	$page = "Cadastros &raquo; <a href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos$autenticacao'>Tipos de Serviço</a>";

	$action = $_GET['action'] ?? '';
	$pagina = $_GET['pagina'] ?? '';
	$pag = isset($_GET['pag']) ? (int) $_GET['pag'] : 1;

	if ($action === 'adicionar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$tps_nome = $_POST['tps_nome'] ?? '';
		$stmt = $pdo->prepare('INSERT INTO cadastro_tipos_servicos (tps_nome) VALUES (:tps_nome)');
		if ($stmt->execute([':tps_nome' => $tps_nome])) {
			echo "<script>abreMask('<img src=../imagens/ok.png> Cadastro efetuado com sucesso.<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>');</script>";
		} else {
			echo "<script>abreMask('<img src=../imagens/x.png> Erro ao efetuar cadastro, por favor tente novamente.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');</script>";
		}
	}

	if ($action === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
		$tps_id = $_GET['tps_id'] ?? '';
		$tps_nome = $_POST['tps_nome'] ?? '';
		$stmt = $pdo->prepare('UPDATE cadastro_tipos_servicos SET tps_nome = :tps_nome WHERE tps_id = :tps_id');
		if ($stmt->execute([':tps_nome' => $tps_nome, ':tps_id' => $tps_id])) {
			echo "<script>abreMask('<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br><input value=\' Ok \' type=\'button\' class=\'close_janela\'>');</script>";
		} else {
			echo "<script>abreMask('<img src=../imagens/x.png> Erro ao alterar dados, por favor tente novamente.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back();>');</script>";
		}
	}

	if ($action === 'excluir') {
		$tps_id = $_GET['tps_id'] ?? '';
		$stmt = $pdo->prepare('DELETE FROM cadastro_tipos_servicos WHERE tps_id = :tps_id');
		if ($stmt->execute([':tps_id' => $tps_id])) {
			echo "<script>abreMask('<img src=../imagens/ok.png> Exclusão realizada com sucesso<br><br><input value=\' OK \' type=\'button\' class=\'close_janela\'>');</script>";
		} else {
			echo "<script>abreMask('<img src=../imagens/x.png> Este tipo de serviço não pode ser excluído pois está relacionado com alguma tabela.<br><br><input value=\' Ok \' type=\'button\' onclick=javascript:window.history.back(); >');</script>";
		}
	}

	$num_por_pagina = 20;
	$primeiro_registro = ($pag - 1) * $num_por_pagina;

	if ($pagina === 'cadastro_tipos_servicos') {
		$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome ASC LIMIT :offset, :limit');
		$stmt->bindValue(':offset', $primeiro_registro, PDO::PARAM_INT);
		$stmt->bindValue(':limit', $num_por_pagina, PDO::PARAM_INT);
		$stmt->execute();
		$servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

		$total = $pdo->query('SELECT COUNT(*) FROM cadastro_tipos_servicos')->fetchColumn();

		echo "
	<div class='centro'>
		<div class='titulo'> $page  </div>
		<div id='botoes'><input value='Novo Serviço' type='button' onclick=\"window.location.href='cadastro_tipos_servicos.php?pagina=adicionar_cadastro_tipos_servicos$autenticacao';\" /></div>
	";

		if ($servicos) {
			echo "
		<table align='center' width='100%' border='0' cellspacing='0' cellpadding='10' class='bordatabela'>
			<tr>
				<td class='titulo_tabela'>Serviço</td>
				<td class='titulo_tabela' align='center'>Gerenciar</td>
			</tr>";
			$c = 0;
			foreach ($servicos as $servico) {
				$tps_id = $servico['tps_id'];
				$tps_nome = htmlspecialchars($servico['tps_nome']);
				$c1 = $c % 2 == 0 ? 'linhaimpar' : 'linhapar';
				$c++;
				echo "
			<script type='text/javascript'>
				jQuery(document).ready(function($) {
					$('#normal-button-$tps_id').toolbar({content: '#user-options-$tps_id', position: 'top', hideOnClick: true});
				});
			</script>
			<div id='user-options-$tps_id' class='toolbar-icons' style='display: none;'>
				<a href='cadastro_tipos_servicos.php?pagina=editar_cadastro_tipos_servicos&tps_id=$tps_id$autenticacao'><img border='0' src='../imagens/icon-editar.png'></a>
				<a onclick=\"
					abreMask(
						'Deseja realmente excluir o tipo de serviço <b>" . addslashes($tps_nome) . "</b>?<br><br>'+
						'<input value=\' Sim \' type=\'button\' onclick=javascript:window.location.href=\\'cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos&action=excluir&tps_id=$tps_id$autenticacao\\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\' Não \' type=\'button\' class=\'close_janela\'');
					\">
					<img border='0' src='../imagens/icon-excluir.png'>
				</a>
			</div>
			<tr class='$c1'>
				<td>$tps_nome</td>
				<td align=center><div id='normal-button-$tps_id' class='settings-button'><img src='../imagens/icon-cog-small.png' /></div></td>
			</tr>";
			}
			echo '</table>';
			$variavel = "&pagina=cadastro_tipos_servicos$autenticacao";
			include '../mod_includes/php/paginacao.php';
		} else {
			echo '<br><br><br>Não há nenhum tipo de serviço cadastrado.';
		}
		echo "<div class='titulo'>  </div></div>";
	}

	if ($pagina === 'adicionar_cadastro_tipos_servicos') {
		echo "
	<form name='form_cadastro_tipos_servicos' id='form_cadastro_tipos_servicos' enctype='multipart/form-data' method='post' action='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos&action=adicionar$autenticacao'>
	<div class='centro'>
		<div class='titulo'> $page &raquo; Adicionar  </div>
		<table align='center' cellspacing='0' width='500'>
			<tr>
				<td align='center'>
					<input name='tps_nome' id='tps_nome' placeholder='Nome do Serviço'>
					<p>
					<center>
					<div id='erro' align='center'>&nbsp;</div>
					<input type='submit' id='bt_cadastro_tipos_servicos' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
					<input type='button' id='botao_cancelar' onclick=\"window.location.href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos$autenticacao';\" value='Cancelar'/>
					</center>
				</td>
			</tr>
		</table>
		<div class='titulo'> </div>
	</div>
	</form>
	";
	}

	if ($pagina === 'editar_cadastro_tipos_servicos') {
		$tps_id = $_GET['tps_id'] ?? '';
		$stmt = $pdo->prepare('SELECT * FROM cadastro_tipos_servicos WHERE tps_id = :tps_id');
		$stmt->execute([':tps_id' => $tps_id]);
		$servico = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($servico) {
			$tps_nome = htmlspecialchars($servico['tps_nome']);
			echo "
		<form name='form_cadastro_tipos_servicos' id='form_cadastro_tipos_servicos' enctype='multipart/form-data' method='post' action='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos&action=editar&tps_id=$tps_id$autenticacao'>
		<div class='centro'>
			<div class='titulo'> $page &raquo; Editar: $tps_nome </div>
			<table align='center' cellspacing='0'>
				<tr>
					<td align='left'>
						<input name='tps_nome' id='tps_nome' value=\"$tps_nome\" placeholder='Nome do Serviço'>
						<p>
						<center>
						<div id='erro' align='center'>&nbsp;</div>
						<input type='submit' id='bt_cadastro_tipos_servicos' value='Salvar' />&nbsp;&nbsp;&nbsp;&nbsp; 
						<input type='button' id='botao_cancelar' onclick=\"window.location.href='cadastro_tipos_servicos.php?pagina=cadastro_tipos_servicos$autenticacao';\" value='Cancelar'/>
						</center>
					</td>
				</tr>
			</table>
			<div class='titulo'>   </div>
		</div>
		</form>
		";
		}
	}

	include '../mod_rodape/rodape.php';
	?>
</body>

</html>