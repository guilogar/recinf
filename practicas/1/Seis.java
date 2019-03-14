import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Seis
{
    public static void main (String[] args)
    {
        String cadena = "dafasdfas 22 asdfasf";
        Pattern pat = Pattern.compile("^.*2[^6].*$");
        Matcher mat = pat.matcher(cadena);
        
        if (mat.matches())
            System.out.println("SI");
        else
            System.out.println("NO");
    }
}
