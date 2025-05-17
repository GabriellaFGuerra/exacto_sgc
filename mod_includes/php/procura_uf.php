<?php
include('connect.php');
$uf = $_POST['uf'];
$sqlprocura = "SELECT * FROM end_municipios WHERE mun_uf = '$uf'";
$queryprocura = mysql_query($sqlprocura, $conexao);
$rowsprocura = mysql_num_rows($queryprocura);
if($rowsprocura>0)
{
	while($row = mysql_fetch_array($queryprocura) )
	{
		echo "<option value='".$row['mun_id']."'>".$row['mun_nome']."</option>";
	}
}
else
{
	echo "<option value=''>Selecione UF</option>";
}
?>