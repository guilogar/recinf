<?php // utils/utils.php

function num_system_cores()
{
    $cmd = "uname";
    $OS = strtolower(trim(shell_exec($cmd)));

    switch($OS)
    {
       case('linux'):
          $cmd = "cat /proc/cpuinfo | grep processor | wc -l";
          break;
       case('freebsd'):
          $cmd = "sysctl -a | grep 'hw.ncpu' | cut -d ':' -f2";
          break;
       default:
          unset($cmd);
    }

    if ($cmd != '') $cpuCoreNo = intval(trim(shell_exec($cmd)));

    return empty($cpuCoreNo) ? 1 : $cpuCoreNo;
}

$SEPARADOR                = "===============================================================" . "\n";
$PRINT_FILTRO_CARACTERES  = "---------------------------------------------------------------" . "\n";
$PRINT_FILTRO_CARACTERES .= "                     Filtro de Caracteres                      " . "\n";
$PRINT_FILTRO_CARACTERES .= "---------------------------------------------------------------" . "\n";

$PRINT_STOCKWORD          = "---------------------------------------------------------------" . "\n";
$PRINT_STOCKWORD         .= "                          StockWord                            " . "\n";
$PRINT_STOCKWORD         .= "---------------------------------------------------------------" . "\n";

$PRINT_STEMMING           = "---------------------------------------------------------------" . "\n";
$PRINT_STEMMING          .= "                          Stemming                             " . "\n";
$PRINT_STEMMING          .= "---------------------------------------------------------------" . "\n";

$PRINT_TF                 = "---------------------------------------------------------------" . "\n";
$PRINT_TF                .= "                            TF                                 " . "\n";
$PRINT_TF                .= "---------------------------------------------------------------" . "\n";

$PRINT_IDF                = "---------------------------------------------------------------" . "\n";
$PRINT_IDF               .= "                           IDF                                 " . "\n";
$PRINT_IDF               .= "---------------------------------------------------------------" . "\n";

$PRINT_TFIDF              = "---------------------------------------------------------------" . "\n";
$PRINT_TFIDF             .= "                          TF-IDF                               " . "\n";
$PRINT_TFIDF             .= "---------------------------------------------------------------" . "\n";
