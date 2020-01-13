/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.recinf.proyecto;

/**
 *
 * @author guillermo
 */
public class Filtro {
    
    public String pattern;
    public String replace;
    
    public Filtro(String pattern, String replace)
    {
        this.pattern = pattern;
        this.replace = replace;
    }
    
    public String aplicarFiltro(String origen)
    {
        return origen.replaceAll(this.pattern, this.replace);
    }
}
