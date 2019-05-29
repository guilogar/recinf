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

class DataStemming extends Volatile
{
    public $data;
    
    public function __construct()
    {
        $this->data = [];
    }
    
    public function addArray(array $palabras_stemming)
    {
        echo "Metiendo => " . sizeof($palabras_stemming) . "\n";
        $this->data = array_merge_recursive((array) $this->data, $palabras_stemming);
        echo "Metido \n";
        /*
         *$valores = (array) $this->data;
         *$valores = array_merge_recursive($valores, $palabras_stemming);
         *$this->data = (array) $valores;
         */
    }
}

class DataStemmingUnique extends Volatile
{
    public $data;
    
    public function __construct()
    {
        $this->data = [];
    }
    
    public function addArray(array $pst_unique)
    {
        $valores = (array) $this->data;
        $valores = array_merge($valores, $pst_unique);
        $this->data = (array) $valores;
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

class Stemming extends Volatile
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
        $data = array();
        foreach($this->ficheros as $fichero)
        {
            //echo $fichero . "\n";
            $texto_fichero = file_get_contents($fichero, FILE_USE_INCLUDE_PATH);
            foreach($this->filtros as $filtro)
            {
                $texto_fichero = $filtro->parsear($texto_fichero);
            }
            $palabras = explode(" ", $texto_fichero);
            foreach($palabras as $word)
            {
                $stem = stemword($word, "english", "UTF_8");
                if(!isset($data[$stem]))
                {
                    $data[$stem] = array();
                }
                
                if($stem !== $word)
                {
                    array_push($data[$stem], $word);
                }
            }
        }
        
        echo "Deseando entrar en el bloque sincronizado....\n";
        $this->f->synchronized(function ($f, array $data)
        {
            $f->addArray($data);
        }, $this->f, $data);
        echo "Saliendo del bloque sincronizado....\n";
    }
}

class StemmingUnique extends Threaded
{
    private $f;
    private $stu;
    
    public function __construct(array $f, DataStemmingUnique $stu)
    {
        $this->f = $f;
        $this->stu = $stu;
    }
    
    public function run()
    {
        foreach ($this->f as $k => $v)
        {
            $v = array_unique($v);
            $this->f[$k] = $v;
        }
        $this->stu->synchronized(function ($stu, $data)
        {
            $stu->addArray($data);
        }, $this->stu, $this->f);
    }
}
