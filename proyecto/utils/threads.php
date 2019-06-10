<?php // utils/threads.php

require_once "filtro.php";

class Cerrojo extends Threaded { }

class DataDeteccionDirectorios extends Volatile
{
    public $data;
    
    public function __construct()
    {
        $this->data = [];
    }
    
    public function add(string $directorio)
    {
        $this->data[sizeof($this->data)] = $directorio;
    }
    
    public function clean()
    {
        $this->data = [];
    }
}

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
                $valores = (array) $this->data[$termino];
            
            foreach($documentos as $d => $v)
            {
                $valores[$d] = $v;
            }
            
            $this->data[$termino] = (array) $valores;
        }
    }
}

class DataIdf extends Volatile
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

class DataTfIdf extends Threaded
{
    public $data;
    
    public function __construct(/*$tf, $idf*/)
    {
        $this->data = [];
    }
    
    public function add(array &$tfidf)
    {
        foreach ($tfidf as $termino => $documentos)
        {
            $this->data[$termino] = (array) $documentos;
        }
    }
}

class DeteccionDirectorio extends Threaded
{
    private $ficheros;
    private $directorio_base;
    private $f;
    
    public function __construct(array $ficheros, $directorio_base, DataDeteccionDirectorios $f)
    {
        $this->ficheros = $ficheros;
        $this->directorio_base = $directorio_base;
        $this->f = $f;
    }
    
    public function run()
    {
        foreach ($this->ficheros as $fichero)
        {
            if(!is_dir($fichero))
            {
                $this->f->synchronized(function ($f, $directorio, $directorio_base)
                {
                    $f->add($directorio_base . DIRECTORY_SEPARATOR . $directorio);
                }, $this->f, $fichero, $this->directorio_base);
            }
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

class Tf extends Threaded
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
        if(isset($data["The"]))
            var_dump($data["The"]);
        
        $this->f->synchronized(function ($f, array $tf)
        {
            $f->add($tf);
        }, $this->f, $data);
    }
}

class TfIdf extends Threaded
{
    private $tf;
    private $idf;
    private $f;
    private $min, $max;
    
    public function __construct(array &$tf, array &$idf, DataTfIdf $f, $min = 0, $max = 0)
    {
        $this->tf  = (array) $tf;
        $this->idf = (array) $idf;
        $this->f   = $f;
        $this->min = $min;
        $this->max = $max;
    }
    
    public function run()
    {
        $tf  = array_slice((array) $this->tf,  $this->min, $this->max);
        $idf = array_slice((array) $this->idf, $this->min, $this->max);
        $data = array();
        
        foreach($tf as $termino => $documentos)
        {
            foreach($documentos as $d => $f)
            {
                $documentos[$d] = $f * $idf[$termino];
            }
            $data[$termino] = $documentos;
        }
        
        $this->f->synchronized(function ($f, array $tfidf)
        {
            $f->add($tfidf);
        }, $this->f, $data);
    }
}
