var activeClass  = "active";
var currentClass = "current";
var counter      = 0;

$(document).ready(function(){
    addActiveStatus();
    checkActiveItem();
    addClickToNavItems();
});

/**
 * Klick-Funcktion für alle Einträge in der Navigation,
 * welche ein Unter-Menü haben
 */
function addClickToNavItems()
{
    $("nav a.has-subitem").on("click", function(event){
        event.preventDefault();
        $(this).next("ul").toggleClass(activeClass);
        $(this).toggleClass(activeClass);
    });
}

/**
 * Back-Traversing für alle Items,
 * welche im Pfad gerade aktiv sind
 */
function addActiveStatus()
{
    let currUrl = $(location).attr("href");
    
    $("nav li").each(function(){
        if ( $(this).find("a").attr("href") == currUrl ) {
            $("nav li a").removeClass(activeClass).removeClass(currentClass);
            $(this).find("a").addClass(activeClass).addClass(currentClass);
        }
        
        if ( $(this).find("a").hasClass(currentClass) ) {
            $(this).children("ul").first().addClass(activeClass);
            $(this).children("a").first().addClass(activeClass);
            $(this).addClass(activeClass);

            counter++;
        }
    });
}

/**
 * Wenn keine Aktiv-Treffer, dann Index auswählen
 */
function checkActiveItem()
{
    if ( counter == 0 ) {
        $("#index").addClass(activeClass).addClass(currentClass);
        $("#index").parent("li").addClass(activeClass);
    }
}