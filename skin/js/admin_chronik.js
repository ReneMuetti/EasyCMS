let chronikEditPopup;

let chronikContainer = "chronik-list";
let disableClass = "item-disable";

$(document).ready(function() {
    chronikEditPopup = $("#chronik-edit-popup");

    $("#" + chronikContainer).sortable({
                                 "placeholder"     : "ui-state-highlight",
                                 "connectWith"     : "#" + chronikContainer,
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
                                              setNewPositionAfterMoving(chronikContainer, 1);
                                          },
                             })
                             .disableSelection();

    resetForm();
});

function resetForm()
{
    chronikEditPopup.attr("data-curr-chronik-id", "");
    $("#chronik-title").val("");
    $("#chronik-content").val("");
    $("#chronik-status").prop("checked", true);
}

function showNewChronikPopup(event)
{
    event.preventDefault();
    chronikEditPopup.addClass("active");
}

function hideChronikPopup()
{
    // reset Form
    resetForm();

    chronikEditPopup.removeClass("active");
}

function editItem(itemNumber)
{
    if ( $("li[data-element=" + itemNumber + "]").length ) {
        let enableStatus = ($("#chronik-enable-" + itemNumber).val() == "true");

        chronikEditPopup.attr("data-curr-chronik-id", itemNumber);
        $("#chronik-title").val( $("#chronik-title-" + itemNumber).val() );
        $("#chronik-content").val( $("#chronik-content-" + itemNumber).val().replace(/{{rn}}/g, "\n") );
        $("#chronik-status").prop("checked", enableStatus);

        showNewChronikPopup(event);
    }
    else {
        alert( message_element_not_found + " (" + elementId + ")" );
    }
}

function switchStatus(itemNumber)
{
    let elementId = "#chronik-enable-" + itemNumber;

    if ( $(elementId).length ) {
         if ( $(elementId).val() == "false" ) {
            $(elementId).val("true");
            $("li[data-element=" + itemNumber + "]").removeClass(disableClass);
         }
         else {
            $(elementId).val("false");
            $("li[data-element=" + itemNumber + "]").addClass(disableClass);
         }
    }
    else {
        alert( message_element_not_found + " (" + elementId + ")" );
    }
}

function deleteItem(itemNumber)
{
    if ( $("li[data-element=" + itemNumber + "]").length ) {
        $("li[data-element=" + itemNumber + "]").remove();
    }
    else {
        alert( message_element_not_found + " (" + elementId + ")" );
    }
}

function createChronikEntry()
{
    let chronikTitle   = escapeHTML( $("#chronik-title").val() );
    let chronikContent = escapeHTML( $("#chronik-content").val() );

    if ( chronikEditPopup.attr("data-curr-chronik-id").length ) {
        // edit existing Chronik-Element
        let elementId = parseInt( chronikEditPopup.attr("data-curr-chronik-id") );

        $("li[data-element=" + elementId + "] > span:first-child").html(chronikTitle);
        $("li[data-element=" + elementId + "] > span:nth-child(2)").html(chronikContent);

        $("#chronik-title-" + elementId).val(chronikTitle);
        $("#chronik-content-" + elementId).val(chronikContent);
        $("#chronik-enable-" + elementId).val( $("#chronik-status").prop("checked") );

        if ( $("#chronik-status").prop("checked") == true ) {
            $("li[data-element=" + elementId + "]").removeClass(disableClass);
        }
        else {
            $("li[data-element=" + elementId + "]").addClass(disableClass);
        }
    }
    else {
        // create new Chronik-Element

        // get first free ID for new element
        getNewChronikId();
        let newChronikId = parseInt( $("#chronik-last").val() );
        let template  = $("template").html();
        let newItem   = template.replace(/{{id}}/g, newChronikId)
                                .replace(/{{position}}/g, 0)
                                .replace(/{{title}}/g, chronikTitle )
                                .replace(/{{content}}/g, chronikContent )
                                .replace(/{{enable}}/g, $("#chronik-status").prop("checked") );

        $("#" + chronikContainer).append(newItem);

        // reset all item positions
        setNewPositionAfterMoving(chronikContainer, 1);
    }

    // close & reset Popup
    hideChronikPopup();
}

function getNewChronikId()
{
    $.ajax({
        "url"   : baseurl + "ajax_chronik.php",
        "method": "POST",
        "async" : false,
        "data"  : {
                      "action" : "get_id",
                      "last-id": parseInt( $("#chronik-last").val() ),
                  },
        "beforeSend": function() {
        }
    })
    .done(function(returnId) {
        if ( parseInt(returnId) >= 1 ) {
            $("#chronik-last").val(returnId);
        }
        else {
            alert( new_chronik_item_id_error );
            $("#chronik-last").val("-1");
        }
    })
    .fail(function(jqXHR, textStatus) {
        alert( ajax_error + textStatus );
        $("#chronik-last").val("-1");
    });
}

function setNewPositionAfterMoving(element, counter)
{
    let elementId;

    if ( $("#" + element + " > li").length ) {
        $("#" + element + " > li").each(function() {
            elementId = "#chronik-position-" + $(this).attr("data-element");
            $(elementId).val(counter);

            counter++;
        });

        $("#chronik-count").val(--counter);
    }
}