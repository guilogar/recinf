import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Once
{
    public static void main (String[] args)
    {
        String[] tlfs = {"+34 95 6030466", "+34 9 5 60 3 0466"};
        for (int i = 0; i < tlfs.length; i++)
        {
            String cadena = tlfs[i];
            Pattern pat = Pattern.compile("^\\+(34)\\s\\d{2}\\s\\d{7}$");
            Matcher mat = pat.matcher(cadena);
            
            if (mat.matches())
                System.out.println(cadena + " " + "SI");
            else
                System.out.println(cadena + " " + "NO");
        }
    }
}
