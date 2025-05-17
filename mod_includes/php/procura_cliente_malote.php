<?php
session_start (); 
include('connect.php');

$busca = $_POST['busca'];
if($busca != "")
{
	$sqlprocura = "SELECT * FROM cadastro_clientes
				   INNER JOIN cadastro_usuarios_clientes ON cadastro_usuarios_clientes.ucl_cliente = cadastro_clientes.cli_id
				   WHERE ucl_usuario = '".$_SESSION['usuario_id']."' AND (cli_nome_razao LIKE '%$busca%' OR REPLACE(REPLACE(cli_cnpj, '.', ''), '-', '') LIKE '%$busca%')
				   ORDER BY cli_nome_razao ASC";
}
$queryprocura = mysql_query($sqlprocura, $conexao);
$rowsprocura = mysql_num_rows($queryprocura);
if($rowsprocura>0)
{
	while($rowsprocura = mysql_fetch_array($queryprocura) )
	{
		echo "<input id='campo' value='&raquo; ".$rowsprocura['cli_nome_razao']." (".$rowsprocura['cli_cnpj'].")' name='campo' onclick='carregaClienteMal(this.value,\"".$rowsprocura['cli_id']."\");'><br>";
	}
	
}
else
{
	echo "<script> jQuery('#suggestions').hide();</script>"; 
	echo "";
}
?>