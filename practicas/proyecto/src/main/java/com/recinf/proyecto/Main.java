/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.recinf.proyecto;

import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.LinkedHashSet;
import java.util.Map;

/**
 *
 * @author guillermo
 */
public class Main {
    
    public static void main(String[] args) throws IOException
    {
        Indexacion i = new Indexacion("/home/guillero/web/corpus/corpus_base/");
        
        // Delete all useless characters
        // new Filtro("[\.\"\'\,¿\?¡\!\=\(\)\<\>\-\:\;\/%]", " ")
        ArrayList<Filtro> filtrosCaracteres = new ArrayList<>();
        filtrosCaracteres.add(
            new Filtro("[\\.\"\',¿\\?¡\\!\\=\\(\\)\\<\\>\\-\\:\\;\\/%]", " ")
        );
        filtrosCaracteres.add(
            new Filtro("\\d", " ")
        );
        filtrosCaracteres.add(
            new Filtro("(\r\n|\r|\n)", " ")
        );
        
        i.filtrosCaracteres(filtrosCaracteres);

        // Delete all stop words
        ArrayList<Filtro> filtroStopWords = new ArrayList<>();
        String[] allStopWords = StopWords.getAllStopWords();
        
        for (String allStopWord : allStopWords)
        {
            filtroStopWords.add(
                new Filtro("\\s" + allStopWord + "\\s", " ")
            );
            
            filtroStopWords.add(
                new Filtro("\\." + allStopWord + "\\s", " ")
            );
            
            filtroStopWords.add(
                new Filtro("\\s" + allStopWord + "\\.", " ")
            );
        }
        
        i.stopWord(filtroStopWords);
        
        // Make stem of word all words
        i.stemming();
        
        //HashMap<String, String> ficheros = i.getFicheros();
        HashMap<String, HashMap<String, Double>> data = i.tf();
        
        JSONObject json = new JSONObject();
        json.putAll( data );
        System.out.printf( "JSON: %s", json.toString(2) );
    }
}
