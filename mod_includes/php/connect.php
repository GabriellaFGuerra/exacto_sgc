<?php
include("ctracker.php");
error_reporting(1);
date_default_timezone_set('America/Sao_Paulo');

$ip = "localhost";
$user = "sistemae_admin"; 
$senha = "infomogi123";
$db = "sistemae_sistema";

$conexao =  mysql_connect("$ip","$user","$senha");
if($conexao)
{       
	if( !  mysql_select_db("$db",$conexao)  )
	{       
		die( mysql_error($conexao)); 	
    }
}
else
{
	die('N�o foi poss�vel conectar ao banco de dados.');     
}

include("parametros.php");


mysql_query("SET NAMES 'utf8'");
mysql_query('SET character_set_connection=utf8');
mysql_query('SET character_set_client=utf8');
mysql_query('SET character_set_results=utf8');
?>
