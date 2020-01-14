/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.recinf.proyecto;

import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.HashSet;
import java.util.Iterator;
import java.util.Map;
import org.tartarus.snowball.SnowballStemmer;
import org.tartarus.snowball.ext.porterStemmer;

/**
 *
 * @author guillermo
 */
public class Busqueda {
    
    private String search;
    
    public Busqueda(String search)
    {
        this.search = search;
    }
    
    public String filtrosCaracteres(ArrayList<Filtro> filtros)
    {
        System.out.println("empieza el filtro de caracteres");
        
        for(int i = 0; i < filtros.size(); i++)
        {
            Filtro f = filtros.get(i);
            this.search = f.aplicarFiltro(this.search);
        }
        
        return this.search;
    }
    
    public String stopWord(ArrayList<Filtro> filtros)
    {
        System.out.println("empieza el stopword");
        
        for(int i = 0; i < filtros.size(); i++)
        {
            Filtro f = filtros.get(i);
            this.search = f.aplicarFiltro(this.search);
        }
        
        return this.search;
    }
    
    public String stemming()
    {
        System.out.println("empieza el stemming");
        
        SnowballStemmer stemmer = new porterStemmer();

        String[] words = this.search.split(" ");
            
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
        
        this.search = String.join(" ", words);
        
        return this.search;
    }
    
    public HashMap<String, Double> search() throws IOException
    {
        String tfJsonString = this.readContent(
            new File("/home/guillermo/web/corpus/tf.json")
        );
        String idfJsonString = this.readContent(
            new File("/home/guillermo/web/corpus/idf.json")
        );
        String invertTfJsonString = this.readContent(
            new File("/home/guillermo/web/corpus/invertTf.json")
        );
        
        HashMap<String, HashMap<String, Double>> tf = new Gson().fromJson(
            tfJsonString, new TypeToken<HashMap<String, HashMap<String, Double>>>() {}.getType()
        );
        
        HashMap<String, Double> idf = new Gson().fromJson(
            idfJsonString, new TypeToken<HashMap<String, Double>>() {}.getType()
        );
        
        HashMap<String, HashMap<String, Double>> invertTf = new Gson().fromJson(
            invertTfJsonString, new TypeToken<HashMap<String, HashMap<String, Double>>>() {}.getType()
        );
        
        HashSet<String> files = new HashSet<>();
        
        String[] words = this.search.split(" ");
        
        for(int i = 0; i < words.length; i++)
        {
            Iterator it = tf.get(words[i]).entrySet().iterator();
            while (it.hasNext())
            {
                Map.Entry pair = (Map.Entry) it.next();

                files.add((String) pair.getKey());
            }
        }
        
        HashMap<String, Double> resultSearch = new HashMap<>();
        
        Iterator it = files.iterator();
        while (it.hasNext())
        {
            String fileName = (String) it.next();
            
            // sumatorio de todos los terminos a buscar
            double numerador = 0;
            
            for(int i = 0; i < words.length; i++)
            {
                HashMap<String, Double> ficheros = tf.get(words[i]);
                double valueTermFile = ficheros.get(fileName);
                double idfValueTerm = idf.get(words[i]);
                
                numerador += idfValueTerm * valueTermFile;
            }
            
            // sumatorio de todos los pesos de un fichero
            double denominador = 0;
            
            HashMap<String, Double> terms = invertTf.get(fileName);
            Iterator itTerms = terms.entrySet().iterator();
            
            while (itTerms.hasNext())
            {
                Map.Entry pairTerms = (Map.Entry) itTerms.next();
                double valueTermInFile = (double) pairTerms.getValue();
                
                denominador += Math.pow(valueTermInFile, 2);
            }
            
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
    
    private String readContent(File file) throws IOException
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
        
        return null;
    }
}
