<?php
declare(strict_types=1);

error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

// URL de redirecionamento em caso de tentativa de invasão
const REDIRECT_URL = 'http://www.plughackers.net/index.php';

// Lista de padrões maliciosos
const MALICIOUS_PATTERNS = [
  'chr(',
  'chr=',
  'wget ',
  'cmd=',
  'union ',
  'echr(',
  'esystem(',
  'chmod(',
  'chown(',
  'kill(',
  'passwd ',
  'telnet ',
  'insert into',
  'select ',
  'fopen',
  'fwrite',
  'config.php',
  'phpinfo()',
  '<?php',
  '?>',
  'sql='
];

// Captura de query string
$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Verificação de padrões maliciosos
foreach (MALICIOUS_PATTERNS as $pattern) {
  if (stripos($queryString, $pattern) !== false) {
    header('Location: ' . REDIRECT_URL);
    exit;
  }
}

// Função segura contra SQL Injection com PDO
function antiInjection(string $input, PDO $pdo): string
{
  $input = trim(strip_tags($input));
  return $pdo->quote($input);
}