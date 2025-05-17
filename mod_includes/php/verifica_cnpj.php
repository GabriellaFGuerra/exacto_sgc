<?php
include('connect.php');
$cnpj = $_POST['cnpj'];
$cli_id = $_POST['cli_id'];
if($cli_id != 0)
{
	$sql = "SELECT * FROM cadastro_clientes WHERE cli_cnpj = '$cnpj' AND cli_id <> '$cli_id' ";
}
else
{
	$sql = "SELECT * FROM cadastro_clientes WHERE cli_cnpj = '$cnpj' ";
}
$query = mysql_query($sql, $conexao);
$rows = mysql_num_rows($query);

if($rows > 0) 
{
	echo "true";
} 
?>