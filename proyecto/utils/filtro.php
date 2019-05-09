<?php

interface iFiltro
{
    public function parsear(string $texto);
}

class Filtro implements iFiltro
{
    private $patron;
    
    public function __construct(string $patron)
    {
        $this->patron = $patron;
    }
    
    public function parsear(string $texto)
    {
        return preg_replace($this->patron, '', $texto);
    }
}
