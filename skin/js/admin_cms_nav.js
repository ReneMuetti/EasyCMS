let cmsNavPopup;
let cmsNavContainer;
let currentNavEntry;

let navContainer = "nav-template";
let prefix = "nav-item-";
let prefixSub = "sub-";
let defaultClass = "ui-sortable-handle";
let disableClass = "item-disable";
let maxElementCount = 2000;

$(document).ready(function(){
    cmsNavPopup = $("#cms-nav-popup");
    cmsNavContainer = $("#" + navContainer);

    applySortableToElement(cmsNavContainer);

    resetPopupFormElements();

    // create default-home-element
    if ( navigationEmpty == true ) {
        currentNavEntry = {};
        currentNavEntry.id          = prefix + "1";
        currentNavEntry.itemId      = "1";
        currentNavEntry.title       = navigationDefault;
        currentNavEntry.position    = "1";
        currentNavEntry.parent      = "";
        currentNavEntry.enable      = "false";
        currentNavEntry.isHome      = "true";
        currentNavEntry.type        = "0";
        currentNavEntry.cms         = "-1";
        currentNavEntry.cmsTitle    = "";
        currentNavEntry.url         = "";
        currentNavEntry.insertInto  = "";

        createNewNavigationElement(currentNavEntry);
    }
    else {
        bindSortingToElements();
        restoreParentOrder();
        createNewJsonData();
    }
});

function bindSortingToElements()
{
    if ( $("#" + navContainer + " li").length ) {
        $("#" + navContainer + " li").each(function() {
            if ( $(this).attr("data-home") == "true" ) {
                $("#" + prefixSub + $(this).attr("id") ).remove();
                return false;
            }
        });
    }

    if ( $("#" + navContainer + " ul").length ) {
        $("#" + navContainer + " ul").each(function() {
            applySortableToElement( $("#" + $(this).attr("id")) );
        });
    }
}

function restoreParentOrder()
{
    if ( $("#" + navContainer + " li").length ) {
        $("#" + navContainer + " li").each(function() {
            if ( $(this).attr("data-parent").length ) {
                $(this).detach().appendTo( "#" + prefixSub + $(this).attr("data-parent") );
            }
        });
    }
}

function applySortableToElement(newElement)
{
    newElement.sortable({
                  "placeholder"     : "ui-state-highlight",
                  "connectWith"     : "#nav-template, #nav-template ul",
                  "items"           : "> li",
                  "dropOnEmpty"     : true,
                  "revert"          : true,

                  "sort" : function(event, ui) {
                               // Dummy :: TODO :: better way?
                               console.log( "sort elements..." );
                           },
                  "start": function(event, ui) {
                               ui.placeholder.height( ui.item.height() );
                           },
                  "stop" : function(event, ui) {
                               setNewPositionAfterMoving(navContainer, 1);
                           },
              })
              .disableSelection();
}

function resetPopupFormElements()
{
    $("#nav-id").val("");
    $("#nav-home").val("false");
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
    let newState;

    if ( $(elementId).length ) {
        if ( $(elementId).attr("data-enable") == "false" ) {
            $(elementId).attr("data-enable", "true")
                        .removeClass(disableClass);
            newState = true;

            if ( $("#" + prefixSub + prefix + itemNumber).length ) {
                $("#" + prefixSub + prefix + itemNumber + " li").each(function() {
                    $(this).attr("data-enable", "true")
                           .removeClass(disableClass);
                });
            }
        }
        else {
            $(elementId).attr("data-enable", "false")
                        .addClass(disableClass);
            newState = false;

            if ( $("#" + prefixSub + prefix + itemNumber).length ) {
                $("#" + prefixSub + prefix + itemNumber + " li").each(function() {
                    $(this).attr("data-enable", "false")
                           .removeClass(disableClass);
                });
            }
        }

        setNewPositionAfterMoving(navContainer, 1);

        $.ajax({
            "url"   : baseurl + "ajax_navigation.php",
            "method": "POST",
            "data"  : {
                          "action" : "change_state",
                          "navid"  : itemNumber,
                          "state"  : newState,
                      },
            "beforeSend": function() {
            }
        })
        .done(function(result) {
            let ajaxReturn = $.parseJSON(result);

            if ( ajaxReturn.error == true ) {
                alert( ajaxReturn.message );
            }
        })
        .fail(function(jqXHR, textStatus) {
            alert( ajax_error + textStatus );
        });
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

        $("#nav-home").val( $(elementId).attr("data-home") );
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
        setNewPositionAfterMoving(navContainer, 1);
    }
    else {
        alert( message_element_not_found + " (" + elementId + ")" );
    }
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
        styleClass = defaultClass;
    }
    else {
        styleClass = defaultClass + " " + disableClass;
    }

    if ( navData.type == 0 ) {
        // cms-Page-Title
        elementDescription = navData.cmsTitle;
    }
    else {
        // external URL
        elementDescription = navData.url;
    }

    navData.title = escapeHTML(navData.title);

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
        let template = $("template").html();

        let newItem = template.replace(/{{item_element}}/g    , navData.id)
                              .replace(/{{item_class}}/g      , styleClass)
                              .replace(/{{item_title}}/g      , navData.title)
                              .replace(/{{item_id}}/g         , navData.itemId)
                              .replace(/{{item_pos}}/g        , navData.position)
                              .replace(/{{item_parent}}/g     , navData.parent)
                              .replace(/{{item_enable}}/g     , navData.enable)
                              .replace(/{{item_home}}/g       , navData.home)
                              .replace(/{{item_type}}/g       , navData.type)
                              .replace(/{{item_cms}}/g        , navData.cms)
                              .replace(/{{item_url}}/g        , navData.url)
                              .replace(/{{item_decription}}/g , elementDescription);

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

        if ( navData.isHome == true ) {
             $("#" + prefixSub + navData.id).remove();
        }
        else {
            applySortableToElement( $("#" + prefixSub + navData.id) );
        }
    }

    // reset Pop-Element
    resetPopupFormElements();

    // reset all item positions
    setNewPositionAfterMoving(navContainer, 1);
}

function showPopup()
{
    cmsNavPopup.addClass("active");
}

function hidePopup()
{
    cmsNavPopup.removeClass("active");
}

function changeCmsNavType(element, maxCount)
{
    let newSelect = $(element).val();

    for ( let cnt = 0; cnt <= maxCount; cnt++ ) {
        $("#nav_type_" + cnt).removeClass("active");
    }

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
    else if ( $("#nav-type").prop("selectedIndex") == 1 ) {
        // create empty-Item

        // reset internal Page-Selector
        $("#nav-destination-cms").prop("selectedIndex", 0).change();

        // reset external URL
        $("#nav-destination-external").val("");
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
    currentNavEntry.home        = $("#nav-home").val();
    currentNavEntry.type        = $("#nav-type").prop("selectedIndex");
    currentNavEntry.cms         = $("#nav-destination-cms").prop("selectedIndex");
    currentNavEntry.cmsTitle    = $("#nav-destination-cms option:selected").text();
    currentNavEntry.url         = $("#nav-destination-external").val();
    currentNavEntry.insertInto  = "";
    currentNavEntry.home        = false;

    createNewNavigationElement(currentNavEntry);
    hidePopup();
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

function setNewPositionAfterMoving(element, counter)
{
    if ( $("#" + element + " > li").length ) {
        $("#" + element + " > li").each(function() {
            $(this).attr("data-position", counter);
            $(this).attr("data-parent"  , $("#" + element).attr("data-parent-element") );

            let subId = prefixSub + $(this).attr("id");
            if ( $("#" + subId + " > li").length ) {
                setNewPositionAfterMoving(subId, 1);
            }

            counter++;
        });
    }

    createNewJsonData();
}

function createNewJsonData()
{
    let navData = [];

    if ( $("#" + navContainer + " li").length ) {
        $("#" + navContainer + " li").each(function() {
            navData.push({
                        "item-id" : $(this).attr("data-item-id"),
                        "id"      : $(this).attr("id"),
                        "title"   : $(this).attr("data-title"),
                        "class"   : $(this).attr("class"),
                        "position": $(this).attr("data-position"),
                        "parent"  : $(this).attr("data-parent"),
                        "enable"  : $(this).attr("data-enable"),
                        "home"    : $(this).attr("data-home"),
                        "type"    : $(this).attr("data-type"),
                        "cms-id"  : $(this).attr("data-cms"),
                        "url"     : $(this).attr("data-url"),
                    });
        });
    }

    $("#nav-data").val( JSON.stringify(navData) );
}
