<?php // utils/threads.php

require_once "filtro.php";

class Cerrojo extends Threaded { }

class FicherosPreprocesados extends Threaded
{
    public $data;
    
    public function __construct()
    {
        $this->data = [];
    }
    
    public function add(string $ruta_fichero, string $fichero_preprocesado)
    {
        //$this->data[sizeof($this->data)] = (object) $fichero_preprocesado;
        $this->data[$ruta_fichero] = $fichero_preprocesado;
    }
}

class StockWord extends Threaded
{
    public $data;
    
    public function __construct()
    {
        $this->data = [];
    }
    
    public function add(string $ruta_fichero, string $stock_words)
    {
        //$this->data[sizeof($this->data)] = (object) $stock_words;
        $this->data[$ruta_fichero] = $stock_words;
    }
}

class Stemming extends Threaded
{
    public $data;
    
    public function __construct()
    {
        $this->data = [];
    }
    
    public function add(string $ruta_fichero, string $stemming)
    {
        //$this->data[sizeof($this->data)] = (object) $stemming;
        $this->data[$ruta_fichero] = $stemming;
    }
}

class Preprocesador extends Threaded
{
    private $ficheros;
    private $filtros;
    private $f;
    
    public function __construct(array $ficheros, array $filtros, FicherosPreprocesados $f)
    {
        $this->ficheros = $ficheros;
        $this->filtros = $filtros;
        $this->f = $f;
    }
    
    public function run()
    {
        foreach($this->ficheros as $fichero)
        {
            $texto_fichero          = file_get_contents($fichero, FILE_USE_INCLUDE_PATH);
            $texto_fichero_parseado = $texto_fichero;
            
            foreach($this->filtros as $filtro)
            {
                $texto_fichero_parseado = $filtro->parsear($texto_fichero_parseado);
            }
            
            $this->f->synchronized(function ($f, $ruta_fichero, $texto_fichero_parseado)
            {
                $f->add($ruta_fichero, $texto_fichero_parseado);
            }, $this->f, basename($fichero), $texto_fichero_parseado);
        }
    }
}

class FiltroTerminos extends Threaded
{
    private $ficheros;
    private $filtros;
    private $f;
    
    public function __construct(array $ficheros, array $filtros, StockWord $f)
    {
        $this->ficheros = $ficheros;
        $this->filtros = $filtros;
        $this->f = $f;
    }
    
    public function run()
    {
        foreach($this->ficheros as $fichero)
        {
            $texto_fichero          = file_get_contents($fichero, FILE_USE_INCLUDE_PATH);
            $texto_fichero_parseado = $texto_fichero;
            
            foreach($this->filtros as $filtro)
            {
                $texto_fichero_parseado = $filtro->parsear($texto_fichero_parseado);
            }
            
            $this->f->synchronized(function ($f, $ruta_fichero, $texto_fichero_parseado)
            {
                $f->add($ruta_fichero, $texto_fichero_parseado);
            }, $this->f, basename($fichero), $texto_fichero_parseado);
        }
    }
}
