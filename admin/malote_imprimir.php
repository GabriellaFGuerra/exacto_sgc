<?php
session_start (); 
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

$ip = "localhost";
$user = "sistemae_admin"; 
$senha = "infomogi123";
$db = "sistemae_sistema";


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
$mal_id = $_GET['mal_id'];
$sqledit = "SELECT * FROM malote_gerenciar 
			LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = malote_gerenciar.mal_cliente
			WHERE mal_id = '$mal_id'";
$queryedit = mysql_query($sqledit,$conexao);
$rowsedit = mysql_num_rows($queryedit);
if($rowsedit > 0)
{
	$mal_id = mysql_result($queryedit, 0, 'mal_id');
	$mal_lacre = mysql_result($queryedit, 0, 'mal_lacre');
	$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
	$mal_observacoes = mysql_result($queryedit, 0, 'mal_observacoes');
	$mal_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($queryedit, 0, 'mal_data_cadastro'),0,10))));
	$mal_hora_cadastro = substr(mysql_result($queryedit, 0, 'mal_data_cadastro'),11,5);
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
.bordatabela		{ border: 1px solid #DADADA; font-size:11px; color:#666;  -moz-border-radius:2px 2px 0px 0px; -webkit-border-radius:2px 2px 0px 0px; border-radius:2px 2px 0px 0px;}
.formtitulo		{ font-family:"Calibri"; text-align:left; font-size:16px; color:#81C566; padding:25px 0px 0px 0px; }
.label			{ font-family:"Calibri"; font-weight:bold;}
.label2			{ font-family:"Calibri"; font-weight:bold; font-size:16px;}
.azul			{ color:#0F72BD;}
.laranja		{ color:#F60; font-weight:bold;}
.verde			{ color:#81C566; font-weight:bold;}
.vermelho		{ color:#900; font-weight:bold;}

.linhapar			{ background:#FAFAFA; }
.linhaimpar			{ background:#FFFFFF; }
.titulo_tabela2		{ font-size:11px; font-weight:bold; border:0; color:#666; background:#FAFAFA;}

#resultados_anteriores		{ border-collapse:collapse; width:1000px; }
#resultados_anteriores tr td{ border: 1px solid #CCC; text-align:center;}
#resultados_anteriores .titulo_ant{ background:#EEE; text-align:center;}
#resultados_anteriores .esquerda{ text-align:left;}

</style>
<?php	
	echo "
	<table align='center' border='0'  cellspacing='0' cellpadding='0'>
		<tr>
			<td align='left'>
				<div class='laudo'>
				<table class='laudo' align='center' cellspacing='0' cellpadding='3' width='1000'>
					<tr>
						<td colspan='2' align='center'>
							<span class='titulo_laudo'>Protocolo de Envio</span> 
							<br>&nbsp;
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							<table class='bordatabela' cellspacing='0' cellpadding='3' width='1000'>
								<tr>
									<td colspan='4' height='60' class='label2' align='center'>
										$cli_nome_razao 
									</td>
								</tr>
								<tr>
									<td width='20%' class='label' align='right'>
										 Data de Envio: 
									</td>
									<td colspan='3' >
										 $mal_data_cadastro
									</td>
								</tr>
								<tr>
									<td width='20%' class='label' align='right'>
										 Malote N°: 
									</td>
									<td>
										 $mal_id
									</td>
									<td width='20%' class='label' align='right'>
										N° Lacre: 
									</td>
									<td>
										 $mal_lacre
									</td>
								</tr>
								<tr>
									<td colspan='4'  valign='top'>
										 <table class='bordatabela' cellpadding='5' cellspacing='0' width='1000'>
										<tr>
											<td class='titulo_tabela2'>Fornecedor</td>
											<td class='titulo_tabela2'>Tipo Documento</td>
											<td class='titulo_tabela2'>N° Cheque</td>
											<td class='titulo_tabela2'>Valor</td>
											<td class='titulo_tabela2' align='center'>Data Vencimento</td>
										</tr>
										";
										$sql_itens = "SELECT * FROM  malote_itens WHERE mai_malote = $mal_id ";
										$query_itens = mysql_query($sql_itens,$conexao);
										$rows_itens = mysql_num_rows($query_itens);
										if($rows_itens > 0)
										{
											for($y=0; $y < $rows_itens; $y++)
											{
												$mai_fornecedor = mysql_result($query_itens,$y,'mai_fornecedor');
												$mai_tipo_documento = mysql_result($query_itens,$y,'mai_tipo_documento');
												$mai_num_cheque = mysql_result($query_itens,$y,'mai_num_cheque');
												$mai_valor = number_format(mysql_result($query_itens,$y,'mai_valor'),2,',','.');
												$mai_data_vencimento = implode("/",array_reverse(explode("-",mysql_result($query_itens,$y,'mai_data_vencimento'))));
												if ($c == 0){$c1 = "linhaimpar";$c=1;}else{$c1 = "linhapar";$c=0;} 
												echo "
												<tr class='$c1'>
													<td>".$mai_fornecedor."</td>
													<td>".$mai_tipo_documento."</td>
													<td>".$mai_num_cheque."</td>
													<td>R$ ".$mai_valor."</td>
													<td align='center'>".$mai_data_vencimento."</td>
												</tr>
												";
											}
										}
										echo "
									</table>
									</td>
								</tr>								
								<tr>
									<td colspan='4' align='center' class='italic'>
										<br>
										Data de Envio _______/_______/____________
										<br><br><br>
										______________________________________________<br>
										Responsábel pelo envio
									</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							 
						</td>
					</tr>
				</table>
				</div>
			</td>
		</tr>
	</table>
	<table align='center' border='0'  cellspacing='0' cellpadding='0'>
		<tr>
			<td align='left'>
				<div class='laudo'>
				<table class='laudo' align='center' cellspacing='0' cellpadding='3' width='1000'>
					<tr>
						<td colspan='2'>
							<br>
							<img src=../imagens/linha.png />
							<br>
							<div class='topo'><center><img src=../imagens/logo.png width='200'></center><br><br></div>
						</td>
					</tr>
					<tr>
						<td colspan='2' align='center'>
							<span class='titulo_laudo'>Protocolo de Devolução</span> 
							<br>&nbsp;
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							<table class='bordatabela' cellspacing='0' cellpadding='3' width='1000'>
								<tr>
									<td colspan='4' height='60' class='label2' align='center'>
										$cli_nome_razao 
									</td>
								</tr>
								<tr>
									<td width='20%' class='label' align='right'>
										 Data de Devolução: 
									</td>
									<td colspan='3' >
										 _______/_______/____________
									</td>
								</tr>
								<tr>
									<td width='20%' class='label' align='right'>
										 Malote N°: 
									</td>
									<td>
										 $mal_id
									</td>
									<td width='20%' class='label' align='right'>
										N° Lacre: 
									</td>
									<td>
										 $mal_lacre
									</td>
								</tr>
								<tr>
									<td colspan='4'  valign='top'>
										 <table class='bordatabela' cellpadding='5' cellspacing='0' width='1000'>
										<tr>
											<td class='titulo_tabela2'>Fornecedor</td>
											<td class='titulo_tabela2'>Tipo Documento</td>
											<td class='titulo_tabela2'>N° Cheque</td>
											<td class='titulo_tabela2'>Valor</td>
											<td class='titulo_tabela2' align='center'>Data Vencimento</td>
										</tr>
										";
										$sql_itens = "SELECT * FROM  malote_itens WHERE mai_malote = $mal_id ";
										$query_itens = mysql_query($sql_itens,$conexao);
										$rows_itens = mysql_num_rows($query_itens);
										if($rows_itens > 0)
										{
											for($y=0; $y < $rows_itens; $y++)
											{
												$mai_fornecedor = mysql_result($query_itens,$y,'mai_fornecedor');
												$mai_tipo_documento = mysql_result($query_itens,$y,'mai_tipo_documento');
												$mai_num_cheque = mysql_result($query_itens,$y,'mai_num_cheque');
												$mai_valor = number_format(mysql_result($query_itens,$y,'mai_valor'),2,',','.');
												$mai_data_vencimento = implode("/",array_reverse(explode("-",mysql_result($query_itens,$y,'mai_data_vencimento'))));
												if ($c == 0){$c1 = "linhaimpar";$c=1;}else{$c1 = "linhapar";$c=0;} 
												echo "
												<tr class='$c1'>
													<td>".$mai_fornecedor."</td>
													<td>".$mai_tipo_documento."</td>
													<td>".$mai_num_cheque."</td>
													<td>R$ ".$mai_valor."</td>
													<td align='center'>".$mai_data_vencimento."</td>
												</tr>
												";
											}
										}
										echo "
									</table>
									</td>
								</tr>								
								<tr>
									<td colspan='4' align='center' class='italic'>
										<br>
										Data de Devolução _______/_______/____________
										<br><br><br>
										______________________________________________<br>
										Síndico
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
				</div>
				<div class='titulo_adm'>   </div>
			</td>
		</tr>
	</table>
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
 25,     // margin top
 23,    // margin bottom
 5,     // margin header
 5,     // margin footer
 'P');  // L - landscape, P - portrait);
$mpdf->SetTitle('Exacto Adm | Imprimir Malote');
$mpdf->useOddEven = false;
$mpdf->SetHTMLHeader('<div class="topo"><img src=../imagens/logo.png width="200"><br><br></div>'); 
$mpdf->SetHTMLFooter('<div class=rodape>
<table align=center class=rod width="100%">
<tr>
<td colspan=2 align=center>
</td>
</tr>
<tr>
<td colspan=2 align=right>
{PAGENO} / {nbpg}
</td>
</tr>
</table>
</div>');

$mpdf->allow_charset_conversion=true;
$mpdf->charset_in='UTF-8';
$mpdf->WriteHTML(utf8_decode("$html"));
//$mpdf->AddPage();
$mpdf->SetImportUse(); 
$mpdf->Output('Orçamento_'.str_pad($orc_id,6,'0',STR_PAD_LEFT).'.pdf','I');
exit();
?>