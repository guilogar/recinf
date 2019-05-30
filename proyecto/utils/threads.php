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
    
    public function add(string $ruta_fichero, string $stemming)
    {
        $this->data[$ruta_fichero] = $stemming;
    }
}

class DataTf extends Threaded
{
    public $data;
    
    public function __construct()
    {
        $this->data = [];
    }
    
    public function add(array &$tf)
    {
        foreach ($tf as $termino => $documentos)
        {
            $valores = array();
            if(isset($this->data[$termino]))
                $valores = $this->data[$termino];
            
            $valores = array_merge((array) $valores, $documentos);
            
            $this->data[$termino] = (array) $valores;
        }
    }
}

class DataIdf extends Threaded
{
    public $data;
    
    public function __construct()
    {
        $this->data = [];
    }
    
    public function add(array &$idf)
    {
        foreach ($idf as $termino => $documentos)
        {
            $this->data[$termino] = $documentos;
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
            $texto_fichero_parseado = trim($texto_fichero_parseado);
            
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
            $texto_fichero = file_get_contents($fichero, FILE_USE_INCLUDE_PATH);
            foreach($this->filtros as $filtro)
            {
                $texto_fichero = $filtro->parsear($texto_fichero);
            }
            
            $palabras = explode(" ", $texto_fichero);
            $stemming = "";
            foreach($palabras as $word)
            {
                if(strlen($word) <= 1) continue;
                
                $stem = stemword($word, "english", "UTF_8");
                $stemming .= " " . $stem;
            }
            $stemming = trim($stemming);
            
            $this->f->synchronized(function ($f, string $ruta_fichero, string $stemming)
            {
                $f->add($ruta_fichero, $stemming);
            }, $this->f, basename($fichero), $stemming);
        }
    }
}

class Tfidf extends Threaded
{
    private $ficheros;
    private $filtros;
    private $f;
    
    public function __construct(array $ficheros, array $filtros, DataTf $f)
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
            $fich = basename($fichero);
            $texto_fichero = file_get_contents($fichero, FILE_USE_INCLUDE_PATH);
            foreach($this->filtros as $filtro)
            {
                $texto_fichero = $filtro->parsear($texto_fichero);
            }
            
            $palabras = explode(" ", $texto_fichero);
            $num_palabras = sizeof($palabras);
            
            foreach($palabras as $word)
            {
                if(isset($data[$word]) && isset($data[$word][$fich]))
                    continue;
                
                $w = array($word);
                $palabras_sin_w = array_diff($palabras, $w);
                $num_palabras_sin_w = sizeof($palabras_sin_w);
                $ocurrencias = $num_palabras - $num_palabras_sin_w;
                $tf = (double) ($ocurrencias / $num_palabras);
                
                $data[$word] = array(
                    $fich => $tf
                );
            }
        }
        
        $this->f->synchronized(function ($f, array $tf)
        {
            $f->add($tf);
        }, $this->f, $data);
    }
}
