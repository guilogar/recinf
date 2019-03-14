import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Ocho
{
    public static void main (String[] args)
    {
        String cadena = "www.marca.es";
        Pattern pat = Pattern.compile("^(www)\\..+\\.(es)$");
        Matcher mat = pat.matcher(cadena);
        
        if (mat.matches())
            System.out.println("SI");
        else
            System.out.println("NO");
    }
}
