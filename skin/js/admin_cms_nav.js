let cmsNavPopup;

$(document).ready(function(){
    cmsNavPopup = $("#cms-nav-popup");
});

function resetPopupFormElements()
{
    $("#nav-id").val("0");
    $("#nav-parent-id").val("0");
    $("#nav-position").val("0");
    $("#nav-title, #nav-destination-external").val("");
    $("#nav-status").prop("checked", false);
    $("#nav-type, #nav-destination-cms").prop("selectedIndex", 0).change();
}

function showPopup(editItem)
{
    resetPopupFormElements();

    if ( parseInt(editItem) >= 1 ) {
        // load Data by Ajax and Edit-Item
        $("#nav-id").val( parseInt(editItem) );
    }

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
        // check, if CMS-Page selected
        if ( $("#nav-destination-cms").prop("selectedIndex") == 0 ) {
            // no valid CMS-Page selected
            alert( message_no_cms_page_selected );
            return false;
        }
    }
    else {
        // check, if external link
        if ( $("#nav-destination-external").val().length === 0 ) {
            // Input-Field is empty
            alert( message_no_external_link );
            return false;
        }
    }

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
}