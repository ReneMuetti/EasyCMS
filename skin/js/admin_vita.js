let vitaEditPopup;
let vitaImagePopup;

let vitaContainer = "vita-list";
let disableClass = "item-disable";

$(document).ready(function() {
    vitaEditPopup  = $("#vita-edit-popup");
    vitaImagePopup = $("#vita-image-popup");

    $("#" + vitaContainer).sortable({
                              "placeholder"     : "ui-state-highlight",
                              "connectWith"     : "#" + vitaContainer,
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
                                           setNewPositionAfterMoving(vitaContainer, 1);
                                       },
                          })
                          .disableSelection();

    resetForm();
});

function resetForm()
{
    vitaEditPopup.attr("data-curr-vita-id", "");
    $("#vita-title").val("");
    $("#vita-descr").val("");
    $("#vita-status").prop("checked", true);
}

function showNewVitaPopup(event)
{
    event.preventDefault();
    vitaEditPopup.addClass("active");
}

function hideVitaPopup()
{
    // reset Form
    resetForm();

    vitaEditPopup.removeClass("active");
}

function editItem(itemNumber)
{
    if ( $("li[data-element=" + itemNumber + "]").length ) {
        let enableStatus = ($("#vita-enable-" + itemNumber).val() == "true");

        vitaEditPopup.attr("data-curr-vita-id", itemNumber);
        $("#vita-title").val( $("#vita-title-" + itemNumber).val() );
        $("#vita-descr").val( $("#vita-decription-" + itemNumber).val() );
        $("#vita-status").prop("checked", enableStatus);

        showNewVitaPopup(event);
    }
    else {
        alert( message_element_not_found + " (" + elementId + ")" );
    }
}

function switchStatus(itemNumber)
{
    let elementId = "#vita-enable-" + itemNumber;

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

function saveVitaEntry()
{
    let vitaTitle = escapeHTML( $("#vita-title").val() );
    let vitaDescr = escapeHTML( $("#vita-descr").val() );

    if ( vitaEditPopup.attr("data-curr-vita-id").length ) {
        // edit existing Vita-Element
        let elementId = parseInt( vitaEditPopup.attr("data-curr-vita-id") );

        $("li[data-element=" + elementId + "] > span:first-child").html(vitaTitle);
        $("li[data-element=" + elementId + "] > span:nth-child(2)").html(vitaDescr);

        $("#vita-title-" + elementId).val(vitaTitle);
        $("#vita-decription-" + elementId).val(vitaDescr);
        $("#vita-enable-" + elementId).val( $("#vita-status").prop("checked") );

        if ( $("#vita-status").prop("checked") == true ) {
            $("li[data-element=" + elementId + "]").removeClass(disableClass);
        }
        else {
            $("li[data-element=" + elementId + "]").addClass(disableClass);
        }
    }
    else {
        // create new Vita-Element

        // get first free ID for new element
        getNewVitaId();
        let newVitaId = parseInt( $("#vita-last").val() );
        let template  = $("template").html();
        let newItem   = template.replace(/{{id}}/g, newVitaId)
                                .replace(/{{position}}/g, 0)
                                .replace(/{{title}}/g, vitaTitle )
                                .replace(/{{decription}}/g, vitaDescr )
                                .replace(/{{enable}}/g, $("#vita-status").prop("checked") )
                                .replace(/{{image}}/g, "");

        $("#" + vitaContainer).append(newItem);

        // reset all item positions
        setNewPositionAfterMoving(vitaContainer, 1);
    }

    // close & reset Popup
    hideVitaPopup();
}

function getNewVitaId()
{
    $.ajax({
        "url"   : baseurl + "ajax_vita.php",
        "method": "POST",
        "async" : false,
        "data"  : {
                      "action" : "get_id",
                      "last-id": parseInt( $("#vita-last").val() ),
                  },
        "beforeSend": function() {
        }
    })
    .done(function(returnId) {
        if ( parseInt(returnId) >= 1 ) {
            $("#vita-last").val(returnId);
        }
        else {
            alert( new_vita_item_id_error );
            $("#vita-last").val("-1");
        }
    })
    .fail(function(jqXHR, textStatus) {
        alert( ajax_error + textStatus );
        $("#vita-last").val("-1");
    });
}

function setNewPositionAfterMoving(element, counter)
{
    let elementId;

    if ( $("#" + element + " > li").length ) {
        $("#" + element + " > li").each(function() {
            elementId = "#vita-position-" + $(this).attr("data-element");
            $(elementId).val(counter);

            counter++;
        });

        $("#vita-count").val(--counter);
    }
}