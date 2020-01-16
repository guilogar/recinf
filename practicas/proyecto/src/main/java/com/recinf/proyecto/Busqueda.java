/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.recinf.proyecto;

import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.google.gson.stream.JsonReader;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileReader;
import java.io.IOException;
import java.io.ObjectInputStream;
import java.lang.reflect.Type;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.HashMap;
import java.util.Iterator;
import java.util.LinkedHashMap;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;
import org.tartarus.snowball.SnowballStemmer;
import org.tartarus.snowball.ext.porterStemmer;

/**
 *
 * @author guillermo
 */
public class Busqueda {
    
    private String search;
    private String[] palabras;
    
    public Busqueda(String search)
    {
        this.search = search;
    }
    
    public String[] filtros(ArrayList<Filtro> filtros)
    {
        System.out.println("empieza el filtro de caracteres, stopwords y stemming");
        SnowballStemmer stemmer = new porterStemmer();
        
        // characters and stopwords...
        for(int i = 0; i < filtros.size(); i++)
        {
            Filtro f = filtros.get(i);
            this.search = f.aplicarFiltro(this.search);
        }
        
        String[] words = this.search.split(" ");
        // stemming...
        for(int i = 0; i < words.length; i++)
        {
            // delete spaces...
            words[i] = words[i].trim();

            // make stem of word...
            String rawString = words[i];
            stemmer.setCurrent(rawString);
            stemmer.stem();
            String stemmedString = stemmer.getCurrent();
            words[i] = stemmedString;
        }
        this.palabras = words;
        
        return this.palabras;
    }
    
    public HashMap<String, Double> search() throws IOException
    {
        Gson gson = new Gson();
        
        JsonReader reader = new JsonReader(new FileReader("/home/guillermo/web/corpus/tf.json"));
        HashMap<String, HashMap<String, Double>> tf = gson.fromJson(
            reader,
            new TypeToken<HashMap<String, HashMap<String, Double>>>() {}.getType()
        );
        
        reader = new JsonReader(new FileReader("/home/guillermo/web/corpus/idf.json"));
        HashMap<String, Double> idf = gson.fromJson(
            reader,
            new TypeToken<HashMap<String, Double>>() {}.getType()
        );
        
        
        reader = new JsonReader(new FileReader("/home/guillermo/web/corpus/longDocumentos.json"));
        HashMap<String, Double> longDocumentos = gson.fromJson(
            reader,
            new TypeToken<HashMap<String, Double>>() {}.getType()
        );
        
        HashMap<String, Double> files = new HashMap<>();
        
        String[] words = this.palabras;
        
        for(int i = 0; i < words.length; i++)
        {
            HashMap<String, Double> ficheros = tf.get(words[i]);
            
            if(ficheros != null)
            {
                Iterator it = ficheros.entrySet().iterator();
            
                while (it.hasNext())
                {
                    Map.Entry pair = (Map.Entry) it.next();
                    String fileName = (String) pair.getKey();
                    double numerador = 0;
                    
                    if(files.get(fileName) != null)
                    {
                        numerador = (double) files.get(fileName);
                    }
                    
                    double tfValue = (double) ficheros.get(fileName);
                    double idfValue = (double) idf.get(words[i]);
                    
                    numerador += (tfValue * idfValue);
                    // System.out.println(numerador);
                    files.put(
                        fileName,
                        numerador
                    );
                }
            }
        }
        
        HashMap<String, Double> resultSearch = new HashMap<>();
        
        Iterator it = files.entrySet().iterator();
        while (it.hasNext())
        {
            Map.Entry pair = (Map.Entry) it.next();
            
            String fileName = (String) pair.getKey();
            
            double numerador = (double) pair.getValue();
            // sumatorio de todos los pesos de un fichero
            double denominador = longDocumentos.get(fileName);
            
            // raiz cuadrada del denominador
            denominador = Math.sqrt(denominador);
            
            System.out.println(fileName);
            System.out.println(numerador / denominador);
            
            // result search
            resultSearch.put(
                fileName,
                numerador / denominador
            );
        }
        
        return resultSearch;
    }
    
    public String readContent(File file) throws IOException
    {
        String strLine;
        String stringFile = "";
        
        try
        {
            BufferedReader br  = new BufferedReader(new FileReader(file));
            
            while((strLine = br.readLine()) != null)
            {
                stringFile += strLine;
            }
        } catch(Exception e) { }
        
        return stringFile;
    }
    
    public HashMap<String, Double> ranking(HashMap<String, Double> documents)
    {
        List<Map.Entry<String, Double> > list = 
               new LinkedList<Map.Entry<String, Double> >(documents.entrySet()); 
  
        // Sort the list 
        Collections.sort(list, new Comparator<Map.Entry<String, Double> >() { 
            public int compare(Map.Entry<String, Double> o1,  
                               Map.Entry<String, Double> o2) 
            { 
                return (o2.getValue()).compareTo(o1.getValue()); 
            } 
        }); 
          
        // put data from sorted list to hashmap  
        HashMap<String, Double> temp = new LinkedHashMap<String, Double>();
        
        for (Map.Entry<String, Double> aa : list)
        { 
            temp.put(aa.getKey(), aa.getValue()); 
        } 
        
        return temp;
    }
}
