import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.net.MalformedURLException;
import java.net.URL;

import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Catorce
{
    public static void main (String[] args)
    {
        
        URL url;
        InputStream is = null;
        BufferedReader br;
        String line, web = "";
        
        try {
            url = new URL("https://www.uca.es");
            is = url.openStream();  // throws an IOException
            br = new BufferedReader(new InputStreamReader(is));

            while ((line = br.readLine()) != null)
            {
                web += line + "\n";
            }
        } catch (MalformedURLException mue) {
             mue.printStackTrace();
        } catch (IOException ioe) {
             ioe.printStackTrace();
        } finally {
            try {
                if (is != null) is.close();
            } catch (IOException ioe) {
                // nothing to see here
            }
        }
        
        String cadena = web;
        Pattern pat = Pattern.compile("(<img.*src.*>)");
        Matcher mat = pat.matcher(cadena);
        
        if (mat.find())
        {
            System.out.println("Hay coincidencias.... Estas son: ");
            for (int i = 0; i < mat.groupCount(); i++)
            {
                System.out.println((i + 1) + ") " + mat.group(i));
            }
        } else
            System.out.println("No hay coincidencias");
    }
}
