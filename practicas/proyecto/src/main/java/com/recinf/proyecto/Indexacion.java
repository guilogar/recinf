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
    private HashMap<String, HashMap<String, Double>> tf;
    private HashMap<String, Double> idf;
    private HashMap<String, HashMap<String, Double>> invertTf;
    
    public Indexacion(String directorio) throws IOException
    {
        this.ficheros = new HashMap<>();
        this.tf       = new HashMap<>();
        this.idf      = new HashMap<>();
        this.invertTf = new HashMap<>();
        
        final File folder = new File(directorio);
        
        for (final File fileEntry : folder.listFiles())
        {
            if (fileEntry.isFile())
            {
                this.ficheros.put(
                    fileEntry.getAbsolutePath(),
                    this.readContent(fileEntry).toLowerCase()
                );
            }
        }
    }
    
    public HashMap<String, String> getFicheros()
    {
        return this.ficheros;
    }
    
    public HashMap<String, String> filtrosCaracteres(ArrayList<Filtro> filtros)
    {
        System.out.println("empieza el filtro de caracteres");
        Iterator it = this.ficheros.entrySet().iterator();
        
        while (it.hasNext())
        {
            Map.Entry pair = (Map.Entry) it.next();
            
            String text = (String) pair.getValue();
            
            for(int i = 0; i < filtros.size(); i++)
            {
                Filtro f = filtros.get(i);
                text = f.aplicarFiltro(text);
            }
            this.ficheros.replace((String) pair.getKey(), text);
            // break;
        }
        return this.ficheros;
    }
    
    public HashMap<String, String> stopWord(ArrayList<Filtro> filtros)
    {
        System.out.println("empieza el stopword");
        Iterator it = this.ficheros.entrySet().iterator();
        
        while (it.hasNext())
        {
            Map.Entry pair = (Map.Entry) it.next();
            
            String text = (String) pair.getValue();
            
            for(int i = 0; i < filtros.size(); i++)
            {
                Filtro f = filtros.get(i);
                text = f.aplicarFiltro(text);
            }
            
            this.ficheros.replace((String) pair.getKey(), text);
            // break;
        }
        return this.ficheros;
    }
    
    public HashMap<String, String> stemming()
    {
        System.out.println("empieza el stemming");
        SnowballStemmer stemmer = new porterStemmer();

        Iterator it = this.ficheros.entrySet().iterator();
        
        while (it.hasNext())
        {
            Map.Entry pair = (Map.Entry) it.next();
            
            String text = (String) pair.getValue();
            
            String[] words = text.split(" ");
            
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
            
            this.ficheros.put((String) pair.getKey(), text);
            // break;
        }
        
        return this.ficheros;
    }
    
    public HashMap<String, HashMap<String, Double>> tf()
    {
        System.out.println("empieza el tf");
        Iterator it = this.ficheros.entrySet().iterator();
        
        while (it.hasNext())
        {
            
            Map.Entry pair = (Map.Entry) it.next();
            
            String fichero = (String) pair.getKey();
            String text = (String) pair.getValue();
            String[] words = text.split(" ");
            
            String textWithoutRepeats = new LinkedHashSet<String>(
                Arrays.asList(text.split("\\s+")) ).toString().replaceAll("[\\[\\],]", ""
            );
            
            String[] wordsWithoutRepeats = textWithoutRepeats.split(" ");
            
            for(int i = 0; i < wordsWithoutRepeats.length; i++)
            {
                // delete spaces...
                wordsWithoutRepeats[i] = wordsWithoutRepeats[i].trim();
                
                HashMap<String, Double> mapWord = this.tf.get(wordsWithoutRepeats[i]);
                
                // count the ocurrencies of word in a file
                int count = this.countOccurrencies(wordsWithoutRepeats[i], text);
                if(count == 0) count = 1;
                
                // Calculate the tf of term in this file...
                // double tfValue = 1 + Math.abs(Math.log10((double) count / words.length));
                double tfValue = 1 + Math.abs(Math.log10((double) count)) / Math.log10(2);
                
                /*
                // Debug for see if any word have 0 ocurrences...
                if(Double.isInfinite(tfValue))
                {
                    System.out.println(count + " => " + words.length);
                }
                */
                
                if(mapWord == null)
                {
                    mapWord = new HashMap<String, Double>();
                    
                    mapWord.put(fichero, tfValue);
                    
                    this.tf.put(
                        wordsWithoutRepeats[i],
                        mapWord
                    );
                } else
                {
                    mapWord.put(fichero, tfValue);
                }
                
                HashMap<String, Double> mapFile = this.invertTf.get(fichero);
                
                if(mapFile == null)
                {
                    mapFile = new HashMap<String, Double>();
                    mapFile.put(
                        wordsWithoutRepeats[i],
                        tfValue
                    );
                    
                    this.invertTf.put(
                        fichero, mapFile
                    );
                } else
                {
                    mapFile.put(
                        wordsWithoutRepeats[i],
                        tfValue
                    );
                }
                
                // System.out.println(wordsWithoutRepeats[i] + " => " + fichero + " = " + tfValue);
            }
            // break;
        }
        
        return this.tf;
    }
    
    public HashMap<String, HashMap<String, Double>> getInvertTf()
    {
        return this.invertTf;
    }
    
    public HashMap<String, Double> idf()
    {
        System.out.println("empieza el idf");
        int n = this.ficheros.size();
        Iterator it = this.tf.entrySet().iterator();
        
        while (it.hasNext())
        {
            Map.Entry pair = (Map.Entry) it.next();
            
            String term = (String) pair.getKey();
            HashMap<String, Double> hash = (HashMap) pair.getValue();
            int ni = hash.size();
            
            this.idf.put(
                term,
                Math.log(n / ni)
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
    
    private int countOccurrencies(String word, String line)
    {
        String[] words = line.split(" ");
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