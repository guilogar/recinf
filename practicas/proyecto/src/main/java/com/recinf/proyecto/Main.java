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
    public static String dirCorpus = "/home/guillermo/web/corpus/corpus";
    public static String dirTF = "/home/guillermo/web/corpus/tf.json";
    public static String dirIDF = "/home/guillermo/web/corpus/idf.json";
    public static String dirLongDocumentos = "/home/guillermo/web/corpus/longDocumentos.json";
    
    public static void index() throws IOException
    {
        Indexacion i = new Indexacion(dirCorpus);
        
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
        
        try (FileWriter file = new FileWriter(dirTF))
        {
            file.write(tfJson);
        }
        
        try (FileWriter file = new FileWriter(dirIDF))
        {
            file.write(idfJson);
        }
        
        try (FileWriter file = new FileWriter(dirLongDocumentos))
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
    
    public static void search(String s, int maxResults) throws IOException
    {
        System.out.println("Empieza la búsqueda");
        Busqueda b = new Busqueda(s);
        
        if(filtros.isEmpty())
        {
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
        }
        
        b.filtros(filtros);
        
        HashMap<String, Double> bestResults = b.ranking(
            b.search(dirTF, dirIDF, dirLongDocumentos)
        );
        
        System.out.println("===============================");
        Iterator it = bestResults.entrySet().iterator();
        
        for(int i = 0; i < maxResults && it.hasNext(); i++)
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
        // Comment to mode production with .jar export
        args = new String[3];
        
        // Comment to search and uncomment to index
        args[0] = "1";
        
        // Comment to index and uncomment to search
        args[0] = "2";
        args[1] = "jhghjg jg ghjgjh";
//        args[1] = "bibliometrics";
        args[2] = "10";

        try
        {
            int action = Integer.parseInt(args[0]);
            
            switch(action)
            {
                case 1: index(); break;
                case 2: 
                {
                    String s = args[1];
                    int maxResults = Integer.parseInt(args[2]);
                    search(s, maxResults);
                } break;
                
                default: System.out.println("Bad option. Try again with option 1 or 2"); break;
            }
        } catch(Exception e) { }
    }
}
