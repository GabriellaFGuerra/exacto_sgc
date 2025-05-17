<?php
include('connect.php');
$int_id = $_POST['int_id'];
$departamento = $_POST['departamento'];
if($departamento != "")
{
	if($int_id != '')
	{
		$sqlprocura = "SELECT * FROM `usuarios_internos` 
					   WHERE int_departamento = '$departamento' AND int_id = $int_id
					   ORDER BY int_nome ASC";
	}
	else
	{
		$sqlprocura = "SELECT * FROM `usuarios_internos` 
					   WHERE int_departamento = '$departamento' 
					   ORDER BY int_nome ASC";
	}
}
$queryprocura = mysql_query($sqlprocura, $conexao);
$rowsprocura = mysql_num_rows($queryprocura);
if($rowsprocura>0)
{
	echo "<option value=''>Selecione o Responsável</option>";
	while($rowsprocura = mysql_fetch_array($queryprocura) )
	{
		echo "<option value='".$rowsprocura['int_id']."'>".$rowsprocura['int_nome']."</option>";
	}
}
else
{
	echo "<option value=''>Nome do Responsável</option>";
}
?>