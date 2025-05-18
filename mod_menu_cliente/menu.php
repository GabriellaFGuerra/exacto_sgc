<?php
declare(strict_types=1);

require_once '../mod_includes/php/connect.php';
require_once '../mod_menu_cliente/style_menu.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
	<meta charset="utf-8">
	<title>Menu Cliente</title>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('.menu li').forEach(function (el) {
				el.addEventListener('mouseenter', function () {
					this.classList.add('over');
				});
				el.addEventListener('mouseleave', function () {
					this.classList.remove('over');
				});
			});
		});
	</script>
</head>

<body>
	<div class="containermenu bodytext">
		<div class="textomenu">
			<ul class="menu">
				<li class="top">
					<a href="admin.php?pagina=admin<?= $autenticacao ?>" class="top_link" target="_parent">
						<img src="../imagens/icon-home.png" alt="Início"><br>Início
					</a>
				</li>
			</ul>
			<ul class="menu">
				<li class="top">
					<a href="novo_orcamento.php?pagina=novo_orcamento<?= $autenticacao ?>" class="top_link"
						target="_parent">
						<img src="../imagens/icon-registrar.png" alt="Novo Orçamento"><br>Novo Orçamento
					</a>
				</li>
			</ul>
			<ul class="menu">
				<li class="top">
					<a href="consultar_orcamento.php?pagina=consultar_orcamento<?= $autenticacao ?>" class="top_link"
						target="_parent">
						<img src="../imagens/icon-consultar.png" alt="Consultar Orçamento"><br>Consultar Orçamento
					</a>
				</li>
			</ul>
			<ul class="menu">
				<li class="top">
					<a href="consultar_documento.php?pagina=consultar_documento<?= $autenticacao ?>" class="top_link"
						target="_parent">
						<img src="../imagens/icon-consultar-doc.png" alt="Consultar Documentos"><br>Consultar Documentos
					</a>
				</li>
			</ul>
			<ul class="menu">
				<li class="top">
					<a href="consultar_infracoes.php?pagina=consultar_infracoes<?= $autenticacao ?>" class="top_link"
						target="_parent">
						<img src="../imagens/icon-consultar-infracoes.png" alt="Consultar Infrações"><br>Consultar
						Infrações
					</a>
				</li>
			</ul>
			<ul class="menu">
				<li class="toplast">
					<a href="#" class="top_link" target="_parent" onclick="
						abreMask(
							'Deseja realmente sair do sistema?<br><br>' +
							'<input value=\'Sim\' type=\'button\' onclick=window.location.href=\'logout.php?pagina=logout\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' +
							'<input value=\'Não\' type=\'button\' class=\'close_janela\'>'
						); return false;">
						<img src="../imagens/icon-sair.png" alt="Sair"><br>Sair
					</a>
				</li>
			</ul>
		</div>
	</div>
	<div id="usuario">
		Bem-vindo <span class="nome"><?= htmlspecialchars($n ?? '', ENT_QUOTES, 'UTF-8') ?></span>
	</div>
	<div id="janela" class="janela" style="display:none;"></div>
</body>

</html>