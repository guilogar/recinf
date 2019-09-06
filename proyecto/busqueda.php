<?php

ini_set('memory_limit', -1);
define('DIR_CORPUS', '/home/' . get_current_user() . '/web/corpus');
define('MAX_RESULTS_PAGE', 2);
define('MAX_RESULTS_PER_PAGE', 5);

require_once "utils/filtro.php";

$directorio_palabras_vacias = "./palabras_vacias.txt";
$palabras_vacias = explode(
    "\n", file_get_contents($directorio_palabras_vacias)
);

$filtros = array(
    new Filtro('/[\.\,¿\?¡\!\=\(\)\<\>\-\:\;\/%]/', " "),
    new Filtro('/[  ]/', " "),
);
foreach($palabras_vacias as $pv)
{
    array_push($filtros, new Filtro("/ $pv /", " "));
    array_push($filtros, new Filtro("/$pv /", " "));
    array_push($filtros, new Filtro("/ $pv/", " "));
}

$consulta_str = NULL;
if(isset($argv[1]) && $argv[1] !== NULL)
{
    $consulta_str = trim($argv[1]);
} else
{
    echo "Introduzca su consulta => ";
    $stdin = fopen ("php://stdin","r");
    $consulta_str = trim(fgets($stdin));
    fclose($stdin);
}

if($consulta_str === NULL) die("Consulta vacia, por favor, formulé una.");

foreach($filtros as $filtro)
{
    $consulta_str = $filtro->parsear($consulta_str);
}
$consulta = explode(" ", $consulta_str);

foreach($consulta as $indice => $palabra)
{
    foreach($filtros as $filtro)
    {
        $palabra = $filtro->parsear($palabra);
    }
    $palabra = stemword($palabra, "english", "UTF_8");
    $palabra = trim($palabra);
    $palabra = strtolower($palabra);
    $consulta[$indice] = $palabra;
}
$directorio_tfidf = DIR_CORPUS . "/tfidf";
$fichero          = $directorio_tfidf . "/tfidf.json";
$tfidf            = (array) json_decode(file_get_contents($fichero, FILE_USE_INCLUDE_PATH));

$tfidf_consulta = array();
foreach($consulta as $palabra)
{
    if(isset($tfidf[$palabra]))
    {
        array_push($tfidf_consulta, (array) $tfidf[$palabra]);
    }
}
$resultado = array();

foreach($tfidf_consulta as $tic)
{
    foreach($tic as $d => $v)
    {
        if(isset($resultado[$d]))
            $resultado[$d] += $v;
        else
            $resultado[$d] = $v;
    }
}
arsort($resultado);

$directorio_corpus = DIR_CORPUS . "/corpus_base";
$results_per_page = 0;
$pages = 0;

$consulta = explode(" ", $consulta_str);
foreach($consulta as $indice => $palabra)
{
    $consulta[$indice] = trim($palabra);
}
$consulta_grep = implode("|", $consulta);

foreach($resultado as $d => $v)
{
    $fichero = $directorio_corpus . '/' . $d . "\n\n";
    echo $fichero;

    $comando = "egrep -ri --color \"$consulta_grep\" $fichero";
    $se = shell_exec($comando);

    foreach($consulta as $palabra)
    {
        $se = preg_replace("/$palabra/i", "\x1b[31m" . $palabra . "\x1b[0m", $se);
    }
    echo $se . "\n\n";

    $results_per_page++;

    if($results_per_page >= MAX_RESULTS_PER_PAGE)
    {
        $results_per_page = 0; $pages++;
        echo "\n";
        echo "======== Página $pages ========" . "\n";
        echo "\n";
    }

    if($pages >= MAX_RESULTS_PAGE)
        break;
}
