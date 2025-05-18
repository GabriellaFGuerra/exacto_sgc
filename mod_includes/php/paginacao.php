<?php
require_once 'connect.php';

// Obter total de registros com PDO
$stmt = $pdo->prepare($cnt);
$stmt->execute();
$totalLinhas = $stmt->fetchColumn();

$limite = 1;
$total = $totalLinhas / $num_por_pagina;
$prox = $pag + 1;
$ant = $pag - 1;
$ultimaPag = (int) ceil($total / $limite);
$penultima = $ultimaPag - 1;
$adjacentes = 3;
$paginacao = '';

$self = htmlspecialchars($_SERVER['PHP_SELF']);

if ($pag > 1) {
	$paginacao .= " <a href=\"{$self}?pag={$ant}{$variavel}\"><img src=\"../imagens/icon-anterior.png\" width=\"16\" border=\"0\"></a> ";
}

if ($ultimaPag <= 10) {
	for ($i = 1; $i <= $ultimaPag; $i++) {
		$paginacao .= $i == $pag
			? " <span class=\"atual\">[{$i}]</span> "
			: " <a href=\"{$self}?pag={$i}{$variavel}\">{$i}</a> ";
	}
} else {
	if ($pag < 1 + 2 * $adjacentes) {
		for ($i = 1; $i < 2 + 2 * $adjacentes; $i++) {
			$paginacao .= $i == $pag
				? " <span class=\"atual\">[{$i}]</span> "
				: " <a href=\"{$self}?pag={$i}{$variavel}\">{$i}</a> ";
		}
		$paginacao .= ' ... ';
		$paginacao .= " <a href=\"{$self}?pag={$penultima}{$variavel}\">{$penultima}</a> ";
		$paginacao .= " <a href=\"{$self}?pag={$ultimaPag}{$variavel}\">{$ultimaPag}</a> ";
	} elseif ($pag > 2 * $adjacentes && $pag < $ultimaPag - 3) {
		$paginacao .= " <a href=\"{$self}?pag=1{$variavel}\">1</a> ";
		$paginacao .= " <a href=\"{$self}?pag=2{$variavel}\">2</a> ... ";
		for ($i = $pag - $adjacentes; $i <= $pag + $adjacentes; $i++) {
			$paginacao .= $i == $pag
				? " <span class=\"atual\">[{$i}]</span> "
				: " <a href=\"{$self}?pag={$i}{$variavel}\">{$i}</a> ";
		}
		$paginacao .= ' ... ';
		$paginacao .= " <a href=\"{$self}?pag={$penultima}{$variavel}\">{$penultima}</a> ";
		$paginacao .= " <a href=\"{$self}?pag={$ultimaPag}{$variavel}\">{$ultimaPag}</a> ";
	} else {
		$paginacao .= " <a href=\"{$self}?pag=1{$variavel}\">1</a> ";
		$paginacao .= " <a href=\"{$self}?pag=2{$variavel}\">2</a> ... ";
		for ($i = $ultimaPag - (1 + 2 * $adjacentes); $i <= $ultimaPag; $i++) {
			$paginacao .= $i == $pag
				? " <span class=\"atual\">[{$i}]</span> "
				: " <a href=\"{$self}?pag={$i}{$variavel}\">{$i}</a> ";
		}
	}
}

if ($prox <= $ultimaPag && $ultimaPag > 2) {
	$paginacao .= " <a href=\"{$self}?pag={$prox}{$variavel}\"><img src=\"../imagens/icon-proxima.png\" width=\"16\" border=\"0\"></a> ";
}

echo "<center>{$paginacao}</center>";
