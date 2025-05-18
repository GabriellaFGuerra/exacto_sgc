<?php
session_start();

$login = $_GET['login'] ?? '';
$n = $_GET['n'] ?? '';
$pagina = $_GET['pagina'] ?? '';
$action = $_GET['action'] ?? '';
$pag = $_GET['pag'] ?? '';
$autenticacao = "&login=" . urlencode($login) . "&n=" . urlencode($n);

// Consulta segura com PDO
$sql_par = 'SELECT * FROM parametros_gerais
            LEFT JOIN end_uf ON end_uf.uf_id = parametros_gerais.ger_uf
            LEFT JOIN end_municipios ON end_municipios.mun_id = parametros_gerais.ger_municipio';

$stmt = $pdo->prepare($sql_par);
$stmt->execute();
$parametros = $stmt->fetch(PDO::FETCH_ASSOC);

if ($parametros) {
	$ger_cor_primaria = $parametros['ger_cor_primaria'] ?? '';
	$ger_cor_secundaria = $parametros['ger_cor_secundaria'] ?? '';
	$logo = $parametros['ger_logo'] ?? '';
	$titulo = htmlspecialchars($parametros['ger_nome'] ?? '', ENT_QUOTES, 'UTF-8');
	$titulo_guia = $parametros['ger_nome'] ?? '';
	$ger_numeracao_anual = $parametros['ger_numeracao_anual'] ?? '';
	$ger_guia_anual = $parametros['ger_guia_anual'] ?? '';
	$ger_status = $parametros['ger_status'] ?? '';
	$uf_sigla = $parametros['uf_sigla'] ?? '';
	$ger_municipio = $parametros['ger_municipio'] ?? '';
	$mun_nome = $parametros['mun_nome'] ?? '';
	$ger_bairro = $parametros['ger_bairro'] ?? '';
	$ger_endereco = $parametros['ger_endereco'] ?? '';
	$ger_numero = $parametros['ger_numero'] ?? '';
	$ger_comp = $parametros['ger_comp'] ?? '';
	$ger_telefone = $parametros['ger_telefone'] ?? '';
	$ger_email = $parametros['ger_email'] ?? '';
	$ger_site = $parametros['ger_site'] ?? '';

	if ($ger_status == 0) {
		echo "Sistema fora do ar.";
		exit;
	}
}
?>