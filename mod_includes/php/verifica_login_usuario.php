<?php
include('connect.php');
$login = $_POST['login'];
$usu_id = $_POST['usu_id'];
if($usu_id != 0)
{
	$sql = "SELECT * FROM admin_usuarios WHERE usu_login = '$login' AND usu_id <> '$usu_id' ";
}
else
{
	$sql = "SELECT * FROM admin_usuarios WHERE usu_login = '$login' ";
}
$query = mysql_query($sql, $conexao);
$rows = mysql_num_rows($query);

if($rows > 0) 
{
	echo "true";
}

	
?>