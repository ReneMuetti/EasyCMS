$(document).ready(function() {
});

function changeExplorerStyle(newType)
{
    $("#media-manager-toolbar a").removeClass("current");
    $("#media-toolbar-" + newType).addClass("current");

    $("#media-manager-explorer").removeClass("explorer-list")
                                .removeClass("explorer-grid")
                                .addClass("explorer-" + newType);
}

function switchSymbolMode(sender)
{
    let addClass = "show-thumbnails";
    let newState = $(sender).prop("checked");

    if ( newState == false ) {
        $("#media-manager-explorer").addClass(addClass);
    }
    else {
        $("#media-manager-explorer").removeClass(addClass);
    }
}

function chancelNewDirectory()
{
    closeNewDirectoryForm();
}

function closeNewDirectoryForm()
{
    $("#new-directory-form").remove();
    $("#media-manager-path").attr("data-new-form", "false");
}

function createNewDirectoryForm()
{
    if ( $("#media-manager-path").attr("data-new-form") == "false" ) {
        $("#media-manager-path").attr("data-new-form", "true");

        let newInput = $("<input />", {
                           "id"   : "media-manager-new-directory",
                           "style": "width:200px; padding:12px; margin:0 10px 0 0;",
                           "value": ""
                       });
        let saveBtn = $("<button></button>", {
                          "onclick": "createNewDirectory()",
                          "style"  : "margin:0 10px 0 0;",
                          "class"  : "button",
                          "html"   : new_button_caption
                      });
        let cancelBtn = $("<button></button>", {
                            "onclick": "chancelNewDirectory()",
                            "style"  : "margin:0;",
                            "class"  : "button",
                            "html"   : chancel_button_caption
                        });
        let newBlock = $("<div></div>", {
                           "style": "display:inline;",
                           "id"   : "new-directory-form"
                       });

        newBlock.append(newInput).append(saveBtn).append(cancelBtn);

        $("#media-manager-path").append(newBlock);
    }
}

function createNewDirectory()
{
    if ( $("#media-manager-new-directory").val().length ) {
        let newSubDir = $("#media-manager-new-directory").val();
        newSubDir = newSubDir.replace(" ","_");

        $.ajax({
            "url"   : baseurl + "ajax_media_manager.php",
            "method": "POST",
            "data"  : {
                          "action"   : "new_directory",
                          "path"     : $("#media-manager-content").attr("data-current-path"),
                          "directory": newSubDir
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
                $("#media-manager-explorer").html(ajaxReturn.data);
                closeNewDirectoryForm();
            }
        })
        .fail(function(jqXHR, textStatus){
            alert( ajax_error + textStatus );
        });
    }
}

function openDirectory(subDirectory, parentDir)
{
    if ( subDirectory.length ) {
        $.ajax({
            "url"   : baseurl + "ajax_media_manager.php",
            "method": "POST",
            "data"  : {
                          "action"   : "change_directory",
                          "path"     : $("#media-manager-content").attr("data-current-path"),
                          "directory": subDirectory,
                          "parent"   : parentDir
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
                $("#media-manager-path").html(ajaxReturn.data.nav);
                $("#media-manager-content").attr("data-current-path", ajaxReturn.data.path);
                $("#media-manager-explorer").html(ajaxReturn.data.html);
            }
        })
        .fail(function(jqXHR, textStatus){
            alert( ajax_error + textStatus );
        });
    }
}

function deleteDirectory(subDirectory)
{
    if ( subDirectory.length ) {
        $.ajax({
            "url"   : baseurl + "ajax_media_manager.php",
            "method": "POST",
            "data"  : {
                          "action"   : "delete_directory",
                          "path"     : $("#media-manager-content").attr("data-current-path"),
                          "directory": subDirectory
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
                $("#media-manager-explorer").html(ajaxReturn.data);
            }
        })
        .fail(function(jqXHR, textStatus){
            alert( ajax_error + textStatus );
        });
    }
}

function deleteFile(filename)
{
    if ( filename.length ) {
        $.ajax({
            "url"   : baseurl + "ajax_media_manager.php",
            "method": "POST",
            "data"  : {
                          "action"   : "delete_file",
                          "path"     : $("#media-manager-content").attr("data-current-path"),
                          "filename" : filename
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
                $("#media-manager-explorer").html(ajaxReturn.data);
            }
        })
        .fail(function(jqXHR, textStatus){
            alert( ajax_error + textStatus );
        });
    }
}