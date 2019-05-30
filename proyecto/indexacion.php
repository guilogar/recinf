<?php

ini_set('memory_limit', -1);

define('DIR_CORPUS', '/home/oem/web/corpus');

if(PHP_ZTS && class_exists("Thread"))
{
    require_once "utils/threads.php";
    require_once "utils/filtro.php";
    require_once "utils/utils.php";
    
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
    
    foreach ($ficheros as $k => $fichero)
    {
        $ficheros[$k] = $directorio_corpus . DIRECTORY_SEPARATOR . $fichero;
        if(is_dir($directorio_corpus . DIRECTORY_SEPARATOR . $fichero))
        {
            unset($ficheros[$k]);
        }
    }
    
    $cb = 0.3;
    $num_cores = num_system_cores();
    $tam_pool = (int) ($num_cores / (1 - $cb));
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $ventana = (int) (sizeof($ficheros) / $tam_pool);
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
    foreach ($ficheros_preprocesados as $k => $fichero)
    {
        $ficheros_preprocesados[$k] = $directorio_corpus_preprocesado . DIRECTORY_SEPARATOR . $fichero;
        if(is_dir($directorio_corpus_preprocesado . DIRECTORY_SEPARATOR . $fichero))
        {
            unset($ficheros_preprocesados[$k]);
        }
    }
    
    $cb = 0.3;
    $num_cores = num_system_cores();
    $tam_pool = (int) ($num_cores / (1 - $cb));
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $ventana = (int) (sizeof($ficheros_preprocesados) / $tam_pool);
    $max = $ventana;
    
    $filtros = array();
    foreach($palabras_vacias as $pv)
    {
        array_push($filtros, new Filtro('/ '.$pv.' /', ' '));
    }
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
    
    echo "===============================================================" . "\n";
    
    echo "---------------------------------------------------------------" . "\n";
    echo "                         Stemming                              " . "\n";
    echo "---------------------------------------------------------------" . "\n";
    
    // #######################################################
    //                       Stemming
    // #######################################################
    $ficheros_stockword = scandir($directorio_corpus_stockword);
    foreach ($ficheros_stockword as $k => $fichero)
    {
        $ficheros_stockword[$k] = $directorio_corpus_stockword . DIRECTORY_SEPARATOR . $fichero;
        if(is_dir($directorio_corpus_stockword . DIRECTORY_SEPARATOR . $fichero))
        {
            unset($ficheros_stockword[$k]);
        }
    }
    
    $cb = 0.3;
    $num_cores = num_system_cores();
    $tam_pool = (int) ($num_cores / (1 - $cb));
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $ventana = (int) (sizeof($ficheros_stockword) / $tam_pool);
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
    
    echo "===============================================================" . "\n";
    
    echo "---------------------------------------------------------------" . "\n";
    echo "                           TF                              " . "\n";
    echo "---------------------------------------------------------------" . "\n";
    
    // #######################################################
    //                       TF*IDF
    // #######################################################
    $ficheros_stemming = scandir($directorio_corpus_stemming);
    foreach ($ficheros_stemming as $k => $fichero)
    {
        $ficheros_stemming[$k] = $directorio_corpus_stemming . DIRECTORY_SEPARATOR . $fichero;
        if(is_dir($directorio_corpus_stemming . DIRECTORY_SEPARATOR . $fichero))
        {
            unset($ficheros_stemming[$k]);
        }
    }
    
    $cb = 0.3;
    $num_cores = num_system_cores();
    $tam_pool = (int) ($num_cores / (1 - $cb));
    
    $pool = new Pool($tam_pool);
    $min = 0;
    $ventana = (int) (sizeof($ficheros_stemming) / $tam_pool);
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
    
    /*
     *var_dump($tf->data);
     *foreach ($tf->data as $termino => $f)
     *{
     *    if(sizeof($f) > 1) var_dump($f);
     *}
     */
    echo "===============================================================" . "\n";
    
    echo "---------------------------------------------------------------" . "\n";
    echo "                             IDF                               " . "\n";
    echo "---------------------------------------------------------------" . "\n";
    
    $idf = new DataIdf();
    //$idf->data = $tf->data;
    
    $terminos = array_keys($tf->data);
    $valores  = array_values($tf->data);
    $frecuencias = array();
    foreach ($valores as $v)
    {
        array_push($frecuencias, sizeof($v));
    }
    
    $idf->data = array_combine($terminos, $frecuencias);
    
    var_dump($idf->data);
} else
{
    
}
