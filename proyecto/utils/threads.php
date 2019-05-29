<?php // utils/threads.php

require_once "filtro.php";

class Cerrojo extends Threaded { }

class DataFicherosPreprocesados extends Threaded
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

class DataStockWord extends Threaded
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

class DataStemming extends Threaded
{
    public $data;
    
    public function __construct()
    {
        $this->data = [];
    }
    
    public function isIN(string $stem, string $word)
    {
        if(!isset($this->data[$stem])) return false;
        
        if($stem === $word) return true;
        
        $valores = (array) $this->data[$stem];
        
        return array_search($word, $valores) !== FALSE;
    }
    
    public function add(string $stem, string $word)
    {
        if(!isset($this->data[$stem]))
        {
            $this->data[$stem] = [];
        }
        
        if($stem !== $word)
        {
            $valores = (array) $this->data[$stem];
            array_push($valores, $word);
            $this->data[$stem] = (array) $valores;
        }
    }
}

class Preprocesador extends Threaded
{
    private $ficheros;
    private $filtros;
    private $f;
    
    public function __construct(array $ficheros, array $filtros, DataFicherosPreprocesados $f)
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
    
    public function __construct(array $ficheros, array $filtros, DataStockWord $f)
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

class Stemming extends Threaded
{
    private $ficheros;
    private $filtros;
    private $f;
    
    public function __construct(array $ficheros, array $filtros, DataStemming $f)
    {
        $this->ficheros = $ficheros;
        $this->filtros = $filtros;
        $this->f = $f;
    }
    
    public function run()
    {
        foreach($this->ficheros as $fichero)
        {
            echo $fichero . "\n";
            $texto_fichero = file_get_contents($fichero, FILE_USE_INCLUDE_PATH);
            foreach($this->filtros as $filtro)
            {
                $texto_fichero = $filtro->parsear($texto_fichero);
            }
            $palabras = explode(" ", $texto_fichero);
            foreach($palabras as $word)
            {
                $stem = stemword($word, "english", "UTF_8");
                if(!$this->f->isIN($stem, $word))
                {
                    $this->f->synchronized(function ($f, $stem, $word)
                    {
                        $f->add($stem, $word);
                    }, $this->f, $stem, $word);
                }
            }
        }
    }
}
