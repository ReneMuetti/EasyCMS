// see @https://dsmorse.github.io/gridster.js/
let gridster = [];
let pageElements = [ ["header", "#gridster-header"], ["content", "#gridster-content"], ["footer", "#gridster-footer"] ];
let blockHeight;

$(document).ready(function() {
    let raster = pageWidth / 8;

    $(pageElements).each(function(index, data) {
        if ( $(data[1]).length && data[1] != "#gridster-content" ) {
            blockHeight = parseInt($(data[1]).css("height"), 10);
        }
        else {
            blockHeight = defaultBlockHeight;
        }

        gridster[index] = $(data[1]).gridster({
                              "namespace"             : "#gridster-" + data[0],
                              "widget_selector"       : "div",
                              "widget_margins"        : [0, 0],
                              "widget_base_dimensions": [raster, blockHeight],
                          })
                          .data("gridster")
                          .disable();
    });
});