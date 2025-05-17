<?php
include('connect.php');
$cnpj = $_POST['cnpj'];
$for_id = $_POST['for_id'];
if($for_id != 0)
{
	$sql = "SELECT * FROM cadastro_fornecedores WHERE for_cnpj = '$cnpj' AND for_id <> '$for_id' ";
}
else
{
	$sql = "SELECT * FROM cadastro_fornecedores WHERE for_cnpj = '$cnpj' ";
}
$query = mysql_query($sql, $conexao);
$rows = mysql_num_rows($query);

if($rows > 0) 
{
	echo "true";
} 
?>