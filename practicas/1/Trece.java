import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Trece
{
    public static void main (String[] args)
    {
        String[] palabras = {"vi@gra", "v1agra", "v1@gra", "v!@gr@"};
        for (int i = 0; i < palabras.length; i++)
        {
            String cadena = palabras[i];
            Pattern pat = Pattern.compile("^\\w*.*(\\d*|@+\\!*|\\!*@+).*\\w*$");
            Matcher mat = pat.matcher(cadena);
            
            if (mat.matches())
                System.out.println(cadena + " " + "SI");
            else
                System.out.println(cadena + " " + "NO");
        }
    }
}
