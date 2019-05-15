<?php

interface iFiltro
{
    public function parsear(string $texto);
    public function parsear_array(array $textos);
}

class Filtro implements iFiltro
{
    private $patron;
    private $sustitucion;
    
    public function __construct(string $patron, $sustitucion)
    {
        $this->patron = $patron;
        $this->sustitucion = $sustitucion;
    }
    
    public function parsear(string $texto)
    {
        return preg_replace($this->patron, $this->sustitucion, $texto);
    }
    
    public function parsear_array(array $textos)
    {
        return preg_replace($this->patron, $this->sustitucion, $textos);
    }
}
