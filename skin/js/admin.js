$(document).ready(function(){
    openAdminNavigation();

    setInterval(runClock, 1000);
});

function openAdminNavigation()
{
    let currUrl = $(location).attr("href");

    if( currUrl.indexOf("&") > 0 ) {
        currUrl = currUrl.substring(0, currUrl.indexOf("&"));
    }

    $("nav li").each( function(){
        let elem = $(this).find("a");
        let link = $(this).find("a").attr("href");
        if(link == currUrl) {
            elem.closest("ul").toggleClass(activeClass);
            elem.closest("ul").prev("a").toggleClass(activeClass);

            if ( !elem.hasClass(activeClass) ) {
                elem.addClass(activeClass);
            }
            if ( !elem.hasClass("current") ) {
                elem.addClass("current");
            }

            return false;
        }
    });
}

function runClock()
{
    if ( $("#clock").length ) {
        let newTime = new Date().toLocaleTimeString();
        $("#clock").html(newTime);
    }
}

function _fixedSpecialCharacters(strToFixed)
{
    return strToFixed.replace("&uuml;" , "ü")
                     .replace("&Uuml;" , "Ü")
                     .replace("&auml;" , "ä")
                     .replace("&Auml;" , "A")
                     .replace("&ouml;" , "ö")
                     .replace("&Ouml;" , "Ö")
                     .replace("&szlig;", "ß");
}

JSON.stringify = JSON.stringify || function (obj)
{
    var t = typeof (obj);
    if (t != "object" || obj === null)
   {
        // simple data type
        if (t == "string")
       {
            obj = '"'+obj+'"';
       }
        return String(obj);
   }
    else
   {
        // recurse array or object
        var n, v, json = [], arr = (obj && obj.constructor == Array);
        for (n in obj)
       {
            v = obj[n];
            t = typeof(v);

            if (t == "string")
           {
                v = '"'+v+'"';
           }
            else if (t == "object" && v !== null)
           {
                v = JSON.stringify(v);
           }
            json.push((arr ? "" : '"' + n + '":') + String(v));
       }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
   }
};
