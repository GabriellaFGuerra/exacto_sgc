<?php
require_once '../mod_includes/php/connect.php';
$titulo = $titulo ?? 'Login - Sistema de Gerenciamento de Orçamentos';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="author" content="MogiComp">
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../libs/jquery-1.8.3.min.js"></script>
</head>

<body>
    <?php
	include '../mod_includes/php/funcoes-jquery.php';
	include '../mod_topo/topo_login.php';
	?>
    <form name="form_login" id="form_login" method="post" autocomplete="off" action="envialogin.php">
        <div class="centro">
            <div class="titulo">Bem vindo ao Sistema de Gerenciamento de Orçamentos</div>
            <div id="interna">
                <table align="center" cellspacing="10">
                    <tr>
                        <td>
                            <span class="textopeq">Digite seu usuário e senha para acessar o sistema.</span><br>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <input name="login" id="login" placeholder="Login" size="20" required>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <input type="password" name="senha" id="senha" placeholder="Senha" size="20" required>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" height="30" valign="bottom">
                            <input type="submit" id="bt_login" value="Entrar no Sistema" name="B1">
                        </td>
                    </tr>
                </table>
            </div>
            <div class="titulo"></div>
        </div>
    </form>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>