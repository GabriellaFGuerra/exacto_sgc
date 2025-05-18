<?php
session_start (); 
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');


$ip = "localhost";
$user = "sistemae_admin"; 
$senha = "infomogi123";
$db = "sistemae_sistema";
/*
$ip = "bd-mogicomp.sytes.net:3030";
$user = "root"; 
$senha = "m0507c1106";
$db = "exacto";*/

$conexao =  mysql_connect("$ip","$user","$senha");
if($conexao)
{       
	if( !  mysql_select_db("$db",$conexao)  )
	{       
		die( mysql_error($conexao)); 
    }
}
else
{
	die('Não foi possível conectar ao banco de dados.');     
}
mysql_query("SET NAMES 'utf8'");
mysql_query('SET character_set_connection=utf8');
mysql_query('SET character_set_client=utf8');
mysql_query('SET character_set_results=utf8');
$meses = array(
    '01'=>'Janeiro',
    '02'=>'Fevereiro',
    '03'=>'Março',
    '04'=>'Abril',
    '05'=>'Maio',
    '06'=>'Junho',
    '07'=>'Julho',
    '08'=>'Agosto',
    '09'=>'Setembro',
    '10'=>'Outubro',
    '11'=>'Novembro',
    '12'=>'Dezembro'
);

$login = $_GET['login'];
$n = $_GET['n'];
$autenticacao = "&login=$login&n=".str_replace(' ','%20',$n);
$pagina = $_GET['pagina'];
$rec_id = $_GET['rec_id'];
$sqledit = "SELECT * FROM recurso_gerenciar 
			LEFT JOIN ( infracoes_gerenciar 
					LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = infracoes_gerenciar.inf_cliente	)
				ON infracoes_gerenciar.inf_id = recurso_gerenciar.rec_infracao
			WHERE rec_id = '$rec_id'";
$queryedit = mysql_query($sqledit,$conexao);
$rowsedit = mysql_num_rows($queryedit);
if($rowsedit > 0)
{
	$rec_id = mysql_result($queryedit, 0, 'rec_id');
	$inf_cliente = mysql_result($queryedit, 0, 'inf_cliente');
	$cli_foto = mysql_result($queryedit, 0, 'cli_foto');
	$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
	$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
	$rec_assunto = mysql_result($queryedit, 0, 'rec_assunto');
	$rec_descricao = mysql_result($queryedit, 0, 'rec_descricao');
	$inf_cidade = mysql_result($queryedit, 0, 'inf_cidade');
	$inf_data = implode("/",array_reverse(explode("-",mysql_result($queryedit, 0, 'inf_data'))));
	$inf_proprietario = mysql_result($queryedit, 0, 'inf_proprietario');
	$inf_apto = mysql_result($queryedit, 0, 'inf_apto');
	$inf_bloco = mysql_result($queryedit, 0, 'inf_bloco');
	$inf_endereco = mysql_result($queryedit, 0, 'inf_endereco');
	$inf_email = mysql_result($queryedit, 0, 'inf_email');
	
	
}
//header("Content-Type: text/html; charset=utf-8", true); 
ob_start();  //inicia o buffer
?>
<!--<img src='../imagens/topopdf.png'>-->
<style>
.topo 			{ margin:0 auto; text-align:center; padding: 0 0 15px 0;}
.rodape 		{ margin:0 auto; text-align:left; padding: 15px 0 0 0; font-family:"Calibri";}
.rod			{ color: #999; font-size:13px; font-family:"Calibri"; }
.titulo_adm		{ width:960px; margin:0 auto; font-size:18px; color:#999; text-align:left; border-bottom:1px dashed #DDD; padding:0 0 10px 10px; margin:20px 0 10px 0;}
.laudo			{ font-family:"Calibri"; font-size:13px;  -webkit-print-color-adjust: exact; -moz-border-radius:10px; -webkit-border-radius:10px; border-radius:10px; padding:20px 10px;}
.titulo_laudo	{ font-size:20px; font-family:"sharpmedium";color:#0F72BD; font-weight:bold; text-align:center; }
.titulo_tabela	{ font-size:13px; font-family:"Calibri"; border:0; color:#333; background:#EEE;}
.titulo_first	{ font-size:13px; font-family:"Calibri"; border:0; color:#333; background:#EEE; -moz-border-radius:5px 0px 0px 0px; -webkit-border-radius:5px 0px 0px 0px; border-radius:5px 0px 0px 0px;}
.titulo_last	{ font-size:13px; font-family:"Calibri"; border:0; color:#333; background:#EEE; -moz-border-radius:0px 5px 0px 0px; -webkit-border-radius:0px 5px 0px 0px; border-radius:0px 5px 0px 0px;}
.bordatabela	{ border: 1px solid #DADADA; font-size:11px; color:#666;  -moz-border-radius:2px 2px 0px 0px; -webkit-border-radius:2px 2px 0px 0px; border-radius:2px 2px 0px 0px;}
.formtitulo		{ font-family:"Calibri"; text-align:left; font-size:16px; color:#81C566; padding:25px 0px 0px 0px; }
.label			{ font-family:"Calibri"; font-weight:bold;}
.label2			{ font-family:"Calibri"; font-weight:bold; font-size:16px;}
.cliente		{ font-family:"Calibri"; font-weight:normal; font-size:11px; color:#000;}
.azul			{ color:#0F72BD;}
.laranja		{ color:#F60; font-weight:bold;}
.verde			{ color:#81C566; font-weight:bold;}
.vermelho		{ color:#900; font-weight:bold;}
.italic			{ font-style:italic;}
.linhapar			{ background:#FAFAFA; }
.linhaimpar			{ background:#FFFFFF; }

.topo2			{ float:left; width:33%; text-align:center; font-size:15px; font-family:"sharpmedium";color:#0F72BD; font-weight:bold;}

#resultados_anteriores		{ border-collapse:collapse; width:1000px; }
#resultados_anteriores tr td{ border: 1px solid #CCC; text-align:center;}
#resultados_anteriores .titulo_ant{ background:#EEE; text-align:center;}
#resultados_anteriores .esquerda{ text-align:left;}

</style>
<?php	
	echo "
	
	<div class='laudo'>				
		
					$inf_cidade, $inf_data 
					<br><br>
					<b>Proprietário(a):</b> $inf_proprietario 
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<b>Unidade:</b> $inf_apto
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
					<b>Bloco/Quadra:</b> $inf_bloco
					<br>
					<br>
					<b>Endereço:</b> $inf_endereco 
					<br>
					<br>
					<b>Email:</b> $inf_email 
					<br>
					<br>
					$rec_assunto
					<br>
					<br>
					$rec_descricao
				
		<br>							
	</div>
	
	";
$html = ob_get_clean();
$html = utf8_encode($html);

define('MPDF_PATH', '../mod_includes/js/mpdf/');
include(MPDF_PATH.'mpdf.php');
$mpdf = new mPDF(
 '',    // mode - default ''
 'A4',    // format - A4, for example, default ''
 0,     // font size - default 0
 '',    // default font family
 10,    // margin_left
 10,    // margin right
 35,     // margin top
 30,    // margin bottom
 5,     // margin header
 15,     // margin footer
 'P');  // L - landscape, P - portrait);
$mpdf->SetTitle('Exacto Adm | Imprimir Carta Deferimento/Indeferimento');
$mpdf->useOddEven = false;
$mpdf->SetHTMLHeader('<div class="topo2"><img src='.$cli_foto.' height="100"></div><div class="topo2"></div><div class="topo2"></div>'); 
$mpdf->SetHTMLFooter('
<div class=rodape>
<table align=center class=rod width="100%">
<tr>
<td colspan=2 align=left>
<br>
Atenciosamente,
<br>
'.$cli_nome_razao.'
</td>
</tr>
</table>
</div>
');

$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='UTF-8';
$mpdf->WriteHTML(utf8_decode("$html"));
//$mpdf->AddPage();
$mpdf->SetImportUse(); 


$mpdf->Output('recurso_'.str_pad($rec_id,6,'0',STR_PAD_LEFT).'.pdf','I');
exit();
?>