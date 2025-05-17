<?php
include('connect.php');

$busca = str_replace(".","",str_replace("-","",$_POST['busca']));
$tipo_requerente = $_POST['tipo_requerente'];
if($busca != "")
{
	if($tipo_requerente == "Externo")
	{
		$sqlprocura = "SELECT * FROM `requerente` 
					   WHERE (req_nome LIKE '%$busca%' OR REPLACE(REPLACE(req_cpf_cnpj, '.', ''), '-', '') LIKE '%$busca%')
					   ORDER BY req_nome ASC";
	}
	elseif($tipo_requerente == "Interno")
	{
		$sqlprocura = "SELECT * FROM `usuarios_internos` 
					   LEFT JOIN `departamentos` ON `departamentos`.dep_id = `usuarios_internos`.int_departamento
					   WHERE (int_nome LIKE '%$busca%')
					   ORDER BY int_nome ASC";
	}
}
$queryprocura = mysql_query($sqlprocura, $conexao);
$rowsprocura = mysql_num_rows($queryprocura);
if($rowsprocura>0)
{
	while($rowsprocura = mysql_fetch_array($queryprocura) )
	{
			echo "<input id='campo' value='&raquo; ".$rowsprocura['req_nome']."".$rowsprocura['int_nome']." (".$rowsprocura['req_cpf_cnpj']."".$rowsprocura['dep_nome'].")' name='campo' onclick='carregaBuscaRequerente(this.value,\"".$rowsprocura['req_id']."".$rowsprocura['int_id']."\");'><br>";
			
			
	}
	
}
else
{
	echo "<script> jQuery('#suggestions').hide();</script>"; 
	echo "";
}
?>