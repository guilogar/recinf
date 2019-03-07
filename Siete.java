import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Siete
{
    public static void main (String[] args)
    {
        String cadena = "asGFasf";
        Pattern pat = Pattern.compile("^\\p{Alpha}{5,10}$");
        Matcher mat = pat.matcher(cadena);
        
        if (mat.matches())
            System.out.println("SI");
        else
            System.out.println("NO");
    }
}
