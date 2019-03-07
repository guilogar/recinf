import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Nueve
{
    public static void main (String[] args)
    {
        String[] fechas = {"25/10/83", "4/11/56", "30/6/71", "4/3/85"};
        for (int i = 0; i < fechas.length; i++)
        {
            String cadena = fechas[i];
            Pattern pat = Pattern.compile("^\\d{1,2}/\\d{1,2}/\\d{2,4}$");
            Matcher mat = pat.matcher(cadena);
            
            if (mat.matches())
                System.out.println(cadena + " " + "SI");
            else
                System.out.println(cadena + " " + "NO");
        }
    }
}
