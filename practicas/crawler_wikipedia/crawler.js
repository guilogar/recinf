let request = require('request');
let cheerio = require('cheerio');
let fs = require('fs');

const { patrones } = require('./util');

let LANGUAGE_BASE = '';
let LIMIT_PAGES   = 0;

let languages = [
    {
        urlBase:   'https://es.wikipedia.org',
        url:       '/wiki/Wikipedia:Portada',
        urlPatron: '/wiki/',
        lang:      'spanish'
    },
    {
        urlBase:   'https://en.wikipedia.org',
        url:       '/wiki/Main_Page',
        urlPatron: '/wiki/',
        lang:      'english'
    },
];

let args = process.argv.slice(2);

args.forEach(function (param, index, array) {
    let option = param.split('=')[0];
    let value  = param.split('=')[1];

    switch(option)
    {
        case '--language': LANGUAGE_BASE = value; break;
        case '--limit':    LIMIT_PAGES   = value; break;
        default: break;
    }
});


let crawlerBase = undefined;

for(let i = 0; i < languages.length; i++)
{
    let l = languages[i];
    if(l.lang == LANGUAGE_BASE)
    {
        crawlerBase = l;
        break;
    }
}

if(crawlerBase === undefined)
{
    console.log('Error: lenguaje no permitido. Tan solo se permite el Español y el Inglés.');
    process.exit();
}

let urlsVisited = [];
let documents  = [];

async function crawlerWikipedia(url = undefined)
{
    if(!urlsVisited.includes(url) && urlsVisited.length < LIMIT_PAGES)
    {
        urlsVisited.push(url);
        //console.log(url);
        request(url, async function(err, resp, body)
        {
            try
            {
                $ = cheerio.load(body);

                let webTitle = $('title').text().replace('/\//', '');
                while(webTitle.indexOf('/') >= 0)
                {
                    webTitle = webTitle.replace('/\//g', '');
                    console.log(webTitle);
                }
                let stream = fs.createWriteStream('pages/' + webTitle + '.html');
                stream.once('open', function(fd)
                {
                    stream.write($.html());
                    stream.end();
                });

                documents.push($);

                let links = $('a');
                $(links).each(async function(i, link)
                {
                    if($(link).attr('lang') === undefined && $(link).attr('hreflang') === undefined)
                    {
                        let u = $(link).attr('href');
                        if(u !== undefined && patrones(u, crawlerBase.urlPatron))
                        {
                            await crawlerWikipedia(crawlerBase.urlBase + u);
                        }
                    }
                });
            } catch(error)
            {
                // console.log(error);
            }
        });
    }
}

crawlerWikipedia(crawlerBase.urlBase + crawlerBase.url);

// Para guardar el html en un fichero....
/*
var fs = require('fs');
var stream = fs.createWriteStream("my_file.txt");
stream.once('open', function(fd) {
  stream.write("My first row\n");
  stream.write("My second row\n");
  stream.end();
});
*/
