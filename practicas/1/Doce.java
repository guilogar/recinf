import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Doce
{
    public static void main (String[] args)
    {
        String[] numeros = {"P 11-1111", "P-11-1111", "P# 11 1111", "P#11-1111", "P 111111"};
        for (int i = 0; i < numeros.length; i++)
        {
            String cadena = numeros[i];
            Pattern pat = Pattern.compile("^P((#\\s?\\d{2}[\\s-]\\d{4})|([\\s-]\\d{2}-\\d{4})|(\\s\\d{6}))$");
            Matcher mat = pat.matcher(cadena);
            
            if (mat.matches())
                System.out.println(cadena + " " + "SI");
            else
                System.out.println(cadena + " " + "NO");
        }
    }
}
