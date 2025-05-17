<?php
$login = $_GET['login'];
$n = $_GET['n'];
$pagina = $_GET['pagina'];
$action = $_GET['action'];
$pag = $_GET['pag'];
$autenticacao = "&login=$login&n=".str_replace(' ','%20',$n);

$sql_par = 'SELECT * FROM `parametros_gerais`
			LEFT JOIN end_uf ON end_uf.uf_id = `parametros_gerais`.ger_uf
			LEFT JOIN end_municipios ON end_municipios.mun_id = `parametros_gerais`.ger_municipio
			';
$query_par = mysql_query($sql_par, $conexao);
$rows_par = mysql_num_rows($query_par);
if($rows_par > 0)
{
	$ger_cor_primaria = mysql_result($query_par, 0, 'ger_cor_primaria');
	$ger_cor_secundaria = mysql_result($query_par, 0, 'ger_cor_secundaria');
	$logo = mysql_result($query_par, 0, 'ger_logo');
	$titulo = utf8_encode(mysql_result($query_par, 0, 'ger_nome'));
	$titulo_guia = mysql_result($query_par, 0, 'ger_nome');
	$ger_numeracao_anual = mysql_result($query_par, 0, 'ger_numeracao_anual');
	$ger_guia_anual = mysql_result($query_par, 0, 'ger_guia_anual');
	$ger_status = mysql_result($query_par, 0, 'ger_status');
	$uf_sigla = mysql_result($query_par, 0, 'uf_sigla');
	$ger_municipio = mysql_result($query_par, 0, 'ger_municipio');
	$mun_nome = mysql_result($query_par, 0, 'mun_nome');
	$ger_bairro = mysql_result($query_par, 0, 'ger_bairro');
	$ger_endereco = mysql_result($query_par, 0, 'ger_endereco');
	$ger_numero = mysql_result($query_par, 0, 'ger_numero');
	$ger_comp = mysql_result($query_par, 0, 'ger_comp');
	$ger_telefone = mysql_result($query_par, 0, 'ger_telefone');
	$ger_email = mysql_result($query_par, 0, 'ger_email');
	$ger_site = mysql_result($query_par, 0, 'ger_site');
		
	if($ger_status == 0) { echo "Sistema fora do ar."; exit;}
}
?>
