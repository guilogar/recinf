/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
package com.recinf.proyecto;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.HashMap;
import java.util.Iterator;
import java.util.LinkedHashSet;
import java.util.Map;
import org.tartarus.snowball.SnowballStemmer;
import org.tartarus.snowball.ext.porterStemmer;

/**
 *
 * @author guillermo
 */
public class Indexacion {
    
    private HashMap<String, String> ficheros;
    private HashMap<String, String[]> palabras;
    private HashMap<String, HashMap<String, Double>> tf;
    private HashMap<String, Double> idf;
    private HashMap<String, Double> longDocumentos;
    
    public Indexacion(String directorio) throws IOException
    {
        this.palabras = new HashMap<>();
        this.ficheros = new HashMap<>();
        this.tf       = new HashMap<>();
        this.idf      = new HashMap<>();
        this.longDocumentos = new HashMap<>();
        
        final File folder = new File(directorio);
        
        for (final File fileEntry : folder.listFiles())
        {
            if (fileEntry.isFile())
            {
                String text = this.readContent(fileEntry).toLowerCase();
                String fileName = fileEntry.getAbsolutePath();
                this.ficheros.put(
                    fileName,
                    text
                );
            }
        }
    }
    
    public HashMap<String, String> getFicheros()
    {
        return this.ficheros;
    }
    
    public HashMap<String, HashMap<String, Double>> filtrosTF(ArrayList<Filtro> filtros)
    {
        System.out.println("empieza el filtro de caracteres, stopwords y stemming");
        Iterator it = this.ficheros.entrySet().iterator();
        
        SnowballStemmer stemmer = new porterStemmer();
        while (it.hasNext())
        {
            Map.Entry pair = (Map.Entry) it.next();
            
            String fileName = (String) pair.getKey();
            String text = (String) pair.getValue();
            
            // characters and stopwords...
            for(int i = 0; i < filtros.size(); i++)
            {
                Filtro f = filtros.get(i);
                text = f.aplicarFiltro(text);
            }
            
            String[] words = text.split(" ");
            
            String[] allStopWords = StopWords.getAllStopWords();
            // allStopWords = new String[0];
            
            text = "";
            for(int i = 0; i < words.length; i++)
            {
                words[i] = words[i].trim();
                for (String stopWord : allStopWords)
                {
                    if(words[i].equalsIgnoreCase(stopWord))
                    {
                        words[i] = "";
                    }
                }
                text += " " + words[i] + " ";
            }
            
            text = text.trim();
            
            words = text.split(" ");
            
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
            
            text = String.join(" ", words);
            
            String textWithoutRepeats = new LinkedHashSet<String>(
                Arrays.asList(text.split("\\s+")) ).toString().replaceAll("[\\[\\],]", ""
            );
            
            String[] wordsWithoutRepeats = textWithoutRepeats.split(" ");
            
            double pesoDocumento = 0;
            
            for(int i = 0; i < wordsWithoutRepeats.length; i++)
            {
                if(wordsWithoutRepeats[i].length() == 0) continue;
                
                // delete spaces...
                wordsWithoutRepeats[i] = wordsWithoutRepeats[i].trim();
                
                HashMap<String, Double> mapWord = this.tf.get(wordsWithoutRepeats[i]);
                
                // count the ocurrencies of word in a file
                int count = this.countOccurrencies(wordsWithoutRepeats[i], words);
                if(count == 0) count = 1;
                
                // Calculate the tf of term in this file...
                // double tfValue = 1 + Math.abs(Math.log10((double) count / words.length));
                double tfValue = 1 + Math.log10((double) count) / Math.log10(2);
                
                /*
                // Debug for see if any word have 0 ocurrences...
                if(Double.isInfinite(tfValue))
                {
                    System.out.println(count + " => " + words.length);
                }
                */
                
                if(mapWord == null)
                {
                    mapWord = new HashMap<>();
                    
                    mapWord.put(fileName, tfValue);
                    
                    this.tf.put(
                        wordsWithoutRepeats[i],
                        mapWord
                    );
                } else
                {
                    mapWord.put(fileName, tfValue);
                }
                
                pesoDocumento += tfValue;
            }
            
            this.longDocumentos.put(
                fileName,
                pesoDocumento
            );
        }
        
        return this.tf;
    }
    
    public HashMap<String, HashMap<String, Double>> getTf()
    {
        return this.tf;
    }
    
    public HashMap<String, Double> getLongDocumentos()
    {
        return this.longDocumentos;
    }
    
    public HashMap<String, Double> idf()
    {
        System.out.println("empieza el idf");
        double n = this.ficheros.size();
        Iterator it = this.tf.entrySet().iterator();
        
        while (it.hasNext())
        {
            Map.Entry pair = (Map.Entry) it.next();
            
            String term = (String) pair.getKey();
            HashMap<String, Double> hash = (HashMap) pair.getValue();
            double ni = hash.size();
            
            this.idf.put(
                term,
                Math.log10(n / ni) / Math.log10(2)
            );
        }
        
        return this.idf;
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
    
    private int countOccurrencies(String word, String[] words)
    {
        int count = 0;
        
        for(int i = 0; i < words.length; i++)
        {
            if(word.trim().equalsIgnoreCase(words[i].trim()))
            {
                ++count;
            }
        }
        
        return count;
    }
}