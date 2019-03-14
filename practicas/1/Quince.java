import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class Quince
{
    public static void main (String[] args)
    {
        
        String texto = "<a>uno</a><b>dos</b><c>tres</c><d>cuatro</d><e>cinco</e>";
        String[] regexs = {"<[^>]*>([^<]*)</[^>]*>", "<.*>(.*)<.*>", "<.*?>(.*?)<.*?>"};
        
        
        for (int i = 0; i < regexs.length; i++)
        {
            String regex = regexs[i];
            Pattern pat = Pattern.compile(regex);
            Matcher mat = pat.matcher(texto);
            
            System.out.println("Con la expresion regular: " + regex);
            if (mat.find())
            {
                System.out.println("Hay coincidencias.... Estas son: ");
                for (int j = 0; j < mat.groupCount(); j++)
                {
                   System.out.println((j + 1) + ") " + mat.group(j));
                }
            } else
                System.out.println("No hay coincidencias");
        }
    }
}
