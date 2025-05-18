<?php
declare(strict_types=1);
session_start();
require_once '../mod_includes/php/connect.php';

$setor = $_SESSION['setor'] ?? '';
$autenticacao = $_SESSION['autenticacao'] ?? '';
$n = $_SESSION['nome'] ?? '';
$setorNome = $_SESSION['setor_nome'] ?? '';

function getModulos(PDO $pdo, string $setor): array
{
	$sql = "
		SELECT m.mod_id, m.mod_nome, m.mod_link
		FROM admin_setores_permissoes p
		INNER JOIN admin_modulos m ON m.mod_id = p.sep_modulo
		WHERE p.sep_setor = :setor
		GROUP BY m.mod_id
		ORDER BY m.mod_ordem ASC
	";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['setor' => $setor]);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSubmodulos(PDO $pdo, string $setor, string $modId): array
{
	$sql = "
		SELECT s.sub_id, s.sub_nome, s.sub_link
		FROM admin_setores_permissoes p
		INNER JOIN admin_submodulos s ON s.sub_id = p.sep_submodulo
		WHERE p.sep_setor = :setor AND s.sub_modulo = :modId
		GROUP BY s.sub_id
		ORDER BY s.sub_id ASC
	";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(['setor' => $setor, 'modId' => $modId]);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt">

<head>
	<meta charset="utf-8" />
	<?php include '../mod_menu/style_menu.php'; ?>
	<script>
		jQuery(function () {
			jQuery(".menu li").hover(
				function () {
					jQuery(this).addClass("over");
				},
				function () {
					jQuery(this).removeClass("over");
				}
			);
		});
	</script>
</head>

<body>
	<div class="containermenu bodytext">
		<div class="textomenu">
			<ul class="menu">
				<li class="top" id="admin">
					<a href="admin.php?pagina=admin<?= htmlspecialchars($autenticacao) ?>" class="top_link"
						target="_parent">
						<img src="../imagens/icon-home.png" alt="Home" />
					</a>
				</li>
			</ul>
			<?php foreach (getModulos($pdo, $setor) as $modulo): ?>
				<ul class="menu">
					<li class="top">
						<a href="<?= htmlspecialchars($modulo['mod_link']) ?>" class="top_link" target="_parent">
							<?= htmlspecialchars($modulo['mod_nome']) ?>
						</a>
						<ul class="sub">
							<?php foreach (getSubmodulos($pdo, $setor, $modulo['mod_id']) as $sub): ?>
								<li class="top">
									<a href="<?= htmlspecialchars($sub['sub_link']) ?>.php?pagina=<?= htmlspecialchars($sub['sub_link']) . htmlspecialchars($autenticacao) ?>"
										target="_parent">
										&raquo; <?= htmlspecialchars($sub['sub_nome']) ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				</ul>
			<?php endforeach; ?>
			<ul class="menu">
				<li class="toplast">
					<a onclick="
					abreMask(
						'Deseja realmente sair do sistema?<br><br>'+
						'<input value=\' Sim \' type=\'button\' onclick=window.location.href=\'logout.php?pagina=logout\';>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'+
						'<input value=\' Não \' type=\'button\' class=\'close_janela\'>'
					);
				" class="top_link" target="_parent">Sair</a>
				</li>
			</ul>
		</div>
	</div>
	<div id="usuario">
		Bem-vindo <span class="nome"><?= htmlspecialchars($n) ?></span> | <span
			class="setor"><?= htmlspecialchars($setorNome) ?></span>
	</div>
	<div id="janela" class="janela" style="display:none;"></div>
</body>

</html>