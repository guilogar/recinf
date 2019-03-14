import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Tres
{
    public static void main (String[] args)
    {
        String cadena = "8abcperiquito";
        Pattern pat = Pattern.compile("^\\D.*$");
        Matcher mat = pat.matcher(cadena);
        
        if (mat.matches())
            System.out.println("SI");
        else
            System.out.println("NO");
    }
}
