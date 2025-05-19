<?php
session_start();
$pagina_link = 'relatorio_orcamentos';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';
require_once '../mod_includes/php/verificapermissao.php';

// Definições de paginação
$registros_por_pagina = 10;
$pagina_atual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$offset = ($pagina_atual - 1) * $registros_por_pagina;

// Filtros
$filtro_numero_orcamento = $_REQUEST['fil_orc'] ?? '';
$filtro_nome_cliente = $_REQUEST['fil_nome'] ?? '';
$filtro_tipo_servico = $_REQUEST['fil_tipo_servico'] ?? '';
$filtro_data_inicio = $_REQUEST['fil_data_inicio'] ?? '';
$filtro_data_fim = $_REQUEST['fil_data_fim'] ?? '';
$filtro_status = $_REQUEST['fil_status'] ?? '';
$filtro_ativo = $_REQUEST['filtro'] ?? '';

// Montagem dos filtros SQL
$where = [];
$params = [];

// Filtro por número do orçamento
if ($filtro_numero_orcamento !== '') {
    $where[] = "orc_id LIKE :numero_orcamento";
    $params[':numero_orcamento'] = "%$filtro_numero_orcamento%";
}

// Filtro por nome do cliente
if ($filtro_nome_cliente !== '') {
    $where[] = "cli_nome_razao LIKE :nome_cliente";
    $params[':nome_cliente'] = "%$filtro_nome_cliente%";
}

// Filtro por tipo de serviço
if ($filtro_tipo_servico !== '') {
    $where[] = "orc_tipo_servico = :tipo_servico";
    $params[':tipo_servico'] = $filtro_tipo_servico;
}

// Filtro por data de cadastro
if ($filtro_data_inicio !== '' && $filtro_data_fim !== '') {
    $data_inicio = implode('-', array_reverse(explode('/', $filtro_data_inicio)));
    $data_fim = implode('-', array_reverse(explode('/', $filtro_data_fim))) . " 23:59:59";
    $where[] = "orc_data_cadastro BETWEEN :data_inicio AND :data_fim";
    $params[':data_inicio'] = $data_inicio;
    $params[':data_fim'] = $data_fim;
} elseif ($filtro_data_inicio !== '') {
    $data_inicio = implode('-', array_reverse(explode('/', $filtro_data_inicio)));
    $where[] = "orc_data_cadastro >= :data_inicio";
    $params[':data_inicio'] = $data_inicio;
} elseif ($filtro_data_fim !== '') {
    $data_fim = implode('-', array_reverse(explode('/', $filtro_data_fim))) . " 23:59:59";
    $where[] = "orc_data_cadastro <= :data_fim";
    $params[':data_fim'] = $data_fim;
}

// Filtro por status
if ($filtro_status !== '') {
    $where[] = "sto_status = :status";
    $params[':status'] = $filtro_status;
}

// Filtro ativo
if ($filtro_ativo === '') {
    $where[] = "1 = 0"; // Não mostra nada até filtrar
}

// Monta a cláusula WHERE final
$where_sql = count($where) ? 'AND ' . implode(' AND ', $where) : '';

// Consulta principal com paginação
$sql = "
    SELECT SQL_CALC_FOUND_ROWS *
    FROM orcamento_gerenciar
    LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = orcamento_gerenciar.orc_cliente
    LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
    LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = orcamento_gerenciar.orc_id
    WHERE h1.sto_id = (
        SELECT MAX(h2.sto_id)
        FROM cadastro_status_orcamento h2
        WHERE h2.sto_orcamento = h1.sto_orcamento
    )
    $where_sql
    ORDER BY orc_data_cadastro DESC
    LIMIT :offset, :limite
";

$stmt = $pdo->prepare($sql);
foreach ($params as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limite', $registros_por_pagina, PDO::PARAM_INT);
$stmt->execute();
$orcamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Total de registros para paginação
$total_registros = $pdo->query("SELECT FOUND_ROWS()")->fetchColumn();
$total_paginas = ceil($total_registros / $registros_por_pagina);

// Função para exibir status formatado
function exibirStatus($status)
{
    switch ($status) {
        case 1:
            return "<span class='laranja'>Pendente</span>";
        case 2:
            return "<span class='azul'>Calculado</span>";
        case 3:
            return "<span class='verde'>Aprovado</span>";
        case 4:
            return "<span class='vermelho'>Reprovado</span>";
        default:
            return "Status";
    }
}

// Função para exibir nome do tipo de serviço
function obterNomeTipoServico($pdo, $id_tipo_servico)
{
    if ($id_tipo_servico === '')
        return "Tipo de Serviço Prestado";
    $stmt = $pdo->prepare("SELECT tps_nome FROM cadastro_tipos_servicos WHERE tps_id = :id");
    $stmt->execute([':id' => $id_tipo_servico]);
    return $stmt->fetchColumn() ?: "Tipo de Serviço Prestado";
}

$titulo_tipo_servico = obterNomeTipoServico($pdo, $filtro_tipo_servico);
$titulo_status = exibirStatus($filtro_status);
$logo = '../imagens/logo.png';
$tituloPagina = "Relatórios &raquo; <a href='relatorio_orcamentos.php?pagina=relatorio_orcamentos'>Orçamentos</a>";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title><?= htmlspecialchars($tituloPagina) ?></title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php include "../css/style.php"; ?>
    <script src="../mod_includes/js/funcoes.js"></script>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
    <link href="../mod_includes/js/toolbar/jquery.toolbars.css" rel="stylesheet" />
    <link href="../mod_includes/js/toolbar/bootstrap.icons.css" rel="stylesheet">
    <script src="../mod_includes/js/toolbar/jquery.toolbar.js"></script>
    <?php include '../mod_topo/topo.php'; ?>
</head>
<body>
    <?php include '../mod_includes/php/funcoes-jquery.php'; ?>
    <div class="centro">
        <div class="titulo"><?= $tituloPagina ?></div>
        <div class="filtro">
            <form name="form_filtro" id="form_filtro" method="post"
                action="relatorio_orcamentos.php?pagina=relatorio_orcamentos&filtro=1">
                <input name="fil_orc" id="fil_orc" value="<?= htmlspecialchars($filtro_numero_orcamento) ?>"
                    placeholder="N° Orçamento">
                <input name="fil_nome" id="fil_nome" value="<?= htmlspecialchars($filtro_nome_cliente) ?>"
                    placeholder="Cliente">
                <select name="fil_tipo_servico" id="fil_tipo_servico">
                    <option value="<?= htmlspecialchars($filtro_tipo_servico) ?>"><?= $titulo_tipo_servico ?></option>
                    <?php
                    $sql_tipo_servico = "SELECT * FROM cadastro_tipos_servicos ORDER BY tps_nome";
                    foreach ($pdo->query($sql_tipo_servico) as $row_tipo_servico) {
                        echo "<option value='{$row_tipo_servico['tps_id']}'>{$row_tipo_servico['tps_nome']}</option>";
                    }
                    ?>
                    <option value="">Todos</option>
                </select>
                <select name="fil_status" id="fil_status">
                    <option value="<?= htmlspecialchars($filtro_status) ?>"><?= $titulo_status ?></option>
                    <option value="1">Pendente</option>
                    <option value="2">Calculado</option>
                    <option value="3">Aprovado</option>
                    <option value="4">Reprovado</option>
                    <option value="">Todos</option>
                </select>
                <input type="text" name="fil_data_inicio" id="fil_data_inicio" placeholder="Data Início"
                    value="<?= htmlspecialchars($filtro_data_inicio) ?>" onkeypress="return mascaraData(this,event);">
                <input type="text" name="fil_data_fim" id="fil_data_fim" placeholder="Data Fim"
                    value="<?= htmlspecialchars($filtro_data_fim) ?>" onkeypress="return mascaraData(this,event);">
                <input type="submit" value="Filtrar">
                <input type="button" onclick="PrintDiv('imprimir');" value="Imprimir" />
            </form>
        </div>
        <div class="contentPrint" id="imprimir">
            <?php if ($total_registros > 0): ?>
                <img src="<?= $logo ?>" border="0" class="logo" />
                <table align="center" width="100%" border="0" cellspacing="0" cellpadding="10" class="bordatabela">
                    <tr>
                        <td class="titulo_tabela">N° Orçamento</td>
                        <td class="titulo_tabela">Cliente</td>
                        <td class="titulo_tabela">Serviço</td>
                        <td class="titulo_tabela" align="center">Status</td>
                        <td class="titulo_tabela" align="center">Data Cadastro</td>
                    </tr>
                    <?php foreach ($orcamentos as $indice => $orcamento): ?>
                        <?php
                        $classe_linha = $indice % 2 == 0 ? "linhaimpar" : "linhapar";
                        $nome_servico = $orcamento['tps_nome'] ?: $orcamento['orc_tipo_servico_cliente'] . "<br><span class='detalhe'>Digitado pelo cliente</span>";
                        $data_cadastro = date('d/m/Y', strtotime($orcamento['orc_data_cadastro']));
                        $hora_cadastro = date('H:i', strtotime($orcamento['orc_data_cadastro']));
                        ?>
                        <tr class="<?= $classe_linha ?>">
                            <td><?= $orcamento['orc_id'] ?></td>
                            <td><?= htmlspecialchars($orcamento['cli_nome_razao']) ?></td>
                            <td><?= $nome_servico ?></td>
                            <td align="center"><?= exibirStatus($orcamento['sto_status']) ?></td>
                            <td align="center"><?= $data_cadastro ?><br><span class="detalhe"><?= $hora_cadastro ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <!-- Paginação -->
                <div class="paginacao">
                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                        <?php if ($i == $pagina_atual): ?>
                            <strong><?= $i ?></strong>
                        <?php else: ?>
                            <a href="?pagina=<?= $i ?>&filtro=1"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
            <?php else: ?>
                <br><br><br>Selecione acima os filtros que deseja para gerar o relatório.
            <?php endif; ?>
            <div class="titulo"></div>
        </div>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
    <script src="../mod_includes/js/jquery-1.3.2.min.js"></script>
    <script src="../mod_includes/js/elementPrint.js"></script>
</body>
</html>