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
import java.io.File;
import java.io.FileWriter;
import java.lang.reflect.Type;
import java.util.Iterator;
import java.util.Map;

/**
 *
 * @author guillermo
 */
public class Main {
    
    public static ArrayList<Filtro> filtros = new ArrayList<>();
    
    public static void index() throws IOException
    {
        Indexacion i = new Indexacion("/home/guillermo/web/corpus/corpus");
        
        // Delete all useless characters
        
        filtros.add(
            new Filtro("[\\.\"',¿\\?¡\\!\\=\\(\\)\\<\\>\\-\\:\\;\\/%\\*\\+\\$`]", " ")
        );
        filtros.add(
            new Filtro("\\d", " ")
        );
        filtros.add(
            new Filtro("\\u0026", " ")
        );
        filtros.add(
            new Filtro("\\\\", " ")
        );
        filtros.add(
            new Filtro("(\r\n|\r|\n)", " ")
        );
        
        
        i.filtrosTF(filtros);
        
        //HashMap<String, String> ficheros = i.getFicheros();
        HashMap<String, HashMap<String, Double>> tf = i.getTf();
        HashMap<String, Double> longDocumentos = i.getLongDocumentos();
        HashMap<String, Double> idf = i.idf();
        
        Gson gson = new GsonBuilder().setPrettyPrinting().create();
        Type gsonType = new TypeToken<HashMap>(){}.getType();
        
        String tfJson = gson.toJson(tf, gsonType);
        String longDocumentosJson = gson.toJson(longDocumentos, gsonType);
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
        
        try (FileWriter file = new FileWriter("/home/guillermo/web/corpus/longDocumentos.json"))
        {
            file.write(longDocumentosJson);
        }
        
        /*
        try (FileOutputStream fos = new FileOutputStream("/home/guillermo/web/corpus/longDocumentos.ser"))
        {
            ObjectOutputStream oos = new ObjectOutputStream(fos);
            oos.writeObject(longDocumentosJson);
            oos.close();
            fos.close();
        }
        */
    }
    
    public static void search(String[] args) throws IOException
    {
        System.out.println("Busqueda");
        Busqueda b = new Busqueda("cancer");
        b.filtros(filtros);
        
        HashMap<String, Double> bestResults = b.ranking(b.search());
        
        System.out.println("===============================");
        System.out.println("===============================");
        System.out.println("===============================");
        System.out.println("===============================");
        System.out.println("===============================");
        System.out.println("===============================");
        Iterator it = bestResults.entrySet().iterator();
        for(int i = 0; i < 10 && it.hasNext(); i++)
        {
            Map.Entry pair = (Map.Entry) it.next();
            
            String fileName = (String) pair.getKey();
            Double value = (Double) pair.getValue();
            
            System.out.println(fileName);
            System.out.println(value);
            System.out.println(b.readContent(new File(fileName)));
        }
    }
    
    public static void main(String[] args) throws IOException
    {
        index();
        search(args);
    }
}
