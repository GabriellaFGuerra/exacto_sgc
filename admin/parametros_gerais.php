<?php
session_start();
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
include '../mod_includes/php/funcoes-jquery.php';
include '../mod_topo/topo.php';

$pagina = 'Parâmetros Gerais';
$erro = false;
$mensagem = '';

function obterValor($array, $chave, $padrao = '')
{
    return $array[$chave] ?? $padrao;
}

function obterCamposParametros()
{
    return [
        'ger_nome',
        'ger_sigla',
        'ger_cep',
        'ger_uf',
        'ger_municipio',
        'ger_bairro',
        'ger_endereco',
        'ger_numero',
        'ger_comp',
        'ger_telefone',
        'ger_email',
        'ger_site',
        'ger_cor_primaria',
        'ger_cor_secundaria',
        'ger_numeracao_anual',
        'ger_guia_anual',
        'ger_status'
    ];
}

function carregarParametros($pdo)
{
    $sql = 'SELECT * FROM parametros_gerais 
            LEFT JOIN end_uf ON end_uf.uf_id = parametros_gerais.ger_uf 
            LEFT JOIN end_municipios ON end_municipios.mun_id = parametros_gerais.ger_municipio 
            WHERE ger_id = 1';
    $stmt = $pdo->query($sql);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function carregarUFs($pdo)
{
    $stmt = $pdo->query('SELECT * FROM end_uf ORDER BY uf_sigla');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function salvarParametros($pdo, $dados, &$erro)
{
    // Verifica se já existe registro
    $stmt = $pdo->query('SELECT ger_id FROM parametros_gerais LIMIT 1');
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($registro) {
        // Atualizar
        $dados['ger_id'] = $registro['ger_id'];
        $sql = 'UPDATE parametros_gerais SET 
            ger_nome = :ger_nome,
            ger_sigla = :ger_sigla,
            ger_cep = :ger_cep,
            ger_uf = :ger_uf,
            ger_municipio = :ger_municipio,
            ger_bairro = :ger_bairro,
            ger_endereco = :ger_endereco,
            ger_numero = :ger_numero,
            ger_comp = :ger_comp,
            ger_telefone = :ger_telefone,
            ger_email = :ger_email,
            ger_site = :ger_site,
            ger_cor_primaria = :ger_cor_primaria,
            ger_cor_secundaria = :ger_cor_secundaria,
            ger_numeracao_anual = :ger_numeracao_anual,
            ger_guia_anual = :ger_guia_anual,
            ger_status = :ger_status
            WHERE ger_id = :ger_id';
    } else {
        // Inserir
        $sql = 'INSERT INTO parametros_gerais (
            ger_nome, ger_sigla, ger_cep, ger_uf, ger_municipio, ger_bairro, ger_endereco,
            ger_numero, ger_comp, ger_telefone, ger_email, ger_site,
            ger_cor_primaria, ger_cor_secundaria, ger_numeracao_anual, ger_guia_anual, ger_status
        ) VALUES (
            :ger_nome, :ger_sigla, :ger_cep, :ger_uf, :ger_municipio, :ger_bairro, :ger_endereco,
            :ger_numero, :ger_comp, :ger_telefone, :ger_email, :ger_site,
            :ger_cor_primaria, :ger_cor_secundaria, :ger_numeracao_anual, :ger_guia_anual, :ger_status
        )';
    }

    $stmt = $pdo->prepare($sql);
    if ($stmt->execute($dados)) {
        return $registro ? $registro['ger_id'] : $pdo->lastInsertId();
    } else {
        $erro = true;
        return null;
    }
}

function salvarLogo($pdo, $ger_id, &$erro)
{
    if (isset($_FILES['ger_logo']) && $_FILES['ger_logo']['error'][0] === UPLOAD_ERR_OK) {
        $diretorioUpload = '../imagens/';
        if (!is_dir($diretorioUpload)) {
            mkdir($diretorioUpload, 0755, true);
        }
        $caminhoTemporario = $_FILES['ger_logo']['tmp_name'][0];
        $caminhoLogo = $diretorioUpload . 'logo.png';
        if (move_uploaded_file($caminhoTemporario, $caminhoLogo)) {
            $stmtLogo = $pdo->prepare('UPDATE parametros_gerais SET ger_logo = :logo WHERE ger_id = :id');
            $stmtLogo->execute(['logo' => $caminhoLogo, 'id' => $ger_id]);
        } else {
            $erro = true;
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'envia') {
    $ger_id = (int)obterValor($_GET, 'ger_id', 1);
    $campos = obterCamposParametros();
    $dados = [];
    foreach ($campos as $campo) {
        $dados[$campo] = obterValor($_POST, $campo, '');
    }

    $idSalvo = salvarParametros($pdo, $dados, $erro);

    if ($idSalvo) {
        salvarLogo($pdo, $idSalvo, $erro);
        $mensagem = $erro
            ? '<img src=../imagens/x.png> Erro ao alterar os dados, por favor tente novamente.<br><br><input value=" Ok " type="button" class="close_janela">'
            : '<img src=../imagens/ok.png> Dados alterados com sucesso.<br><br><input value=" Ok " type="button" class="close_janela">';
    } else {
        $mensagem = '<img src=../imagens/x.png> Erro ao alterar os dados, por favor tente novamente.<br><br><input value=" Ok " type="button" class="close_janela">';
    }
    echo "<script>abreMask(`$mensagem`);</script>";
}

// Carregar dados para o formulário
$parametros = carregarParametros($pdo);

$ger_id = $parametros['ger_id'] ?? 1;
$ger_nome = $parametros['ger_nome'] ?? '';
$ger_sigla = $parametros['ger_sigla'] ?? '';
$ger_cep = $parametros['ger_cep'] ?? '';
$ger_uf = $parametros['ger_uf'] ?? '';
$uf_sigla = $parametros['uf_sigla'] ?? 'UF';
$ger_municipio = $parametros['ger_municipio'] ?? '';
$mun_nome = $parametros['mun_nome'] ?? 'Município';
$ger_bairro = $parametros['ger_bairro'] ?? '';
$ger_endereco = $parametros['ger_endereco'] ?? '';
$ger_numero = $parametros['ger_numero'] ?? '';
$ger_comp = $parametros['ger_comp'] ?? '';
$ger_telefone = $parametros['ger_telefone'] ?? '';
$ger_email = $parametros['ger_email'] ?? '';
$ger_site = $parametros['ger_site'] ?? '';
$ger_logo = $parametros['ger_logo'] ?? '';
$ger_cor_primaria = $parametros['ger_cor_primaria'] ?? '';
$ger_cor_secundaria = $parametros['ger_cor_secundaria'] ?? '';
$ger_numeracao_anual = $parametros['ger_numeracao_anual'] ?? 0;
$ger_guia_anual = $parametros['ger_guia_anual'] ?? 0;
$ger_status = $parametros['ger_status'] ?? 1;

// Carregar UFs
$ufs = carregarUFs($pdo);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= htmlspecialchars($pagina) ?></title>
    <meta name="author" content="MogiComp">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include '../css/style.php'; ?>
    <script src="../mod_includes/js/funcoes.js"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
    <link rel="stylesheet" href="../mod_includes/js/colorpicker/css/colorpicker.css" />
    <link rel="stylesheet" media="screen" href="../mod_includes/js/colorpicker/css/layout.css" />
    <script src="../mod_includes/js/colorpicker/js/colorpicker.js"></script>
    <script src="../mod_includes/js/colorpicker/js/eye.js"></script>
    <script src="../mod_includes/js/colorpicker/js/utils.js"></script>
    <script src="../mod_includes/js/colorpicker/js/layout.js?ver=1.0.2"></script>
</head>

<body>
    <form name="form_parametros_gerais" id="form_parametros_gerais" enctype="multipart/form-data" method="post"
        action="parametros_gerais.php?pagina=parametros_gerais&action=envia&ger_id=<?= $ger_id ?>">
        <div class="centro">
            <div class="titulo"><?= $pagina ?> &raquo; Editar</div>
            <table align="center" cellspacing="0">
                <tr>
                    <td align="left">
                        <div class="quadro">
                            <div class="formtitulo">Dados Gerais</div>
                            <input name="ger_nome" id="ger_nome" value="<?= htmlspecialchars($ger_nome) ?>"
                                placeholder="Nome">
                            <input name="ger_sigla" id="ger_sigla" value="<?= htmlspecialchars($ger_sigla) ?>"
                                placeholder="Sigla">
                        </div>
                        <p>
                        <div class="quadro">
                            <div class="formtitulo">Endereço</div>
                            <input name="ger_cep" id="ger_cep" value="<?= htmlspecialchars($ger_cep) ?>"
                                placeholder="CEP" maxlength="9"
                                onkeypress="mascaraCEP(this); return SomenteNumero(event);" />
                            <select name="ger_uf" id="ger_uf">
                                <option value="<?= htmlspecialchars($ger_uf) ?>"><?= htmlspecialchars($uf_sigla) ?>
                                </option>
                                <?php foreach ($ufs as $uf): ?>
                                <option value="<?= $uf['uf_id'] ?>"><?= htmlspecialchars($uf['uf_sigla']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="ger_municipio" id="ger_municipio">
                                <option value="<?= htmlspecialchars($ger_municipio) ?>">
                                    <?= htmlspecialchars($mun_nome) ?></option>
                            </select>
                            <input name="ger_bairro" id="ger_bairro" value="<?= htmlspecialchars($ger_bairro) ?>"
                                placeholder="Bairro" />
                            <p>
                                <input name="ger_endereco" id="ger_endereco"
                                    value="<?= htmlspecialchars($ger_endereco) ?>" placeholder="Endereço" />
                                <input name="ger_numero" id="ger_numero" value="<?= htmlspecialchars($ger_numero) ?>"
                                    placeholder="Número" />
                                <input name="ger_comp" id="ger_comp" value="<?= htmlspecialchars($ger_comp) ?>"
                                    placeholder="Complemento" />
                        </div>
                        <p>
                        <div class="quadro">
                            <div class="formtitulo">Contato</div>
                            <input name="ger_telefone" id="ger_telefone" value="<?= htmlspecialchars($ger_telefone) ?>"
                                placeholder="Telefone c/ DDD"
                                onkeypress="mascaraTELEFONE(this); return SomenteNumeroCEL(this,event);">
                            <p>
                                <input name="ger_email" id="ger_email" value="<?= htmlspecialchars($ger_email) ?>"
                                    placeholder="Email" />
                                <input name="ger_site" id="ger_site" value="<?= htmlspecialchars($ger_site) ?>"
                                    placeholder="Site" />
                        </div>
                        <p>
                        <div class="quadro">
                            <div class="formtitulo">Personalização</div>
                            <?php if ($ger_logo && file_exists($ger_logo)): ?>
                            <img src="<?= htmlspecialchars($ger_logo) ?>" valign="middle" width="39">
                            <?php endif; ?>
                            Logo: <input type="file" id="ger_logo" name="ger_logo[]" />
                            <p>
                                <input readonly size="2"
                                    style="background-color:#<?= htmlspecialchars($ger_cor_primaria) ?>" class="cor" />
                                Cor primária: &nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="text" maxlength="6" size="6" id="colorpickerField1"
                                    value="<?= htmlspecialchars($ger_cor_primaria) ?>" name="ger_cor_primaria"
                                    placeholder="Selecione a cor" />
                            <p>
                                <input readonly size="2"
                                    style="background-color:#<?= htmlspecialchars($ger_cor_secundaria) ?>"
                                    class="cor" /> Cor secundária:
                                <input type="text" maxlength="6" size="6" id="colorpickerField3"
                                    value="<?= htmlspecialchars($ger_cor_secundaria) ?>" name="ger_cor_secundaria"
                                    placeholder="Selecione a cor" />
                            <p>
                        </div>
                        <p>
                        <div class="quadro">
                            <div class="formtitulo">Particularidades</div>
                            Numeração do processo zera anualmente?
                            <input type="radio" name="ger_numeracao_anual" value="1"
                                <?= $ger_numeracao_anual == 1 ? 'checked' : '' ?>> Sim
                            <input type="radio" name="ger_numeracao_anual" value="0"
                                <?= $ger_numeracao_anual == 0 ? 'checked' : '' ?>> Não
                            <p>
                                Numeração da guia zera anualmente?
                                <input type="radio" name="ger_guia_anual" value="1"
                                    <?= $ger_guia_anual == 1 ? 'checked' : '' ?>> Sim
                                <input type="radio" name="ger_guia_anual" value="0"
                                    <?= $ger_guia_anual == 0 ? 'checked' : '' ?>> Não
                            <p>
                                Status do portal:
                                <input type="radio" name="ger_status" value="1"
                                    <?= $ger_status == 1 ? 'checked' : '' ?>>
                                Ativo
                                <input type="radio" name="ger_status" value="0"
                                    <?= $ger_status == 0 ? 'checked' : '' ?>>
                                Inativo
                        </div>
                        <br><br>
                        <center>
                            <div id="erro" align="center">&nbsp;</div>
                            <input type="submit" id="bt_parametros_gerais" value="Salvar" />&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="button" id="botao_cancelar"
                                onclick="window.location.href='parametros_gerais.php?pagina=parametros_gerais';"
                                value="Cancelar" />
                        </center>
                    </td>
                </tr>
            </table>
            <div class="titulo"> </div>
        </div>
    </form>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>