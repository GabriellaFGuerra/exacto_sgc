<?php
session_start(); 
include('connect.php');

$busca = $_POST['busca'];
if($busca != "")
{
	$sqlprocura = "SELECT * FROM orcamento_gerenciar
				   LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
				   WHERE (orc_cliente = '$busca')
				   ORDER BY orc_id DESC";
}
$queryprocura = mysql_query($sqlprocura, $conexao);
$rowsprocura = mysql_num_rows($queryprocura);
if($rowsprocura>0)
{
	echo "<option value=''>Selecione o orçamento caso tenha relação</option>";
	while($rowsprocura = mysql_fetch_array($queryprocura) )
	{
		echo "<option value='".$rowsprocura['orc_id']."'>".$rowsprocura['orc_id']." (".$rowsprocura['tps_nome'].")</option>";
	}
	
}
else
{
	echo "<option value=''>Nenhum orçamento cadastrado para este cliente.</option>";
}
?>