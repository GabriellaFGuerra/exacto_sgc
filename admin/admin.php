<?php
session_start();
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/verificalogin.php';

// Função para renderizar uma tabela HTML
function renderizarTabela(array $cabecalhos, array $linhas, callable $renderLinha): void
{
    if (empty($linhas)) {
        echo "<br><br><br>Não há registros.";
        return;
    }
    echo "<table align='center' width='100%' border='0' cellspacing='0' cellpadding='5' class='bordatabela'>";
    echo "<tr>";
    foreach ($cabecalhos as $cabecalho) {
        $extra = $cabecalho['extra'] ?? '';
        $rotulo = htmlspecialchars($cabecalho['label']);
        echo "<td class='titulo_tabela'$extra>$rotulo</td>";
    }
    echo "</tr>";
    $c = 0;
    foreach ($linhas as $linha) {
        $classe = $c % 2 == 0 ? 'linhaimpar' : 'linhapar';
        echo $renderLinha($linha, $classe);
        $c++;
    }
    echo "</table>";
}

// Função para buscar linhas do banco de dados
function buscarLinhas(PDO $pdo, string $sql, array $params = []): array
{
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue(is_int($key) ? $key + 1 : ":$key", $value, $type);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para formatar datas
function formatarData(?string $data, bool $comHora = false): string
{
    if (!$data)
        return '';
    $dataFormatada = implode('/', array_reverse(explode('-', substr($data, 0, 10))));
    if ($comHora) {
        $hora = substr($data, 11, 5);
        return "$dataFormatada<br><span class='detalhe'>$hora</span>";
    }
    return $dataFormatada;
}

// Função para exibir o status do orçamento
function statusOrcamentoLabel($status): string
{
    return match ((int) $status) {
        1 => "<span class='laranja'>Pendente</span>",
        2 => "<span class='azul'>Calculado</span>",
        3 => "<span class='verde'>Aprovado</span>",
        4 => "<span class='vermelho'>Reprovado</span>",
        default => "",
    };
}

// Função para exibir a periodicidade
function periodicidadeLabel($periodicidade): string
{
    return match ((int) $periodicidade) {
        6 => 'Semestral',
        12 => 'Anual',
        24 => 'Bienal',
        36 => 'Trienal',
        48 => 'Quadrienal',
        60 => 'Quinquenal',
        default => '',
    };
}

// Título da página
$titulo = 'Administrativo - Página Inicial';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title><?= htmlspecialchars($titulo) ?></title>
    <meta name="author" content="MogiComp">
    <meta charset="utf-8" />
    <link rel="shortcut icon" href="../imagens/favicon.png">
    <?php
    include '../css/style.php';
    require_once '../mod_includes/php/funcoes-jquery.php';
    ?>
    <script src="../mod_includes/js/jquery-1.8.3.min.js"></script>
</head>

<body>
    <?php include '../mod_topo/topo.php'; ?>

    <div class="centro">
        <div class="titulo"><?= $titulo ?></div>
        <table width="100%">
            <tr>
                <td align="justify" valign="top">
                    <!-- Últimas ações dos clientes -->
                    <div class="quadro_home">
                        <div class="formtitulo">Últimas ações dos clientes</div>
                        <?php
                        $notificacoes = buscarLinhas($pdo, "SELECT * FROM notificacoes ORDER BY not_id DESC LIMIT 10");
                        renderizarTabela(
                            [
                                ['label' => 'Nome'],
                                ['label' => 'Obs']
                            ],
                            $notificacoes,
                            fn($linha, $classe) => "
                                <tr class='$classe'>
                                    <td>" . $linha['not_nome'] . "</td>
                                    <td>" . $linha['not_obs'] . "</td>
                                </tr>"
                        );
                        ?>
                    </div>
                    <br>
                    <!-- Orçamentos Pendentes -->
                    <div class="quadro_home">
                        <div class="formtitulo">Orçamentos Pendentes</div>
                        <?php
                        $orcamentosPendentes = buscarLinhas(
                            $pdo,
                            "SELECT o.*, c.cli_nome_razao, t.tps_nome, h1.sto_status
                             FROM orcamento_gerenciar o
                             LEFT JOIN (cadastro_clientes c
                                INNER JOIN cadastro_usuarios_clientes u ON u.ucl_cliente = c.cli_id)
                                ON c.cli_id = o.orc_cliente
                             LEFT JOIN cadastro_tipos_servicos t ON t.tps_id = o.orc_tipo_servico
                             LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = o.orc_id
                             WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento)
                                AND u.ucl_usuario = :usuario_id AND h1.sto_status = 1
                             ORDER BY o.orc_data_cadastro DESC
                             LIMIT 10",
                            ['usuario_id' => $_SESSION['usuario_id']]
                        );
                        renderizarTabela(
                            [
                                ['label' => 'N° Orçamento'],
                                ['label' => 'Cliente'],
                                ['label' => 'Serviço'],
                                ['label' => 'Status', 'extra' => " align='center'"],
                                ['label' => 'Data Cadastro', 'extra' => " align='center'"],
                                ['label' => 'Imprimir', 'extra' => " align='center'"]
                            ],
                            $orcamentosPendentes,
                            function ($linha, $classe) {
                                $orc_id = htmlspecialchars($linha['orc_id']);
                                $cli_nome_razao = htmlspecialchars($linha['cli_nome_razao']);
                                $tps_nome = $linha['tps_nome'] ? htmlspecialchars($linha['tps_nome']) : htmlspecialchars($linha['orc_tipo_servico_cliente']) . "<br><span class='detalhe'>Digitado pelo cliente</span>";
                                $sto_status_n = statusOrcamentoLabel($linha['sto_status']);
                                $dataCadastro = formatarData($linha['orc_data_cadastro'], true);
                                $autenticacao = ''; // Defina se necessário
                                return "
                                    <tr class='$classe'>
                                        <td>$orc_id</td>
                                        <td>$cli_nome_razao</td>
                                        <td>$tps_nome</td>
                                        <td align='center'>$sto_status_n</td>
                                        <td align='center'>$dataCadastro</td>
                                        <td align='center'>
                                            <img class='mouse' src='../imagens/icon-pdf.png' onclick=\"window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');\">
                                        </td>
                                    </tr>";
                            }
                        );
                        ?>
                    </div>
                    <br>
                    <!-- Orçamentos calculados e ainda não aprovados -->
                    <div class="quadro_home">
                        <div class="formtitulo">Orçamentos calculados e ainda não aprovados</div>
                        <?php
                        $orcamentosCalculados = buscarLinhas(
                            $pdo,
                            "SELECT o.*, c.cli_nome_razao, t.tps_nome, h1.sto_status
                             FROM orcamento_gerenciar o
                             LEFT JOIN (cadastro_clientes c
                                INNER JOIN cadastro_usuarios_clientes u ON u.ucl_cliente = c.cli_id)
                                ON c.cli_id = o.orc_cliente
                             LEFT JOIN cadastro_tipos_servicos t ON t.tps_id = o.orc_tipo_servico
                             LEFT JOIN cadastro_status_orcamento h1 ON h1.sto_orcamento = o.orc_id
                             WHERE h1.sto_id = (SELECT MAX(h2.sto_id) FROM cadastro_status_orcamento h2 WHERE h2.sto_orcamento = h1.sto_orcamento)
                                AND u.ucl_usuario = :usuario_id AND h1.sto_status = 2
                             ORDER BY o.orc_data_cadastro DESC
                             LIMIT 10",
                            ['usuario_id' => $_SESSION['usuario_id']]
                        );
                        renderizarTabela(
                            [
                                ['label' => 'N° Orçamento'],
                                ['label' => 'Cliente'],
                                ['label' => 'Serviço'],
                                ['label' => 'Status', 'extra' => " align='center'"],
                                ['label' => 'Data Cadastro', 'extra' => " align='center'"],
                                ['label' => 'Imprimir', 'extra' => " align='center'"]
                            ],
                            $orcamentosCalculados,
                            function ($linha, $classe) {
                                $orc_id = htmlspecialchars($linha['orc_id']);
                                $cli_nome_razao = htmlspecialchars($linha['cli_nome_razao']);
                                $tps_nome = $linha['tps_nome'] ? htmlspecialchars($linha['tps_nome']) : htmlspecialchars($linha['orc_tipo_servico_cliente']) . "<br><span class='detalhe'>Digitado pelo cliente</span>";
                                $sto_status = $linha['sto_status'];
                                $sto_status_n = statusOrcamentoLabel($sto_status);
                                $dataCadastro = formatarData($linha['orc_data_cadastro'], true);
                                $autenticacao = ''; // Defina se necessário
                                $imprimir = ($sto_status == 2 || $sto_status == 3 || $sto_status == 4)
                                    ? "<img class='mouse' src='../imagens/icon-pdf.png' onclick=\"window.open('orcamento_imprimir.php?orc_id=$orc_id$autenticacao');\">"
                                    : '';
                                return "
                                    <tr class='$classe'>
                                        <td>$orc_id</td>
                                        <td>$cli_nome_razao</td>
                                        <td>$tps_nome</td>
                                        <td align='center'>$sto_status_n</td>
                                        <td align='center'>$dataCadastro</td>
                                        <td align='center'>$imprimir</td>
                                    </tr>";
                            }
                        );
                        ?>
                    </div>
                    <br>
                    <!-- Documentos à vencer nos próximos 30 dias -->
                    <div class="quadro_home">
                        <div class="formtitulo">Documentos à vencer nos próximos 30 dias</div>
                        <?php
                        $hoje = date('Y-m-d');
                        $hoje30 = date('Y-m-d', strtotime('+30 days'));
                        $documentosVencer = buscarLinhas(
                            $pdo,
                            "SELECT d.*, c.cli_nome_razao, t.tpd_nome, o.orc_id, s.tps_nome
                             FROM documento_gerenciar d
                             LEFT JOIN (cadastro_clientes c
                                INNER JOIN cadastro_usuarios_clientes u ON u.ucl_cliente = c.cli_id)
                                ON c.cli_id = d.doc_cliente
                             LEFT JOIN cadastro_tipos_docs t ON t.tpd_id = d.doc_tipo
                             LEFT JOIN (orcamento_gerenciar o
                                LEFT JOIN cadastro_tipos_servicos s ON s.tps_id = o.orc_tipo_servico)
                                ON o.orc_id = d.doc_orcamento
                             WHERE d.doc_data_vencimento BETWEEN :hoje AND :hoje30
                                AND u.ucl_usuario = :usuario_id
                             ORDER BY d.doc_data_cadastro DESC",
                            [
                                'hoje' => $hoje,
                                'hoje30' => $hoje30,
                                'usuario_id' => $_SESSION['usuario_id']
                            ]
                        );
                        renderizarTabela(
                            [
                                ['label' => 'Tipo de Doc'],
                                ['label' => 'Cliente'],
                                ['label' => 'Orçamento'],
                                ['label' => 'Data Emissão', 'extra' => " align='center'"],
                                ['label' => 'Periodicidade', 'extra' => " align='center'"],
                                ['label' => 'Data Vencimento', 'extra' => " align='center'"],
                                ['label' => 'Anexo', 'extra' => " align='center'"]
                            ],
                            $documentosVencer,
                            function ($linha, $classe) {
                                $orc_id = htmlspecialchars($linha['orc_id']);
                                $tps_nome = htmlspecialchars($linha['tps_nome']);
                                $tpd_nome = htmlspecialchars($linha['tpd_nome']);
                                $doc_anexo = $linha['doc_anexo'];
                                $doc_periodicidade_n = periodicidadeLabel($linha['doc_periodicidade']);
                                $doc_data_emissao = formatarData($linha['doc_data_emissao']);
                                $doc_data_vencimento = formatarData($linha['doc_data_vencimento']);
                                $cli_nome_razao = htmlspecialchars($linha['cli_nome_razao']);
                                $anexo = !empty($doc_anexo)
                                    ? "<a href='" . htmlspecialchars($doc_anexo) . "' target='_blank'><img src='../imagens/icon-pdf.png' valign='middle'></a>"
                                    : '';
                                return "
                                    <tr class='$classe'>
                                        <td>$tpd_nome</td>
                                        <td>$cli_nome_razao</td>
                                        <td>$orc_id ($tps_nome)</td>
                                        <td align='center'>$doc_data_emissao</td>
                                        <td align='center'>$doc_periodicidade_n</td>
                                        <td align='center'>$doc_data_vencimento</td>
                                        <td align='center'>$anexo</td>
                                    </tr>";
                            }
                        );
                        ?>
                    </div>
                    <br>
                    <!-- Malotes com documentos à vencer -->
                    <div class="quadro_home">
                        <div class="formtitulo">Malotes com documentos à vencer</div>
                        <?php
                        $hoje1 = date('Y-m-d', strtotime('+1 days'));
                        $malotesVencer = buscarLinhas(
                            $pdo,
                            "SELECT m.*, mi.*, c.cli_nome_razao
                             FROM malote_itens mi
                             INNER JOIN (malote_gerenciar m
                                LEFT JOIN (cadastro_clientes c
                                    INNER JOIN cadastro_usuarios_clientes u ON u.ucl_cliente = c.cli_id)
                                ON c.cli_id = m.mal_cliente)
                             ON m.mal_id = mi.mai_malote
                             WHERE mi.mai_data_vencimento BETWEEN :hoje AND :hoje1 AND mi.mai_baixado IS NULL
                                AND u.ucl_usuario = :usuario_id
                             GROUP BY mi.mai_malote
                             ORDER BY m.mal_data_cadastro DESC",
                            [
                                'hoje' => $hoje,
                                'hoje1' => $hoje1,
                                'usuario_id' => $_SESSION['usuario_id']
                            ]
                        );
                        renderizarTabela(
                            [
                                ['label' => 'N° Malote'],
                                ['label' => 'N° Lacre'],
                                ['label' => 'Cliente'],
                                ['label' => 'Observação'],
                                ['label' => 'Data Cadastro', 'extra' => " align='center'"]
                            ],
                            $malotesVencer,
                            function ($linha, $classe) {
                                $mal_id = htmlspecialchars($linha['mal_id']);
                                $mal_lacre = htmlspecialchars($linha['mal_lacre']);
                                $cli_nome_razao = htmlspecialchars($linha['cli_nome_razao']);
                                $mal_observacoes = htmlspecialchars($linha['mal_observacoes']);
                                $mal_data_cadastro = formatarData($linha['mal_data_cadastro'], true);
                                $autenticacao = ''; // Defina se necessário
                                return "
                                    <tr class='$classe'>
                                        <td><a href='malote_gerenciar.php?pagina=exibir_malote_gerenciar&mal_id=$mal_id$autenticacao'><b>$mal_id</b></a></td>
                                        <td>$mal_lacre</td>
                                        <td>$cli_nome_razao</td>
                                        <td>$mal_observacoes</td>
                                        <td align='center'>$mal_data_cadastro</td>
                                    </tr>";
                            }
                        );
                        ?>
                    </div>
                </td>
            </tr>
        </table>
        <div class="titulo"></div>
    </div>
    <?php include '../mod_rodape/rodape.php'; ?>
</body>

</html>