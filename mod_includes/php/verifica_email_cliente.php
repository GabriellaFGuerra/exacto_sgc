<?php
include('connect.php');
$email = $_POST['email'];
$cli_id = $_POST['cli_id'];
if($cli_id != 0)
{
	$sql = "SELECT * FROM cadastro_clientes WHERE cli_email = '$email' AND cli_id <> '$cli_id' ";
}
else
{
	$sql = "SELECT * FROM cadastro_clientes WHERE cli_email = '$email' ";
}
$query = mysql_query($sql, $conexao);
$rows = mysql_num_rows($query);

if($rows > 0) 
{
	echo "true";
}

	
?>