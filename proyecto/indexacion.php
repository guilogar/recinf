<?php

ini_set('memory_limit', -1);

define('DIR_CORPUS', '/home/oem/web/corpus');
define('CB', 0.0);

if(PHP_ZTS && class_exists("Thread"))
{
    require_once "utils/threads.php";
    require_once "utils/filtro.php";
    require_once "utils/utils.php";
    
    $dd = new DataDeteccionDirectorios();
    $fp = new DataFicherosPreprocesados();
    $sw = new DataStockWord();
    $st = new DataStemming();
    $tf = new DataTf();
    
    echo "---------------------------------------------------------------" . "\n";
    echo "                     Filtro de Caracteres                      " . "\n";
    echo "---------------------------------------------------------------" . "\n";
    
    // #######################################################
    //                 Filtro de caracteres
    // #######################################################
    $filtros = array(
        new Filtro('/[\.\,¿\?¡\!\=\(\)\<\>\-\:\;\/]/', " "),
        new Filtro('/[  ]/', " "),
    );
    
    $directorio_corpus = DIR_CORPUS . "/corpus_base";
    $ficheros = scandir($directorio_corpus);
    
    $cb = CB;
    $num_cores = num_system_cores();
    $tam_pool = (int) ($num_cores / (1 - $cb));
    
    $ventana = (int) (sizeof($ficheros) / $tam_pool);
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $max = $ventana;
    for ($i = 0; $i < $tam_pool; $i++)
    {
        $ff = array_slice($ficheros, $min, $max);
        $p = new DeteccionDirectorio($ff, $directorio_corpus, $dd);
        $pool->submit($p);
        $min = $max + 1;
        $max += $ventana;
    }
    while($pool->collect());
    $pool->shutdown();
    
    $ficheros = (array) $dd->data;
    $dd->clean();
    
    echo "listo....";
    $pool = new Pool($tam_pool);
    $min = 0;
    $max = $ventana;
    for ($i = 0; $i < $tam_pool; $i++)
    {
        $ff = array_slice($ficheros, $min, $max);
        $p = new Preprocesador($ff, $filtros, $fp);
        $pool->submit($p);
        $min = $max + 1;
        $max += $ventana;
    }
    while($pool->collect());
    $pool->shutdown();
    
    $directorio_corpus_preprocesado = DIR_CORPUS . "/corpus_preprocesado";
    foreach ($fp->data as $f => $t)
    {
        $dir = $directorio_corpus_preprocesado . DIRECTORY_SEPARATOR;
        file_put_contents($dir . $f, $t);
    }
    
    echo sizeof($dd->data) . '\n';
    echo "===============================================================" . "\n";
    
    echo "---------------------------------------------------------------" . "\n";
    echo "                         StockWord                             " . "\n";
    echo "---------------------------------------------------------------" . "\n";
    
    // #######################################################
    //                       StockWord
    // #######################################################
    $directorio_palabras_vacias = "./palabras_vacias.txt";
    $palabras_vacias = explode(
        "\n", file_get_contents($directorio_palabras_vacias)
    );
    $ficheros_preprocesados = scandir($directorio_corpus_preprocesado);
    
    $cb = CB;
    $num_cores = num_system_cores();
    $tam_pool = (int) ($num_cores / (1 - $cb));
    
    $ventana = (int) (sizeof($ficheros_preprocesados) / $tam_pool);
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $max = $ventana;
    for ($i = 0; $i < $tam_pool; $i++)
    {
        $ff = array_slice($ficheros_preprocesados, $min, $max);
        $p = new DeteccionDirectorio($ff, $directorio_corpus_preprocesado, $dd);
        $pool->submit($p);
        $min = $max + 1;
        $max += $ventana;
    }
    while($pool->collect());
    $pool->shutdown();
    
    $ficheros_preprocesados = (array) $dd->data;
    $dd->clean();
    echo "listo....";
    
    $filtros = array();
    foreach($palabras_vacias as $pv)
    {
        array_push($filtros, new Filtro('/ '.$pv.' /', ' '));
    }
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $max = $ventana;
    for ($i = 0; $i < $tam_pool; $i++)
    {
        $ff = array_slice($ficheros_preprocesados, $min, $max);
        $p = new FiltroTerminos($ff, $filtros, $sw);
        $pool->submit($p);
        $min = $max + 1;
        $max += $ventana;
    }
    while($pool->collect());
    $pool->shutdown();
    
    $directorio_corpus_stockword = DIR_CORPUS . "/corpus_stockword";
    foreach ($sw->data as $s => $w)
    {
        $dir = $directorio_corpus_stockword . DIRECTORY_SEPARATOR;
        file_put_contents($dir . $s, $w);
    }
    
    echo sizeof($dd->data) . '\n';
    echo "===============================================================" . "\n";
    
    echo "---------------------------------------------------------------" . "\n";
    echo "                         Stemming                              " . "\n";
    echo "---------------------------------------------------------------" . "\n";
    
    // #######################################################
    //                       Stemming
    // #######################################################
    $ficheros_stockword = scandir($directorio_corpus_stockword);
    
    $cb = CB;
    $num_cores = num_system_cores();
    $tam_pool = (int) ($num_cores / (1 - $cb));
    
    $ventana = (int) (sizeof($ficheros_stockword) / $tam_pool);
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $max = $ventana;
    for ($i = 0; $i < $tam_pool; $i++)
    {
        $ff = array_slice($ficheros_stockword, $min, $max);
        $p = new DeteccionDirectorio($ff, $directorio_corpus_stockword, $dd);
        $pool->submit($p);
        $min = $max + 1;
        $max += $ventana;
    }
    while($pool->collect());
    $pool->shutdown();
    
    $ficheros_stockword = (array) $dd->data;
    $dd->clean();
    echo "listo....";
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $max = $ventana;
    
    $filtros = array(
        new Filtro('/(\r\n|\r|\n)/', " "),
    );
    for ($i = 0; $i < $tam_pool; $i++)
    {
        $ff = array_slice($ficheros_stockword, $min, $max);
        $p = new Stemming($ff, $filtros, $st);
        $pool->submit($p);
        $min = $max + 1;
        $max += $ventana;
    }
    while($pool->collect());
    $pool->shutdown();
    
    $directorio_corpus_stemming = DIR_CORPUS . "/corpus_stemming";
    foreach ($st->data as $s => $t)
    {
        $dir = $directorio_corpus_stemming . DIRECTORY_SEPARATOR;
        file_put_contents($dir . $s, $t);
    }
    
    echo sizeof($dd->data) . '\n';
    echo "===============================================================" . "\n";
    
    echo "---------------------------------------------------------------" . "\n";
    echo "                           TF                              " . "\n";
    echo "---------------------------------------------------------------" . "\n";
    
    // #######################################################
    //                       TF*IDF
    // #######################################################
    $ficheros_stemming = scandir($directorio_corpus_stemming);
    
    $cb = CB;
    $num_cores = num_system_cores();
    $tam_pool = (int) ($num_cores / (1 - $cb));
    
    $ventana = (int) (sizeof($ficheros_stemming) / $tam_pool);
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $max = $ventana;
    for ($i = 0; $i < $tam_pool; $i++)
    {
        $ff = array_slice($ficheros_stemming, $min, $max);
        $p = new DeteccionDirectorio($ff, $directorio_corpus_stemming, $dd);
        $pool->submit($p);
        $min = $max + 1;
        $max += $ventana;
    }
    while($pool->collect());
    $pool->shutdown();
    
    $ficheros_stemming = (array) $dd->data;
    $dd->clean();
    echo "listo....";
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $max = $ventana;
    
    $filtros = array();
    for ($i = 0; $i < $tam_pool; $i++)
    {
        $ff = array_slice($ficheros_stemming, $min, $max);
        $p = new Tfidf($ff, $filtros, $tf);
        $pool->submit($p);
        $min = $max + 1;
        $max += $ventana;
    }
    while($pool->collect());
    $pool->shutdown();
    
    echo sizeof($dd->data) . '\n';
    echo "===============================================================" . "\n";
    
    echo "---------------------------------------------------------------" . "\n";
    echo "                             IDF                               " . "\n";
    echo "---------------------------------------------------------------" . "\n";
    
    $idf = new DataIdf();
    
    $terminos = array_keys  ((array) $tf->data);
    $valores  = array_values((array) $tf->data);
    $frecuencias = array();
    foreach ($valores as $v)
    {
        array_push($frecuencias, sizeof($v));
    }
    
    $idf->data = array_combine($terminos, $frecuencias);
    
    $directorio_tfidf = DIR_CORPUS . "/tfidf" . DIRECTORY_SEPARATOR;
    file_put_contents($directorio_tfidf . 'tf.json', json_encode($tf->data, JSON_PRETTY_PRINT));
    file_put_contents($directorio_tfidf . 'idf.json', json_encode($idf->data, JSON_PRETTY_PRINT));
    
    /*
     *$tfidf = $tf->data;
     *foreach($tfidf as $termino => $documentos)
     *{
     *    foreach($documentos as $d => $f)
     *    {
     *        $documentos[$d] = $f * $idf->data[$termino];
     *    }
     *    $tfidf[$termino] = $documentos;
     *}
     *file_put_contents($directorio_tfidf . 'tfidf.json', json_encode($tfidf, JSON_PRETTY_PRINT));
     */
    //var_dump($idf->data);
    /*
     *foreach ($idf->data as $termino =>  $value) {
     *    if($value > 1) echo $termino . " => " . $value . "\n";
     *}
     */
} else
{
    
}
