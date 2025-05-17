<?php
include('connect.php');

// Obter total de registros com PDO
$stmt = $pdo->prepare($cnt);
$stmt->execute();
$total_linhas = $stmt->fetchColumn();

$limite = 1;
$total = $total_linhas / $num_por_pagina;
$prox = $pag + 1;
$ant = $pag - 1;
$ultima_pag = ceil($total / $limite);
$penultima = $ultima_pag - 1;
$adjacentes = 3;
$paginacao = '';

if ($pag > 1) {
	$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $ant . $variavel . '">
                        <img src="../imagens/icon-anterior.png" width="16" border="0">
                     </a> ';
}

if ($ultima_pag <= 10) {
	for ($i = 1; $i <= $ultima_pag; $i++) {
		$paginacao .= ($i == $pag)
			? ' <span class="atual">[' . $i . ']</span> '
			: ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $i . $variavel . '">' . $i . '</a> ';
	}
} else {
	if ($pag < 1 + (2 * $adjacentes)) {
		for ($i = 1; $i < 2 + (2 * $adjacentes); $i++) {
			$paginacao .= ($i == $pag)
				? ' <span class="atual">[' . $i . ']</span> '
				: ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $i . $variavel . '">' . $i . '</a> ';
		}
		$paginacao .= ' ... ';
		$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $penultima . $variavel . '">' . $penultima . '</a> ';
		$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $ultima_pag . $variavel . '">' . $ultima_pag . '</a> ';
	} elseif ($pag > (2 * $adjacentes) && $pag < $ultima_pag - 3) {
		$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=1' . $variavel . '">1</a> ';
		$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=2' . $variavel . '">2</a> ... ';
		for ($i = $pag - $adjacentes; $i <= $pag + $adjacentes; $i++) {
			$paginacao .= ($i == $pag)
				? ' <span class="atual">[' . $i . ']</span> '
				: ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $i . $variavel . '">' . $i . '</a> ';
		}
		$paginacao .= ' ... ';
		$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $penultima . $variavel . '">' . $penultima . '</a> ';
		$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $ultima_pag . $variavel . '">' . $ultima_pag . '</a> ';
	} else {
		$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=1' . $variavel . '">1</a> ';
		$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=2' . $variavel . '">2</a> ... ';
		for ($i = $ultima_pag - (1 + (2 * $adjacentes)); $i <= $ultima_pag; $i++) {
			$paginacao .= ($i == $pag)
				? ' <span class="atual">[' . $i . ']</span> '
				: ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $i . $variavel . '">' . $i . '</a> ';
		}
	}
}

if ($prox <= $ultima_pag && $ultima_pag > 2) {
	$paginacao .= ' <a href="' . htmlspecialchars($_SERVER['PHP_SELF']) . '?pag=' . $prox . $variavel . '">
                        <img src="../imagens/icon-proxima.png" width="16" border="0">
                    </a> ';
}

echo "<center>$paginacao</center>";
?>