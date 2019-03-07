import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Diez
{
    public static void main (String[] args)
    {
        String[] ips = {"192.168.1.1", "200.36.127.40", "10.128.1.253"};
        for (int i = 0; i < ips.length; i++)
        {
            String cadena = ips[i];
            Pattern pat = Pattern.compile("^\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}$");
            Matcher mat = pat.matcher(cadena);
            
            if (mat.matches())
                System.out.println(cadena + " " + "SI");
            else
                System.out.println(cadena + " " + "NO");
        }
    }
}
