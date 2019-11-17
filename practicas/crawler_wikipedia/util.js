module.exports.patrones = function patrones(nameUrl, patternUrl)
{
    let condition = true;
    condition = condition                                        &&
                nameUrl.indexOf(patternUrl)                 >= 0 &&
                nameUrl.indexOf(".exe")                      < 0 &&
                nameUrl.indexOf(".pdf")                      < 0 &&
                nameUrl.indexOf(".doc")                      < 0 &&
                nameUrl.indexOf(".rtf")                      < 0 &&
                nameUrl.indexOf(".ppt")                      < 0 &&
                nameUrl.indexOf(".zip")                      < 0 &&
                nameUrl.indexOf(".rar")                      < 0 &&
                nameUrl.indexOf(".xl")                       < 0;
    return condition;
}