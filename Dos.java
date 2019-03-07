import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Dos
{
    public static void main (String[] args)
    {
        String cadena = "bcperiquito";
        Pattern pat = Pattern.compile("^(abc|Abc).*$");
        Matcher mat = pat.matcher(cadena);
        
        if (mat.matches())
            System.out.println("SI");
        else
            System.out.println("NO");
        
    }
}
