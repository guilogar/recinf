import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;

public class Dieciocho {
    
    public static void main (String[] args) throws Exception
    {
        File file = new File("EjercicioExpresiones.txt");
        BufferedReader br = new BufferedReader(new FileReader(file));
        String texto = "", st;
        
        while ((st = br.readLine()) != null) texto += st;
        
        String[] simbolos = {":", ",", "\\.", ";", "\\?", "¿", "¡", "!", "\\.\\.\\.", "\"", "'", "<<", ">>"};
        
        for (int i = 0; i < simbolos.length; i++)
        {
            texto = texto.replaceAll(simbolos[i], "");
        }
        
        String[] vocalesAcentuadas = {"á", "é", "í", "ó", "ú"};
        String[] vocalesNoAcentuadas = {"a", "e", "i", "o", "u"};
        
        for (int i = 0; i < vocalesAcentuadas.length; i++)
        {
            texto = texto.replaceAll(vocalesAcentuadas[i], vocalesNoAcentuadas[i]);
        }
        
        texto = texto.replaceAll(" \\d+ ", " ");
        
        System.out.println(texto);
    }
}
