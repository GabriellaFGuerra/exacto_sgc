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
$orc_id = $_GET['orc_id'];
$sqledit = "SELECT * FROM orcamento_gerenciar 
			LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
			LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
            LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id 
			WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 where h2.sto_orcamento = h1.sto_orcamento) AND
				  orc_id = '$orc_id' AND orc_cliente = ".$_SESSION['cliente_id']." ";
$queryedit = mysql_query($sqledit,$conexao);
$rowsedit = mysql_num_rows($queryedit);
if($rowsedit > 0)
{
	$orc_id = mysql_result($queryedit, 0, 'orc_id');
	$orc_cliente = mysql_result($queryedit, 0, 'orc_cliente');
	$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
	$cli_cnpj = mysql_result($queryedit, 0, 'cli_cnpj');
	$orc_tipo_servico = mysql_result($queryedit, 0, 'orc_tipo_servico');
	$tps_nome = mysql_result($queryedit, 0, 'tps_nome');
	if($tps_nome == ''){$tps_nome = mysql_result($queryedit, 0, 'orc_tipo_servico_cliente');}
	$orc_observacoes = mysql_result($queryedit, 0, 'orc_observacoes');
	$sto_status = mysql_result($queryedit, 0, 'sto_status');
	$orc_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($queryedit, 0, 'orc_data_cadastro'),0,10))));
	$orc_hora_cadastro = substr(mysql_result($queryedit, 0, 'orc_data_cadastro'),11,5);
	$orc_data_aprovacao = implode("/",array_reverse(explode("-",mysql_result($queryedit, 0, 'orc_data_aprovacao'))));
	switch($sto_status)
	{
		case 1: $sto_status_n = "<span class='laranja'>Pendente</span>";break;
		case 2: $sto_status_n = "<span class='azul'>Calculado</span>";break;
		case 3: $sto_status_n = "<span class='verde'>Aprovado</span>";break;
		case 4: $sto_status_n = "<span class='vermelho'>Reprovado</span>";break;
	}
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
.azul			{ color:#0F72BD;}
.laranja		{ color:#F60; font-weight:bold;}
.verde			{ color:#81C566; font-weight:bold;}
.vermelho		{ color:#900; font-weight:bold;}

.linhapar			{ background:#FAFAFA; }
.linhaimpar			{ background:#FFFFFF; }

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
							<span class='titulo_laudo'>Cotação de Material/Serviço</span> 
							<br>&nbsp;
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							<table  cellspacing='0' cellpadding='5' width='1000'>
								<tr>
									<td width='20%' class='label' align='right'>
										 Orçamento N°: 
									</td>
									<td>
										 ".str_pad($orc_id,6,'0',STR_PAD_LEFT)."
									</td>
									<td width='30%' class='label' align='right'>
										Status: 
									</td>
									<td>
										 $sto_status_n
									</td>
								</tr>
								<tr>
									<td class='label' align='right'>
										Condomínio: 
									</td>
									<td colspan='3'>
										 $cli_nome_razao
									</td>
								</tr>
								<tr>
									<td class='label' align='right'>
										Referente:
									</td>
									<td colspan='3'>
										 $tps_nome
									</td>
								</tr>
								<tr>
									<td class='label' align='right'>
										Data de cadastro:
									</td>
									<td>
										 $orc_data_cadastro às $orc_hora_cadastro
									</td>
									<td class='label' align='right'>
										Data de aprovação/reprovação:
									</td>
									<td>
										 $orc_data_aprovacao
									</td>
								</tr>
							</table>
						</td>
					<tr>
						<td colspan='2' align='center' class='formtitulo'>
							Empresas Contatadas
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							 <table class='bordatabela' cellpadding='10' cellspacing='0' width='700'>
							 	<tr>
									<td class='titulo_first'>Nome da Empresa</td>
									<td class='titulo_tabela'>Valor</td>
									<td class='titulo_tabela'>INSS (20%)</td>
									<td class='titulo_tabela'>Observação</td>
									<td class='titulo_tabela' align='right'>Total</td>
									<td class='titulo_last'></td>
								</tr>
								";
								$sql_fornecedores = "SELECT * FROM orcamento_fornecedor 
													 LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = orcamento_fornecedor.orf_fornecedor
													 WHERE orf_orcamento = $orc_id ORDER BY orf_id ASC";
								$query_fornecedores = mysql_query($sql_fornecedores,$conexao);
								$rows_fornecedores = mysql_num_rows($query_fornecedores);
								if($rows_fornecedores > 0)
								{
									$c=0;
									$total=0;
									while($row_fornecedores = mysql_fetch_array($query_fornecedores))
									{
										$total = $row_fornecedores['orf_valor'];
										if ($c == 0){$c1 = "linhaimpar";$c=1;}else{$c1 = "linhapar";$c=0;} 
										echo "
										<tr class='$c1'>
											<td>".$row_fornecedores['for_nome_razao']."</td>
											<td>R$ ".number_format($row_fornecedores['orf_valor'],2,',','.')."</td>
											<td>";
											if($row_fornecedores['for_autonomo'] == 1)
											{
												$valor_autonomo = ($row_fornecedores['orf_valor']*20)/100;
												$total += $valor_autonomo;
												echo "+ R$ ".number_format($valor_autonomo,2,',','.');
											}echo "</td>
											<td>".$row_fornecedores['orf_obs']."</td>
											<td align='right'><b>R$ ".number_format($total,2,',','.')."</b></td>
											<td><div style='border:1px solid #666;'>&nbsp;&nbsp;&nbsp;&nbsp;</div></td>											
										</tr>
										";
									}
								}
								echo "
							</table>
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							
						</td>
					</tr>
					<tr>
						<td align='left'colspan='2'>
							<b>Observações:</b> ".nl2br($orc_observacoes)."
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							<br><br>&nbsp;
						</td>
					</tr>
					<tr>
						<td colspan='2'>
							 <table class='bordatabela' cellpadding='10' cellspacing='0' width='700'>
							 	<tr>
									<td colspan='2' class='titulo_tabela' align='center'>Aprovação (assinalar a empresa acima e preencher com data/assinatura)</td>									
								</tr>
								<tr>
									<td colspan='2'>
										&nbsp;
									</td>
								</tr>
								<tr>
									<td width='30%' align='right'>Data</td>
									<td>_______/_______/______________</td>
									
								</tr>
								<tr>
									<td colspan='2'>
										&nbsp;
									</td>
								</tr>
								<tr>
									<td align='right'>Assinatura</td>
									<td>_________________________________________________________</td>
								</tr>
								<tr>
									<td colspan='2'>
										&nbsp;
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
 30,     // margin top
 30,    // margin bottom
 5,     // margin header
 5,     // margin footer
 'P');  // L - landscape, P - portrait);
$mpdf->SetTitle('Exacto Adm | Imprimir Orçamento');
$mpdf->useOddEven = false;
$mpdf->SetHTMLHeader('<div class="topo"><img src=../imagens/logo.png width="200"><br><br><img src=../imagens/linha.png /></div>'); 
$mpdf->SetHTMLFooter('
<div class=rodape>
<img src=../imagens/linha.png />
<table align=center class=rod width="100%">
<tr>
<td colspan=2 align=center>
<br>
<span class=azul>Exacto Assessoria e Administração</span><br>
Rua Prof. Emilio Augusto Ferreira, 32 - Vila Oliveira, Mogi das Cruzes/SP<br>
Fone: (11) <span class=verde>4791-9220</span><br>
Email: <span class=azul>exacto@exactoadm.com.br</span> | Site: <span class=azul>www.exactoadm.com.br</span><br> 
</td>
</tr>
<tr>
<td colspan=2 align=right>
{PAGENO} / {nbpg}
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
$sql_fornecedores = "SELECT * FROM orcamento_fornecedor 
					 LEFT JOIN cadastro_fornecedores ON cadastro_fornecedores.for_id = orcamento_fornecedor.orf_fornecedor
					 WHERE orf_orcamento = $orc_id ORDER BY orf_id ASC";
$query_fornecedores = mysql_query($sql_fornecedores,$conexao);
$rows_fornecedores = mysql_num_rows($query_fornecedores);
if($rows_fornecedores > 0)
{
	$c=0;
	$total=0;
	while($row_fornecedores = mysql_fetch_array($query_fornecedores))
	{
		if($row_fornecedores['orf_anexo'] != '')
		{
			$mpdf->SetHTMLHeader('');
			$pagecount = $mpdf->SetSourceFile($row_fornecedores['orf_anexo']);
			for ($i=1; $i<=$pagecount; $i++) {
				$mpdf->AddPage();
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
				
				$import_page = $mpdf->ImportPage($i);
				$mpdf->UseTemplate($import_page);
				
//				if ($i < $pagecount)$mpdf->AddPage();
			}
		}
	}
}

$mpdf->Output('Orçamento_'.str_pad($orc_id,6,'0',STR_PAD_LEFT).'.pdf','I');
exit();
?>