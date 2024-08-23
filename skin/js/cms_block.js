let popup = "#insert-image-popup";
let popupItems = "#image-popup-items";
let popupFooter = "#image-popup-footer";
let popupLoading = "popup-content-loaded";
let summernoteContext;

$(document).ready(function(){
    $("#block-editor").summernote({
        "width"    : "98%",
        "height"   : "500px",
        "tabsize"  : 2,
        "lang"     : editor_lang,
        "focus"    : true,
        "toolbar"  : [
                         ['style',    ['style']],
                         ['fontname', ['fontname']],
                         ['fontsize', ['fontsize']],
                         ['font',     ['bold', 'italic', 'underline', 'clear']],
                         ['color',    ['color']],
                         ['height',   ['height']],
                         ['para',     ['ul', 'ol', 'paragraph']],
                         ['table',    ['table']],
                         ['insert',   ['link']],
                         ['image',    ['customImage']],
                         ['view',     ['fullscreen', 'codeview', 'help']],
                     ],
        "fontSizes": ["7", "8", "9", "10", "11", "12", "13", "14", "16", "18", "20", "22", "24", "26", "28", "30", "32", "34", "36", "40", "48" , "64"],
        "popover"  : [
                         'link', ['linkDialogShow', 'unlink'],
                     ],
        "buttons"  : {
                         "customImage": function(context) {
                                            summernoteContext = context;

                                            var ui = $.summernote.ui;
                                            var options = $.summernote.options;
                                            var lang = $.summernote.lang[editor_lang];
                                            var button = ui.button({
                                                    "contents": ui.icon(context.options.icons.picture),
                                                    "title"   : context.options.langInfo.image.image,
                                                    "click"   : function() {
                                                                    showImagePopup();
                                                                }
                                                });
                                            var $button = button.render();

                                            $button.attr("data-toggle", "tooltip").attr("title", context.options.langInfo.image.image);
                                            $button.tooltip({
                                                "container": "body"
                                            });

                                            return $button;
                                        }
                     },
    });
});

function loadPopupContent()
{
    $.ajax({
        "url"   : baseurl + "ajax_file_popup.php",
        "method": "POST",
        "async" : false,
        "data"  : {
                      "action": "block_list",
                      "subdir": $(popup).attr("data-curr-path"),
                      'multi' : false,
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
        }
    })
    .fail(function(jqXHR, textStatus) {
        alert( ajax_error + textStatus );
    });
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

function insertImage(sender)
{
    let ImageUrl = $(sender).children("img").attr("src");
    if ( ImageUrl ) {
        summernoteContext.invoke("editor.insertImage", ImageUrl);
    }
    $(popup).dialog("close");
}

function showImagePopup()
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