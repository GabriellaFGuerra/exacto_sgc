<?php
require_once '../mod_includes/php/ctracker.php';
require_once '../mod_includes/php/connect.php';
require_once '../mod_includes/php/funcoes-jquery.php';
require_once '../mod_includes/php/funcoes.php';

const PERIODS = [
    6  => 'Semestral',
    12 => 'Anual',
    24 => 'Bienal',
    36 => 'Trienal',
    48 => 'Quadrienal',
    60 => 'Quinquenal'
];

function getDueDate($daysAhead = 60): string {
    return date('Y-m-d', strtotime("+{$daysAhead} days"));
}

function fetchDueDocuments(PDO $pdo, string $dueDate): array {
    $sql = "
        SELECT * FROM documento_gerenciar 
        LEFT JOIN cadastro_clientes ON cadastro_clientes.cli_id = documento_gerenciar.doc_cliente
        LEFT JOIN cadastro_tipos_docs ON cadastro_tipos_docs.tpd_id = documento_gerenciar.doc_tipo
        LEFT JOIN orcamento_gerenciar ON orcamento_gerenciar.orc_id = documento_gerenciar.doc_orcamento
        LEFT JOIN cadastro_tipos_servicos ON cadastro_tipos_servicos.tps_id = orcamento_gerenciar.orc_tipo_servico
        WHERE doc_data_vencimento <= :dueDate AND cli_status = 1
        ORDER BY cli_nome_razao ASC, doc_data_vencimento DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':dueDate', $dueDate);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buildDocumentFields(array $documents): array {
    $fields = [
        'tipo_doc'        => '',
        'clientes'        => '',
        'data_emissao'    => '',
        'periodicidade'   => '',
        'data_vencimento' => ''
    ];

    foreach ($documents as $doc) {
        $fields['tipo_doc']        .= htmlspecialchars($doc['tpd_nome']) . '<br>';
        $fields['clientes']        .= htmlspecialchars($doc['cli_nome_razao']) . '<br>';
        $fields['data_emissao']    .= formatDate($doc['doc_data_emissao']) . '<br>';
        $fields['periodicidade']   .= PERIODS[$doc['doc_periodicidade']] ?? 'Desconhecido';
        $fields['periodicidade']   .= '<br>';
        $fields['data_vencimento'] .= formatDate($doc['doc_data_vencimento']) . '<br>';
    }

    return $fields;
}

function formatDate($date): string {
    return date('d/m/Y', strtotime($date));
}

$dueDate = getDueDate();
$dueDocuments = fetchDueDocuments($pdo, $dueDate);

if ($dueDocuments) {
    $documentFields = buildDocumentFields($dueDocuments);
    extract($documentFields); // $tipo_doc, $clientes, $data_emissao, $periodicidade, $data_vencimento
    require_once '../mail/envia_email_docs_a_receber.php';
}

require_once '../mod_rodape/rodape.php';