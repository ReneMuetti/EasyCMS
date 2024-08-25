let positions;
let popup = "#cms-gallery-popup";
let popupItems = "#cms-gallery-popup-items";
let popupFooter = "#cms-gallery-popup-footer";
let popupLoading = "popup-content-loaded";
let galleryImages = "#cms-gallery-elements";

$(document).ready(function(){
    if ( $("#cms-gallery-elements").length ) {
        $("#cms-gallery-elements").sortable({
            "stop": function(event, ui) {
                        setNewImageSortOrder();
                        createGalleryConfiguration();
                    }
        });
    }
});

function finishGalleryConfig()
{
    if ( $("#cms-gallery-type").val() >= 1 ) {
        let galleryType = $("#cms-gallery-options > div").attr("data-gallery-type");

        if ( galleryType == "blocks" ) {
            let perLineIdx = $("#gallery-option-elements-per-line").attr("data-items-per-line");
            $("#gallery-option-elements-per-line").val(perLineIdx).change();
        }
        else if ( galleryType == "simple_slider" ) {
            let directionIdx = $("#gallery-option-direction").attr("data-direction");
            $("#gallery-option-direction").val(directionIdx).change();
        }
    }
}

function loadPopupContent()
{
    $.ajax({
        "url"   : baseurl + "ajax_file_popup.php",
        "method": "POST",
        "async" : false,
        "data"  : {
                      "action": "block_list",
                      "subdir": $(popup).attr("data-curr-path"),
                      'multi' : true,
                  },
        "beforeSend": function() {
        }
    })
    .done(function(result) {
        let ajaxReturn = $.parseJSON(result);

        if ( ajaxReturn.error == true ) {
            alert( ajaxReturn.message );
        }
        else {
            $(popup).addClass(popupLoading);
            $(popupItems).html( ajaxReturn.data.items );
            $(popupFooter).html( ajaxReturn.data.footer );
        }
    })
    .fail(function(jqXHR, textStatus) {
        alert( ajax_error + textStatus );
    });
}

function loadGalleryConfiguration(sender)
{
    let newGalleryType = $(sender).val();

    if( newGalleryType == 0 ) {
        $("#cms-gallery-options").html("");
    }
    else {
        $.ajax({
            "url"   : baseurl + "ajax_cms_gallery.php",
            "method": "POST",
            "data"  : {
                          "action": "gallery_option",
                          "option": newGalleryType,
                      },
            "beforeSend": function() {
            }
        })
        .done(function(result) {
            let ajaxReturn = $.parseJSON(result);

            if ( ajaxReturn.error == true ) {
                alert( ajaxReturn.message );
            }
            else {
                $("#cms-gallery-options").html( ajaxReturn.data );
            }
        })
        .fail(function(jqXHR, textStatus) {
            alert( ajax_error + textStatus );
        });
    }
}

function setNewImageSortOrder()
{
    let counter = 1;

    $(galleryImages + " li").each(function() {
        $(this).attr("data-position", counter);
        counter++;
    });
}

function createImageConfiguration()
{
    let imageConfig = [];
    let counter = 1;

    $(galleryImages + " li").each(function() {
        $(this).attr("data-position", counter);

        imageConfig.push(
                        {
                            "imageId"   : counter,
                            "imagePath" : $(this).attr("data-image"),
                            "imageTitle": $(this).find("input[type=text]").filter(":first").val(),
                            "imageDescr": $(this).find("input[type=text]").eq(1).val(),
                        }
                    );

        counter++;
    });

    if ( !imageConfig.length ) {
        $("#images").val("");
    }
    else {
        $("#images").val( JSON.stringify(imageConfig) );
    }
}

function createGalleryConfiguration()
{
    let galleryConfig;
    let galleryType = $("#cms-gallery-options > div").attr("data-gallery-type");

    if ( $("#cms-gallery-options").html().length ) {
        switch(galleryType) {
            case "blocks": galleryConfig = {
                                               "type"     : galleryType,
                                               "showTitle": $("#gallery-option-elements-show-title").prop("checked"),
                                               "showDescr": $("#gallery-option-elements-show-description").prop("checked"),
                                               "perLine"  : $("#gallery-option-elements-per-line").val(),
                                           };
                           break;

            case "simple_slider": galleryConfig = {
                                                      "type"     : galleryType,
                                                      "showTitle": $("#gallery-option-elements-show-title").prop("checked"),
                                                      "showDescr": $("#gallery-option-elements-show-description").prop("checked"),
                                                      "direction": $("#gallery-option-direction").val(),
                                                      "speed"    : parseInt( $("#gallery-option-speed").val() ) ,
                                                      "height"   : parseInt( $("#gallery-option-height").val() ),
                                                  };
                                  break;

            case "splide": galleryConfig = {
                                               "type": galleryType,
                                           };
                           break;
        }

        $("#gallerycfg").val( JSON.stringify(galleryConfig) );
    }
    else {
        $("#gallerycfg").val("");
    }
}

function saveCurrentGallery()
{
    createGalleryConfiguration();
    setNewImageSortOrder();
    createImageConfiguration();
    $("#content-gallery form").submit();
}

function addNewImage()
{
    $(popup).dialog({
        "modal"   : true,
        "height"  : "auto",
        "width"   : 800,
        "title"   : _fixedSpecialCharacters(popup_title),
        "show"    : {
                        "effect"  : "slideDown",
                        "duration": 500,
                    },
        "hide"    : {
                        "effect"  : "slideUp",
                        "duration": 500,
                    },
        "position": {
                        "my": "center",
                        "at": "center top",
                        //"of": window
                    },
        "open": function() {
            if ( $(popup).hasClass("ui-dialog-content") && !$(popup).hasClass(popupLoading) ) {
                // popup-content is not loaded => AJAX
                loadPopupContent();
            }
        }
    });
}

function addSelectedElements()
{
    let newImages = [];

    $(popup).dialog( "close" );

    $(popupItems + " input").each(function() {
        let itemName = $(this).val();
        let itemSelect = $(this).is(":checked");

        if ( itemSelect == true ) {
            newImages.push( itemName );
            $(this).prop( "checked", false );
        }
    });

    let jsonImage = JSON.stringify(newImages);

    $.ajax({
        "url"   : baseurl + "ajax_file_popup.php",
        "method": "POST",
        "async" : false,
        "data"  : {
                      "action"  : "add_elements",
                      "elements": jsonImage,
                  },
        "beforeSend": function() {
        }
    })
    .done(function(result) {
        let ajaxReturn = $.parseJSON(result);

        if ( ajaxReturn.error == true ) {
            alert( ajaxReturn.message );
        }
        else {
            $(galleryImages).append( ajaxReturn.data );
            setNewImageSortOrder();
            createImageConfiguration();
        }
    })
    .fail(function(jqXHR, textStatus) {
        alert( ajax_error + textStatus );
    });
}

function removeItemFromGallery(sender) {
    $(sender).closest("li").remove();
    setNewImageSortOrder();
    createImageConfiguration();
}

function changeDirectory(goToParent = false, subDirName = "")
{
    let newPath;
    let currPath  = $(popup).attr("data-curr-path");
    let lastIndex = currPath.lastIndexOf(path_mask);

    if ( goToParent == true ) {
        if (lastIndex !== -1) {
            newPath = currPath.substring(0, lastIndex);
        }
    }
    else {
        newPath = currPath + path_mask + subDirName;
    }

    $(popup).attr("data-curr-path", newPath);
    loadPopupContent();
}