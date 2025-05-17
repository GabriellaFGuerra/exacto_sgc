<?php
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

// URL de redirecionamento em caso de tentativa de invasão
$page = "http://www.plughackers.net/index.php";

// Captura de query string
$cracktrack = $_SERVER['QUERY_STRING'];

// Lista de padrões maliciosos
$wormprotector = [
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

// Verificação de padrões maliciosos
foreach ($wormprotector as $pattern) {
  if (stripos($cracktrack, $pattern) !== false) {
    header("Location: $page");
    exit();
  }
}

// Função segura contra SQL Injection com PDO
function anti_injection($sql, $pdo)
{
  $sql = trim($sql); // Remove espaços extras
  $sql = strip_tags($sql); // Remove tags HTML/PHP
  return $pdo->quote($sql); // Escapa a string de forma segura com PDO
}
?>