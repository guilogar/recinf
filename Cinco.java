import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Cinco
{
    public static void main (String[] args)
    {
        String cadena = "la la la";
        Pattern pat = Pattern.compile("^(l*|a*)$");
        Matcher mat = pat.matcher(cadena);
        
        if (mat.matches())
            System.out.println("SI");
        else
            System.out.println("NO");
    }
}
