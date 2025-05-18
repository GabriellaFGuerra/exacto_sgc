<?php
session_start (); 
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');
if($_SESSION['setor'] == 3)
{
	echo "Processo não encontrado";
	exit;
}

$ip = "192.168.1.10:3030";
$user = "root"; 
$senha = "m0507c1106";
$db = "pelisserv";

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
$cha_id = $_GET['cha_id'];
$sqledit = "SELECT * FROM cadastro_chamados
		LEFT JOIN cadastro_equipamentos ON cadastro_equipamentos.equ_id = cadastro_chamados.cha_equipamento
		LEFT JOIN cadastro_tecnicos ON cadastro_tecnicos.tec_id = cadastro_chamados.cha_tecnico
		LEFT JOIN (cadastro_unidades 
			LEFT JOIN cadastro_clientes
			ON cadastro_clientes.cli_id = cadastro_unidades.uni_cliente 
			LEFT JOIN end_municipios
			ON end_municipios.mun_id = cadastro_unidades.uni_municipio 
			LEFT JOIN end_uf
			ON end_uf.uf_id = cadastro_unidades.uni_uf 
			)
		ON cadastro_unidades.uni_id = cadastro_chamados.cha_unidade
		LEFT JOIN cadastro_status_chamado h1 ON h1.stc_chamado = cadastro_chamados.cha_id 
		WHERE h1.stc_id = (SELECT MAX(h2.stc_id) FROM cadastro_status_chamado h2 where h2.stc_chamado = h1.stc_chamado) AND
		      cha_id = $cha_id
		GROUP BY cha_id";
$queryedit = mysql_query($sqledit,$conexao);
$rowsedit = mysql_num_rows($queryedit);
if($rowsedit > 0)
{
	$cha_id = mysql_result($queryedit, 0, 'cha_id');
		$cha_ano = mysql_result($queryedit, 0, 'cha_ano');
		$cli_id = mysql_result($queryedit, 0, 'cli_id');
		$cli_nome_razao = mysql_result($queryedit, 0, 'cli_nome_razao');
		$uni_id = mysql_result($queryedit, 0, 'uni_id');
		$uni_nome_razao = mysql_result($queryedit, 0, 'uni_nome_razao');
		$cha_equipamento = mysql_result($queryedit, 0, 'cha_equipamento');
		$cha_avul_tipo = mysql_result($queryedit, 0, 'cha_avul_tipo');
		$cha_avul_marca = mysql_result($queryedit, 0, 'cha_avul_marca');
		$cha_avul_modelo = mysql_result($queryedit, 0, 'cha_avul_modelo');
		$cha_avul_num_serie = mysql_result($queryedit, 0, 'cha_avul_num_serie');
		$uni_cep = mysql_result($queryedit, 0, 'uni_cep');
		$mun_nome = mysql_result($queryedit, 0, 'mun_nome');
		$uf_sigla = mysql_result($queryedit, 0, 'uf_sigla');
		$uni_bairro = mysql_result($queryedit, 0, 'uni_bairro');
		$uni_endereco = mysql_result($queryedit, 0, 'uni_endereco');
		$uni_numero = mysql_result($queryedit, 0, 'uni_numero');
		$uni_comp = mysql_result($queryedit, 0, 'uni_comp');
		$uni_telefone = mysql_result($queryedit, 0, 'uni_telefone');
		$uni_celular = mysql_result($queryedit, 0, 'uni_celular');
		$uni_responsavel = mysql_result($queryedit, 0, 'uni_responsavel');
		$uni_email = mysql_result($queryedit, 0, 'uni_email');
		$equ_tipo = mysql_result($queryedit, 0, 'equ_tipo');
		$equ_marca = mysql_result($queryedit, 0, 'equ_marca');
		$equ_modelo = mysql_result($queryedit, 0, 'equ_modelo');
		$equ_num_serie = mysql_result($queryedit, 0, 'equ_num_serie');
		$equ_num_pat = mysql_result($queryedit, 0, 'equ_num_pat');
		$equ_nosso_num = mysql_result($queryedit, 0, 'equ_nosso_num');
		$cha_verif_disjuntor = mysql_result($queryedit, 0, 'cha_verif_disjuntor');
		$cha_verif_agua = mysql_result($queryedit, 0, 'cha_verif_agua');
		$cha_verif_ar = mysql_result($queryedit, 0, 'cha_verif_ar');
		$cha_responsavel = mysql_result($queryedit, 0, 'cha_responsavel');
		$cha_telefone = mysql_result($queryedit, 0, 'cha_telefone');
		$cha_descricao = mysql_result($queryedit, 0, 'cha_descricao');
		$stc_status = mysql_result($queryedit, 0, 'stc_status');
		$cha_tecnico = mysql_result($queryedit, 0, 'cha_tecnico');
		$tec_nome = mysql_result($queryedit, 0, 'tec_nome');
		$cha_data_cadastro = implode("/",array_reverse(explode("-",substr(mysql_result($queryedit, 0, 'cha_data'),0,10))));
		$cha_hora_cadastro = substr(mysql_result($queryedit, 0, 'cha_data'),11,5);
		if($cha_equipamento == '' && ($cha_avul_tipo != '' || $cha_avul_marca != '' || $cha_avul_modelo != '' || $cha_avul_num_serie != ''))
		{
			$avulso = "Sim";
			$equ_tipo = $cha_avul_tipo;
			$equ_marca = $cha_avul_marca;
			$equ_modelo = $cha_avul_modelo;
			$equ_num_serie = $cha_avul_num_serie;
			
		}
		else
		{
			$avulso = "Não";
		}		
		switch($stc_status)
		{
			case 1: $stc_status_n = "<span class='preto'>Em análise</span>";break;
			case 2: $stc_status_n = "<span class='azul'>Aberto</span>";break;
			case 3: $stc_status_n = "<span class='laranja'>Pendente</span>";break;
			case 4: $stc_status_n = "<span class='verde'>Finalizado</span>";break;
			case 5: $stc_status_n = "<span class='vermelho'>Cancelado</span>";break;
		}
}
//header("Content-Type: text/html; charset=utf-8", true); 
ob_start();  //inicia o buffer
?>
<!--<img src='../imagens/topopdf.png'>-->
<style>
.topo 			{ margin:0 auto; text-align:center; padding: 0 0 15px 0;}
.rodape 		{ margin:0 auto; text-align:left; padding: 15px 0 0 0; font-family:"Calibri";}
.rod			{ color: #999; font-size:13px; font-family:sharpsemibold; }
.titulo_adm		{ width:960px; margin:0 auto; font-size:18px; color:#999; text-align:left; border-bottom:1px dashed #DDD; padding:0 0 10px 10px; margin:20px 0 10px 0;}
.laudo			{ font-family:sharpmedium; font-size:13px;  -webkit-print-color-adjust: exact; -moz-border-radius:10px; -webkit-border-radius:10px; border-radius:10px; padding:20px 10px;}
.titulo_laudo	{ font-size:20px; font-family:sharpbold; text-transform:uppercase; font-family:sharpbold; color:#DC202B; font-weight:bold; text-align:center; }
.formtitulo		{ font-family:sharpmedium; border-bottom:1px dotted #0066B3; text-align:left; font-size:20px; color:#0066B3; padding:20px 0px 0px 0px;}
.label			{ font-family:sharpsb;}
.vermelho		{ color:#DC202B;}
.azul			{ color:#0066B3;}
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
						<td colspan='2' align='right'>
							<span class='label'>Nº OS:</span> ".$cha_ano.$cha_id."
							";
							if($avulso == "Sim")
							{
								echo " (chamado avulso)";
							}
							echo "
							
							<br>
							<span class='label'>Data:</span> ".date("d/m/Y")."
						</td>
						
					</tr>
					<tr>
						<td colspan='2' align='left' class='formtitulo'>
							Dados da Unidade
						</td>
					</tr>
					<tr>
						<td colspan='2' height='10'>
						
						</td>
					</tr>
					<tr>
						<td width='20%' class='label'>
							Unidade solicitante:
						</td>
						<td>
							 $uni_nome_razao
						</td>
					</tr>
					<tr>
						<td class='label'>
							Endereco:
						</td>
						<td>
							 $uni_endereco, $uni_numero $uni_comp - $uni_bairro - $mun_nome/$uf_sigla - CEP: $uni_cep
						</td>
					</tr>
					<tr>
						<td class='label'>
							Telefone:
						</td>
						<td>
							 $uni_telefone / $uni_celular
						</td>
					</tr>
					<tr>
						<td class='label'>
							Contato:
						</td>
						<td>
							 $uni_responsavel
						</td>
					</tr>
					<tr>
						<td class='label'>
							Email:
						</td>
						<td>
							 $uni_email
						</td>
					</tr>
					<tr>
						<td class='label'>
							Cidade:
						</td>
						<td>
							 $mun_nome
						</td>
					</tr>
					<tr>
						<td colspan='3' align='left' class='formtitulo'>
							Dados do Equipamento
						</td>
					</tr>
					<tr>
						<td colspan='2' height='10'>
						
						</td>
					</tr>
					<tr>
						<td class='label'>
							Equipamento:
						</td>
						<td>
							 $equ_tipo
						</td>
					</tr>
					<tr>
						<td class='label'>
							Marca:
						</td>
						<td>
							$equ_marca &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
							<span class='label'>Modelo:</span> &nbsp; 
							$equ_modelo
						</td>
					</tr>
					<tr>
						<td class='label'>
							Número Série:
						</td>
						<td>
							 $equ_num_serie &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
							 <span class='label'>Patrimonio:</span> &nbsp; 
							 $equ_num_pat &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
							 <span class='label'>Nosso Número:</span> &nbsp; 
							  $equ_nosso_num
						</td>
					</tr>
					<tr>
						<td class='label'>
							Problema identificado:
						</td>
						<td>
							 $cha_descricao
						</td>
					</tr>
					<tr>
						<td class='label'>
							Responsável pelas informações:
						</td>
						<td>
							 $cha_responsavel
						</td>
					</tr>
					<tr>
						<td colspan='2' align='left' class='formtitulo'>
							Causa
						</td>
					</tr>
					<tr>
						<td colspan='2' height='10'>
						
						</td>
					</tr>
					<tr>
						<td class='label' colspan='2'>
							<input type='checkbox'> Material 	____________________________________________________________________________________________
							<p><br>
							<input type='checkbox'> Mão de Obra &nbsp;_______________________________________________________________________________________
							<p><br>
							<input type='checkbox'> Método &nbsp;____________________________________________________________________________________________
							<p><br>
							<input type='checkbox'> Meio Ambiente ______________________________________________________________________________________
							<p><br>
							<input type='checkbox'> Máquina ____________________________________________________________________________________________
							<p><br>
							<input type='checkbox'> Medida _____________________________________________________________________________________________
						</td>
					</tr>
					<tr>
						<td colspan='2' align='left' class='formtitulo'>
							Solução Aplicada
						</td>
					</tr>
					<tr>
						<td colspan='2' height='10'>
						
						</td>
					</tr>
					<tr>
						<td class='label' colspan='2'>
							<input type='checkbox'> Satisfatória 	_________________________________________________________________________________________
							<p><br>
							<input type='checkbox'> Provisória &nbsp;__________________________________________________________________________________________
							<p><br>
							<input type='checkbox'> Não satisfatória _____________________________________________________________________________________
						</td>
					</tr>
				</table>
				<table class='laudo' align='center' cellspacing='0' cellpadding='3' width='1000'>
					<tr>
						<td class='label' valign='top'>
							Técnico Responsável:
						</td>
						<td align='center'>
							______________________________________________________________________<br> $tec_nome
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
 20,    // margin bottom
 5,     // margin header
 5,     // margin footer
 'P');  // L - landscape, P - portrait);
$mpdf->SetHTMLHeader('<div class=topo><img src=../imagens/logo.png width="200"><br><br><img src=../imagens/linha.png /></div>'); 
$mpdf->SetHTMLFooter('
<div class=rodape>
<img src=../imagens/linha.png />
<table align=center class=rod width="100%">
<tr>
<td colspan=2 align=center>
<br>
<span class=azul>Peli</span><span class=vermelho>Serv</span> Equipamentos E Serviços Odonto-Médicos Ltda<br>
Rua Capitão Antônio Bueno Rangel, 266 - Jardim Jaraguá - São Paulo/SP - CEP: 05158-440<br>
Fone: (11) <span class=vermelho>3901-1000</span><br>
Email: <span class=vermelho>pelisserv@pelisserv.com.br</span> | Site: <span class=vermelho>www.pelisserv.com.br</span><br> 
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
$mpdf->Output();
exit();
?>