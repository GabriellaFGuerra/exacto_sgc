<?php
require_once("../mod_includes/php/ctracker.php");
include('../mod_includes/php/connect.php');
include('../mod_includes/php/funcoes-jquery.php');
include('../mod_includes/php/funcoes.php');

$hoje = date("Y-m-d", strtotime("+60 days"));

// Consulta segura com PDO
$sql_vence_fatura = "SELECT * FROM documento_gerenciar 
                     LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
                     LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
                     LEFT JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
                     LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
                     WHERE doc_data_vencimento <= :hoje AND cli_status = 1
                     ORDER BY cli_nome_razao ASC, doc_data_vencimento DESC";

$stmt = $pdo->prepare($sql_vence_fatura);
$stmt->bindParam(':hoje', $hoje);
$stmt->execute();
$rows_vence_fatura = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($rows_vence_fatura) {
    $tipo_doc = "";
    $clientes = "";
    $data_emissao = "";
    $periodicidade = "";
    $data_vencimento = "";

    foreach ($rows_vence_fatura as $row) {
        $tipo_doc .= htmlspecialchars($row['tpd_nome']) . "<br>";
        $clientes .= htmlspecialchars($row['cli_nome_razao']) . "<br>";
        $data_emissao .= date("d/m/Y", strtotime($row['doc_data_emissao'])) . "<br>";

        $periods = [
            6 => "Semestral", 12 => "Anual", 24 => "Bienal",
            36 => "Trienal", 48 => "Quadrienal", 60 => "Quinquenal"
        ];
        $periodicidade .= $periods[$row['doc_periodicidade']] ?? "Desconhecido" . "<br>";
        $data_vencimento .= date("d/m/Y", strtotime($row['doc_data_vencimento'])) . "<br>";
    }

    include('../mail/envia_email_docs_a_receber.php');
}

include('../mod_rodape/rodape.php');
?>
