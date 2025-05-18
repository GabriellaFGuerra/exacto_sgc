<?php
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Exacto Adm</title>
<meta name="author" content="MogiComp">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="../imagens/favicon.ico">
<link href="../css/style.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="../mod_includes/js/jquery-1.8.3.min.js"></script>
<!-- TOOLBAR -->
<link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
<link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
<script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
<!-- TOOLBAR -->
<script type="text/javascript" src="../mod_includes/js/funcoes.js"></script>
</head>
<body>
<?php	
require_once("../mod_includes/php/ctracker.php");
include		('../mod_includes/php/connect.php');
include		('../mod_includes/php/funcoes-jquery.php');
include		('../mod_includes/php/funcoes.php');
?>

<?php


$hoje = date("Y-m-d", strtotime("+60 days"));


### VENCE A FATURA - INI ###
$sql_vence_fatura = "SELECT * FROM documento_gerenciar 
						LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
						LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
						LEFT JOIN (orcamento_gerenciar 
							LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico)
						ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
						WHERE doc_data_vencimento <= '".$hoje."'  AND cli_status = 1
						
						ORDER BY cli_nome_razao ASC, doc_data_vencimento DESC ";
$query_vence_fatura = mysql_query($sql_vence_fatura,$conexao);
$rows_vence_fatura = mysql_num_rows($query_vence_fatura);
if($rows_vence_fatura > 0){
	while($row_vence_fatura = mysql_fetch_array($query_vence_fatura))
	{
		$tipo_doc 		.= $row_vence_fatura['tpd_nome']."<br>";
		$clientes 		.= $row_vence_fatura['cli_nome_razao']."<br>";
		$data_emissao	.= implode("/",array_reverse(explode("-",$row_vence_fatura['doc_data_emissao'])))."<br>";
		$per 			= $row_vence_fatura['doc_periodicidade'];
		switch($per)
		{
			case 6: $period = "Semestral";break;
			case 12: $period = "Anual";break;
			case 24: $period = "Bienal";break;
			case 36: $period = "Trienal";break;
			case 48: $period = "Quadrienal";break;
			case 60: $period = "Quinquenal";break;
			
		}
		$periodicidade		.= $period."<br>";
		$data_vencimento	.= implode("/",array_reverse(explode("-",$row_vence_fatura['doc_data_vencimento'])))."<br>";
	}
	include('../mail/envia_email_docs_a_receber.php');
}
### VENCE A FATURA - FIM ###

include('../mod_rodape/rodape.php');
?>
</body>
</html>