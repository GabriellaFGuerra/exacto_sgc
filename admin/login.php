<?php
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
include		('../mod_includes/php/funcoes-jquery.php');
include		("../mod_topo/topo_login.php");
?>

<?php
echo "
<form name='form_login' id='form_login' enctype='multipart/form-data' method='post' autocomplete='off' action='envialogin.php'>
    <div class='centro'>
		<div class='titulo'> Bem vindo ao Sistema de Gerenciamento de Orçamentos  </div>
		<div id='interna'>
		<table align='center' cellspacing='10'>
			<tr>
				<td>
					<span class='textopeq'>Digite seu usuário e senha para acessar o sistema.</span><br>
				</td>
			</tr>
			<tr>
				<td align='center'>
					<input name='login' id='login' placeholder='Login' size='20'>
				</td>
			</tr>
			<tr >
				<td align='center'>
					<input type='password' name='senha' id='senha' placeholder='Senha' size='20'>
				</td>
			</tr>
			<tr >
				<td  align='center' height='30' valign='bottom'>
					<input type='submit' id='bt_login' value=' Entrar no Sistema ' name='B1'>
				</td>
			</tr>
		</table>
		</div>
		<div class='titulo'> </div>
	</div>
</form>
";
include('../mod_rodape/rodape.php');
?>
</body>
</html>
