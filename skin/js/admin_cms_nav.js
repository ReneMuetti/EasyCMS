let cmsNavPopup;
let cmsNavContainer;
let currentNavEntry;

let prefix = "nav-item-";
let disableClass = "item-disable";
let maxElementCount = 2000;

$(document).ready(function(){
    cmsNavPopup = $("#cms-nav-popup");
    cmsNavContainer = $("#nav-template");

    applySortableToElement(cmsNavContainer);

    resetPopupFormElements();

    // create default-home-element
    if ( navigationEmpty == true ) {
        currentNavEntry = {};
        currentNavEntry.id          = prefix + "1";
        currentNavEntry.itemId      = 0;
        currentNavEntry.title       = navigationDefault;
        currentNavEntry.position    = 1;
        currentNavEntry.parent      = "";
        currentNavEntry.enable      = false;
        currentNavEntry.type        = 0;
        currentNavEntry.cms         = -1;
        currentNavEntry.cmsTitle    = "";
        currentNavEntry.url         = "";
        currentNavEntry.insertInto  = "";
        currentNavEntry.isHome      = "true";

        createNewNavigationElement(currentNavEntry);
    }
});

function applySortableToElement(newElement)
{
    newElement.sortable({
                  "placeholder"     : "ui-state-highlight",
                  "connectWith"     : "#nav-template, #nav-template ul",
                  "items"           : "> li",
                  "dropOnEmpty"     : true,
                  "revert"          : true,

                  "start": function(event, ui) {
                               ui.placeholder.height( ui.item.height() );
                           }
              })
              .disableSelection();
}

function resetPopupFormElements()
{
    $("#nav-id").val("");
    $("#nav-parent-id").val("");
    $("#nav-item-id").val("0");
    $("#nav-position").val("0");
    $("#nav-status").prop("checked", true);
    $("#nav-title, #nav-destination-external").val("");
    $("#nav-type, #nav-destination-cms").prop("selectedIndex", 0).change();
}

function switchStatus(itemNumber)
{
    let elementId = "#" + prefix + itemNumber;

    if ( $(elementId).length ) {
         if ( $(elementId).attr("data-enable") == "false" ) {
            $(elementId).attr("data-enable", "true")
                        .removeClass(disableClass);
         }
         else {
            $(elementId).attr("data-enable", "false")
                        .addClass(disableClass);
         }
    }
    else {
        alert( message_element_not_found + " (" + elementId + ")" );
    }
}

function editItem(itemNumber)
{
    let elementId = "#" + prefix + itemNumber;

    if ( $(elementId).length ) {
        let enableStatus = ($(elementId).attr("data-enable") == "true");

        $("#nav-id").val( $(elementId).attr("id") );
        $("#nav-item-id").val( itemNumber );

        $("#nav-title").val( $(elementId).attr("data-title") );
        $("#nav-position").val( $(elementId).attr("data-position") );
        $("#nav-parent-id").val( $(elementId).attr("data-parent") );
        $("#nav-status").prop("checked", enableStatus);
        $("#nav-type").prop("selectedIndex", $(elementId).attr("data-type")).change();
        $("#nav-destination-cms").prop("selectedIndex", $(elementId).attr("data-cms")).change();
        $("#nav-destination-external").val( $(elementId).attr("data-url") );

        showPopup();
    }
    else {
        alert( message_element_not_found + " (" + elementId + ")" );
    }
}

function deleteItem(itemNumber)
{
    let elementId = "#" + prefix + itemNumber;

    if ( $(elementId).length ) {
        $(elementId).remove();
    }
    else {
        alert( message_element_not_found + " (" + elementId + ")" );
    }
}

function secureString(text)
{
    return text.replace(/[^a-zA-Z0-9\s]/g, "")  // remove all non-alphanumeric characters
               .replace(/<|>/g, "")             // remove single characters ("<" and ">")
               .replace(/<!--|--!?>/g, "")      // remove all HTML comment start and end tags
               .replace(/[<>"'&]/g, "")         // remove potentially dangerous characters
               .replace(/\.\.\//g, "")          // remove path-elements
               .replace(/<\/?[^>]+>/gi, "");    // remove HTML-Tags
}

function createNewNavigationElement(navData)
{
    let styleClass, elementDescription;

    if ( parseInt( navData.itemId ) <= 0 ) {
        navData.itemId = getNextFreeNumberForNewNavElement();
    }

    if ( (navData.id == "") || (navData.id == "-1") ) {
        navData.id = prefix + navData.itemId;
    }

    if ( navData.enable == true ) {
        styleClass = "";
    }
    else {
        styleClass = disableClass;
    }

    if ( navData.type == 0 ) {
        // cms-Page-Title
        elementDescription = navData.cmsTitle;
    }
    else {
        // external URL
        elementDescription = navData.url;
    }

    navData.title = secureString(navData.title);

    if ( $("#" + navData.id).length ) {
        // update existing element
        $("#" + navData.id).attr("data-title" , navData.title)
                           .attr("data-enable", navData.enable)
                           .attr("data-type"  , navData.type)
                           .attr("data-cms"   , navData.cms)
                           .attr("data-url"   , navData.url);
        $("#" + navData.id).removeClass().addClass(styleClass);
        $("#" + navData.id + " span:first-child").html(navData.title);
        $("#" + navData.id + " span:nth-child(2)").html(elementDescription);
    }
    else {
        // create new element
        let newEmptySubNav = $("<ul></ul>", {
                                 "id"                 : "sub-" + navData.id,
                                 "data-parent-element": navData.id,
                             });

        let newItem = $("<li></li>", {
                          "id"   : navData.id,
                          "html" : "<span>" + navData.title + "</span>" +
                                   "<span title=\"" + elementDescription + "\">" + elementDescription + "</span>" +
                                   "<span>" +
                                       "<a href=\"javascript:void(0)\" class=\"nav-item-change-state\" onclick=\"switchStatus(" + navData.itemId + ")\"></a>" +
                                       "<a href=\"javascript:void(0)\" class=\"nav-item-edit\" onclick=\"editItem(" + navData.itemId + ")\"></a>" +
                                       "<a href=\"javascript:void(0)\" class=\"nav-item-delete\" onclick=\"deleteItem(" + navData.itemId + ")\"></a>" +
                                   "</span>",
                          "class": styleClass,

                          "data-item-id" : navData.itemId,
                          "data-title"   : navData.title,
                          "data-position": navData.position,
                          "data-parent"  : navData.parent,
                          "data-enable"  : navData.enable,
                          "data-type"    : navData.type,
                          "data-cms"     : navData.cms,
                          "data-url"     : navData.url,
                      });

        if ( navData.isHome == false ) {
            newItem.append(newEmptySubNav);
        }

        if ( navData.insertInto == "" ) {
            cmsNavContainer.append(newItem);
        }
        else {
            if ( $("#" + navData.insertInto).length ) {
                $("#" + navData.insertInto).append(newItem);
            }
            else {
                cmsNavContainer.append(newItem);
            }
        }

        applySortableToElement(newEmptySubNav);
    }

    // reset Pop-Element
    resetPopupFormElements();
}

function showPopup()
{
    cmsNavPopup.addClass("active");
}

function hidePopup()
{
    cmsNavPopup.removeClass("active");
}

function changeCmsNavType(element)
{
    let newSelect = $(element).val();

    $("#nav_type_0, #nav_type_1").removeClass("active");
    $("#nav_type_" + newSelect).addClass("active");
}

function saveNavEntry()
{
    if ( $("#nav-title").val().length === 0 ) {
        // Title is empty
        alert( message_no_title_exits );
        return false;
    }

    if ( $("#nav-type").prop("selectedIndex") == 0 ) {
        // reset external URL
        $("#nav-destination-external").val("");

        // check, if CMS-Page selected
        if ( $("#nav-destination-cms").prop("selectedIndex") <= 0 ) {
            // no valid CMS-Page selected
            alert( message_no_cms_page_selected );
            return false;
        }
    }
    else {
        // reset internal Page-Selector
        $("#nav-destination-cms").prop("selectedIndex", 0).change();

        // check, if external link
        if ( $("#nav-destination-external").val().length === 0 ) {
            // Input-Field is empty
            alert( message_no_external_link );
            return false;
        }
    }

    // insert new Element
    currentNavEntry = {};

    currentNavEntry.id          = $("#nav-id").val();
    currentNavEntry.itemId      = $("#nav-item-id").val();
    currentNavEntry.title       = $("#nav-title").val();
    currentNavEntry.position    = $("#nav-position").val();
    currentNavEntry.parent      = $("#nav-parent-id").val();
    currentNavEntry.enable      = $("#nav-status").prop("checked");
    currentNavEntry.type        = $("#nav-type").prop("selectedIndex");
    currentNavEntry.cms         = $("#nav-destination-cms").prop("selectedIndex");
    currentNavEntry.cmsTitle    = $("#nav-destination-cms option:selected").text();
    currentNavEntry.url         = $("#nav-destination-external").val();
    currentNavEntry.insertInto  = "";
    currentNavEntry.isHome      = false;

    createNewNavigationElement(currentNavEntry);
    hidePopup();

    /*

    $.ajax({
        "url"   : baseurl + "ajax_navigation.php",
        "method": "POST",
        "data"  : {
                      "nav-id"      : $("#nav-id").val(),
                      "nav-parent"  : $("#nav-parent-id").val(),
                      "nav-position": $("#nav-position").val(),
                      "nav-title"   : $("#nav-title").val(),
                      "nav-enable"  : $("#nav-status").prop("checked"),
                      "nav-type"    : $("#nav-type").prop("selectedIndex"),
                      "nav-cms"     : $("#nav-destination-cms").prop("selectedIndex"),
                      "nav-url"     : $("#nav-destination-external").val(),
                  },
        "beforeSend": function() {
                      }
    })
    .done(function(result){
        let ajaxReturn = $.parseJSON(result);

        if ( ajaxReturn.error == true ) {
            alert( ajaxReturn.message );
        }
        else {
            // insert new Element

            hidePopup();
        }
    })
    .fail(function(jqXHR, textStatus){
        alert( ajax_error + textStatus );
    });

    */
}

function getNextFreeNumberForNewNavElement()
{
    let counter = 1;
    let checkElementId;

    while( counter <= maxElementCount ) {
        checkElementId = prefix + counter;
        if( $("#" + checkElementId).length ) {
            counter++;
        }
        else {
            return counter;
            break;
        }
    }
}