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
            <div class="titulo">
                Bem-vindo ao Sistema de Gerenciamento de Orçamentos
            </div>
            <div id="interna">
                <table align="center" cellspacing="10">
                    <tr>
                        <td>
                            <span class="texto-pequeno">
                                Digite seu usuário e senha para acessar o sistema.
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <input type="text" name="login" id="login" placeholder="Usuário" size="20" required
                                autocomplete="username">
                        </td>
                    </tr>
                    <tr>
                        <td align="center">
                            <input type="password" name="senha" id="senha" placeholder="Senha" size="20" required
                                autocomplete="current-password">
                        </td>
                    </tr>
                    <tr>
                        <td align="center" height="30" valign="bottom">
                            <input type="submit" id="botao_login" value="Entrar no Sistema" name="botao_login">
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </form>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>