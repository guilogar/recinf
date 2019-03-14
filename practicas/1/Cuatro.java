import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Cuatro
{
    public static void main (String[] args)
    {
        String cadena = "abcperiquito";
        Pattern pat = Pattern.compile("^.*\\D$");
        Matcher mat = pat.matcher(cadena);
        
        if (mat.matches())
            System.out.println("SI");
        else
            System.out.println("NO");
    }
}
