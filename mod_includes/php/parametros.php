<?php
session_start();

$login = $_GET['login'] ?? '';
$n = $_GET['n'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$action = $_GET['action'] ?? '';
$pag = $_GET['pag'] ?? '';
$autenticacao = '&login=' . urlencode($login) . '&n=' . urlencode($n);

// Consulta segura com PDO
$sql = '
	SELECT * FROM parametros_gerais
	LEFT JOIN end_uf ON end_uf.uf_id = parametros_gerais.ger_uf
	LEFT JOIN end_municipios ON end_municipios.mun_id = parametros_gerais.ger_municipio
';

$stmt = $pdo->prepare($sql);
$stmt->execute();
$params = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$params) {
	return;
}

$ger_cor_primaria = $params['ger_cor_primaria'] ?? '';
$ger_cor_secundaria = $params['ger_cor_secundaria'] ?? '';
$logo = $params['ger_logo'] ?? '';
$titulo = htmlspecialchars($params['ger_nome'] ?? '', ENT_QUOTES, 'UTF-8');
$titulo_guia = $params['ger_nome'] ?? '';
$ger_numeracao_anual = $params['ger_numeracao_anual'] ?? '';
$ger_guia_anual = $params['ger_guia_anual'] ?? '';
$ger_status = $params['ger_status'] ?? '';
$uf_sigla = $params['uf_sigla'] ?? '';
$ger_municipio = $params['ger_municipio'] ?? '';
$mun_nome = $params['mun_nome'] ?? '';
$ger_bairro = $params['ger_bairro'] ?? '';
$ger_endereco = $params['ger_endereco'] ?? '';
$ger_numero = $params['ger_numero'] ?? '';
$ger_comp = $params['ger_comp'] ?? '';
$ger_telefone = $params['ger_telefone'] ?? '';
$ger_email = $params['ger_email'] ?? '';
$ger_site = $params['ger_site'] ?? '';

if ($ger_status == 0) {
	echo 'Sistema fora do ar.';
	exit;
}