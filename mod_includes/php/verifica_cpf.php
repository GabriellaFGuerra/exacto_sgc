<?php
include('connect.php');
$cpf = $_POST['cpf'];
$req_id = $_POST['req_id'];
if($req_id != 0)
{
	$sql = "SELECT * FROM `requerente` WHERE req_cpf_cnpj = '$cpf' AND req_id <> '$req_id' ";
}
else
{
	$sql = "SELECT * FROM `requerente` WHERE req_cpf_cnpj = '$cpf' ";
}
$query = mysql_query($sql, $conexao);
$rows = mysql_num_rows($query);

if($rows > 0) 
{
	echo "true";
} 
?>