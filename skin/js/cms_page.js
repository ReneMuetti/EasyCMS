// see @https://dsmorse.github.io/gridster.js/
let gridster = [];
let pageHeader   = "#cms-page-header ul";
let pageContent  = "#cms-page-content ul";
let pageFooter   = "#cms-page-footer ul";
let pageElements = [ ["header", pageHeader], ["content", pageContent], ["footer", pageFooter] ];

let popup  = "#cms-block-popup";
let prefix = "block-";
let popupPrefix  = "cms-popup-";
let popupLoading = "popup-content-loaded";
let maxBlockCount = 50;

$(document).ready(function(){
    $(pageElements).each(function(index, data) {
        gridster[index] = $(data[1]).gridster({
                          "namespace"             : "#cms-page-" + data[0],
                          "widget_base_dimensions": [140, 180],
                          "widget_margins"        : [5, 5],
                          "min_cols"              : 4,
                          "max_cols"              : 8,              // TODO: configurable
                          "resize": {
                              "enabled": true,
                              "stop"   : function() {
                                             saveBlockPositions();
                                         }
                          },
                          "draggable": {
                              "stop": function() {
                                         saveBlockPositions();
                                      }
                          },
                          "serialize_params": function($w, wgd) {
                                                  return {
                                                      "id"    : $w.prop("id"),
                                                      "b_id"  : $w.attr("data-block-id"),
                                                      "c_id"  : $w.attr("data-content-id"),
                                                      "c_type": $w.attr("data-content-type"),
                                                      "row"   : wgd.row,
                                                      "col"   : wgd.col,
                                                      "size_x": wgd.size_x,
                                                      "size_y": wgd.size_y,
                                                  }
                                              }
                      })
                      .data("gridster");
    });

    $("#cms-page-internal").on("input keydown keyup", function() {
        let currInternalVal = $(this).val();
        let newSeo;

        if ( currInternalVal.length ) {
            newSeo = currInternalVal.toLowerCase();
            newSeo = newSeo.replace(/[^a-z0-9\s]/g, '');
            newSeo = newSeo.replace(/\s+/g, '-');
        }

        $("#cms-page-seo").val(newSeo);
    });
});

function loadPopupContent()
{
    $.ajax({
        "url"   : baseurl + "ajax_cms_block.php",
        "method": "POST",
        "async" : false,
        "data"  : {
                      "action" : "block_list"
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
            $(popup).html( ajaxReturn.data )
                    .addClass(popupLoading);
        }
    })
    .fail(function(jqXHR, textStatus) {
        alert( ajax_error + textStatus );
    });
}

function addNewCmsBlock(element)
{
    $.ajax({
        "url"   : baseurl + "ajax_cms_block.php",
        "method": "POST",
        "data"  : {
                      "action": "new_block",
                      "prefix": prefix,
                      "number": getNextFreeNumberForNewBlock()
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
            // .add_widget( html, [size_x], [size_y], [col], [row] )
            gridster[element].add_widget(ajaxReturn.data, 2, 1);

            saveBlockPositions();
        }
    })
    .fail(function(jqXHR, textStatus){
        alert( ajax_error + textStatus );
    });
}

function removeBlockById(selectCmsBlockId)
{
    for (let i = 0; i < gridster.length; i++ ) {
        if(typeof gridster[i] !== "undefined") {
            gridster[i].remove_widget( $('#' + prefix + selectCmsBlockId) );
        }
    }

    saveBlockPositions();
}

function getNextFreeNumberForNewBlock()
{
    let counter = 1;
    let checkElementId;

    while( counter <= maxBlockCount ) {
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

function clearBlockById(selectCmsBlockId)
{
    $('#' + prefix + selectCmsBlockId).attr("data-content-type", "")
                                      .attr("data-content-id", "");
    $('#' + prefix + "content-" + selectCmsBlockId).html("");

    saveBlockPositions();
}

function selectCmsBlock(senderElement, senderType)
{
    let element;
    let elementId;
    let elementFullId;
    let layoutId;
    let layoutType;
    let layoutNum;

    let openPopup = false;

    if ( $(senderElement).prop("tagName") == "A" ) {
        element = $(senderElement).parent().parent();
        openPopup = true;
    }
    else {
        if ( senderType.length ) {
            element = $("#" + popupPrefix + senderType + "-" + senderElement);
        }
        else {
            element = $("#" + prefix + senderElement);
        }

    }

    elementId = element.id;

    layoutId   = element.attr("data-block-id");
    layoutType = element.attr("data-content-type");
    layoutNum  = element.attr("data-content-id");

    if ( openPopup == true ) {
        $(popup).dialog({
            "modal"   : true,
            "height"  : "auto",
            "width"   : 800,
            "title"   : popup_title.replace("{{id}}", layoutId),
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
                            //"of": "#cms-page-layout"
                        },
            "open": function() {
                if ( $(popup).attr("data-curr-cms-block-id") == "" ) {
                    $(popup).attr("data-curr-cms-block-id", layoutId);
                }

                if ( $(popup).hasClass("ui-dialog-content") && !$(popup).hasClass(popupLoading) ) {
                    // popup-content is not loaded => AJAX
                    loadPopupContent();
                }

                // highlight selected Item in Popup
                $(popup + " .popup-block").removeClass("block-selected");

                let selectPopupCmsBlockId = $(elementFullId).attr("data-content-id");
                if ( selectPopupCmsBlockId != "" ) {
                    // if Block selected => highlight
                    $(elementFullId).addClass("block-selected");
                }
            }
        });

        return;
    }

    if ( $(popup).attr("data-curr-cms-block-id") != "" ) {
        elementFullId = "#" + popupPrefix + senderType + "-" + senderElement;

        let currBlockName = "#" + prefix + $(popup).attr("data-curr-cms-block-id");
        let selBlockTitle = $(elementFullId).html();

        $(currBlockName).attr("data-content-id"  , senderElement);
        $(currBlockName).attr("data-content-type", $(elementFullId).attr("data-type"));
        $(currBlockName + " .block-content").html( selBlockTitle );

        saveBlockPositions();

        $(popup).attr("data-curr-cms-block-id", "");
        $(popup).dialog("close");
    }
    else {
        alert( block_select_error );
    }
}

function saveBlockPositions()
{
    let layoutConfig = [];
    let blockCounter = 0;

    $(pageElements).each(function(index, data) {
        if(typeof gridster[index] !== "undefined") {
            let currBlockLayout = gridster[index].serialize();
            layoutConfig.push(data[0], currBlockLayout);

            blockCounter += $(data[1] + " li").length;
        }
    });

    if ( $("#layout").length ) {
        $("#layout").val( JSON.stringify(layoutConfig) );
        $("#blockcount").val(blockCounter);
    }
    else {
        // this is only in config-page for Default-Page-Configuration

        if ( $("#default-layout-header").length ) {
            let tmpArray = [];
            tmpArray.push(layoutConfig[0], layoutConfig[1]);

            $("#default-layout-header").val( JSON.stringify(tmpArray) );
        }
        if ( $("#default-layout-footer").length ) {
            let tmpArray = [];
            tmpArray.push(layoutConfig[2], layoutConfig[3]);

            $("#default-layout-footer").val( JSON.stringify(tmpArray) );
        }
    }
}
