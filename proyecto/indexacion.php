<?php

if(PHP_ZTS && class_exists("Thread"))
{
    require_once "utils/threads.php";
    require_once "utils/filtro.php";
    require_once "utils/utils.php";
    
    $fp = new FicherosPreprocesados();
    $sw = new StockWord();
    $st = new Stemming();
    
    $filtros = array(
        new Filtro('/\.\,¿\?¡\!\=/', ""),
    );
    
    $directorio_corpus = "/home/oem/web/corpus";
    $directorio_corpus_preprocesado = "/home/oem/web/corpus_preprocesado";
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
    
    foreach ($fp->data as $f => $t)
    {
        $dir = $directorio_corpus_preprocesado . DIRECTORY_SEPARATOR;
        file_put_contents($dir . $f, $t);
    }

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
    
    $filtros = array(
        new Filtro(...);
    );
    for ($i = 0; $i < $tam_pool; $i++)
    {
        $ff = array_slice($ficheros_preprocesados, $min, $max);
        $p = new FiltroTerminos($ff, $filtros, $fp);
        $pool->submit($p);
        $min = $max + 1;
        $max += $ventana;
    }
    while($pool->collect());
    $pool->shutdown();
    
} else
{
    
}
