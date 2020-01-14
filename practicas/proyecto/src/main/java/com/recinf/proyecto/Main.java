/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.recinf.proyecto;

import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.google.gson.reflect.TypeToken;
import java.io.FileWriter;
import java.lang.reflect.Type;

/**
 *
 * @author guillermo
 */
public class Main {
    
    public static ArrayList<Filtro> filtrosCaracteres = new ArrayList<>();
    public static ArrayList<Filtro> filtroStopWords = new ArrayList<>();
    
    public static void index() throws IOException
    {
        Indexacion i = new Indexacion("/home/guillermo/web/corpus/corpus_base");
        
        // Delete all useless characters
        // ArrayList<Filtro> filtrosCaracteres = new ArrayList<>();
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
        // ArrayList<Filtro> filtroStopWords = new ArrayList<>();
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
        
        // Make stem of word with all words
        i.stemming();
        
        //HashMap<String, String> ficheros = i.getFicheros();
        HashMap<String, HashMap<String, Double>> tf = i.tf();
        HashMap<String, HashMap<String, Double>> invertTf = i.getInvertTf();
        HashMap<String, Double> idf = i.idf();
        
        Gson gson = new GsonBuilder().setPrettyPrinting().create();
        Type gsonType = new TypeToken<HashMap>(){}.getType();
        
        String tfJson = gson.toJson(tf, gsonType);
        String invertTfJson = gson.toJson(invertTf, gsonType);
        String idfJson = gson.toJson(idf, gsonType);
        
        // System.out.println(tfJson);
        // System.out.println(idfJson);
        
        try (FileWriter file = new FileWriter("/home/guillermo/web/corpus/tf.json"))
        {
            file.write(tfJson);
        }
        
        try (FileWriter file = new FileWriter("/home/guillermo/web/corpus/idf.json"))
        {
            file.write(idfJson);
        }
        
        try (FileWriter file = new FileWriter("/home/guillermo/web/corpus/invertTf.json"))
        {
            file.write(invertTfJson);
        }
    }
    
    public static void search(String[] args) throws IOException
    {
        Busqueda b = new Busqueda("fix dna");
        b.filtrosCaracteres(filtrosCaracteres);
        b.stopWord(filtroStopWords);
        b.stemming();
        
        b.search();
    }
    
    public static void main(String[] args) throws IOException
    {
        index();
        search(args);
    }
}
