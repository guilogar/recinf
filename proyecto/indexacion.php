<?php

if(PHP_ZTS && class_exists("Thread"))
{
    require_once "utils/threads.php";
    require_once "utils/filtro.php";
    require_once "utils/utils.php";
    
    $fp = new FicherosPreprocesados();
    $sw = new StockWord();
    $st = new Stemming();
    
    $f = new Filtro('//');
    
    $directorio_corpus = "/home/oem/web/corpus";
    $ficheros = scandir($directorio_corpus);
    var_dump($ficheros);
    
    foreach ($ficheros as $k => $fichero)
    {
        if(is_dir($directorio_corpus . DIRECTORY_SEPARATOR . $fichero))
        {
            unset($ficheros[$k]);
        }
    }
    var_dump($ficheros);
} else
{
    
}
