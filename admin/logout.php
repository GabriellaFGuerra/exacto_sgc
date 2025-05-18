<?php
session_start (); 
include('../mod_includes/php/connect.php');
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $titulo;?></title>
<meta name="author" content="MogiComp">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" href="../imagens/favicon.png">
<?php include("../css/style.php"); ?>
<script type="text/javascript" src="../libs/jquery-1.8.3.min.js"></script>
</head>
<body>
<?php	
if($pagina == 'logout')
{
	unset($_SESSION['exactoadm']);
	unset($_SESSION['setor']);
	unset($_SESSION['setor_nome']);
	unset($_SESSION['usuario_id']);
	session_write_close();
	echo "<SCRIPT LANGUAGE='JavaScript'>
			window.location.href = 'login.php';
		  </SCRIPT>";
}

?>
</body>
</html>