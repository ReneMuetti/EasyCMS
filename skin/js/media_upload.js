let chunkSize = 1024 * 1024;
let progressBar;
let progressStatus;
let uploadForm;
let fileReader;
let debug = false;
let fileInput;

$(document).ready(function() {
    uploadForm = $("#file-upload");
    progressBar = $("#progress-bar");
    progressStatus = $("#progress-status");

    startFileUpload();

    $("#uploadfile").attr( "accept", fileTypeArray[$("#upload-type").val()] );
    $("#filter").val( fileTypeArray[$("#upload-type").val()] );
});

function changeUploadFilter(sender)
{
    let acceptTypes = "";
    let newSelect = $(sender).val();

    if ( fileTypeArray[newSelect] !== undefined ) {
        acceptTypes = fileTypeArray[newSelect];
    }
    else {
        acceptTypes = "*";
    }

    if ( debug == true ) {
        console.log("changeUploadFilter() :: " + acceptTypes);
    }

    $("#uploadfile").attr("accept", acceptTypes);
    $("#filter").val(acceptTypes);
}

function startFileUpload()
{
    if ( uploadForm.length ) {
        setProgressBar("upload-progress", 0);
        progressStatus.html("");

        uploadForm.submit(function(event) {
            event.preventDefault();

            let uploadElement = $("#uploadfile").get(0);
            let uploadCounter = uploadElement.files.length;
            for ( let fileNum = 0; fileNum < uploadCounter; fileNum++ ) {
                fileInput = uploadElement.files[fileNum];

                if ( debug == true ) {
                    console.log("startFileUpload() :: " + fileInput.name);
                }

                uploadChunk(fileInput, 0);
            }
        });
    }
}

function resetUploadForm()
{
    setProgressBar("upload-progress", 0);
    progressStatus.html("");
    $("#uploadfile").val("");
    uploadForm.get(0).reset();
}

function setProgressBar(element, percent)
{
    newStyle = "linear-gradient(to left, transparent " + (100 - percent) + "%, var(--rgba-color-white) 0%)";

    $("#" + element + " .rgb-bar").css({
        "mask"        : newStyle,
        "-webkit-mask": newStyle
    });
}

function uploadChunk(file, offset)
{
    fileReader = new FileReader();

    fileReader.onload = function(event) {
        let filename = file.name;
        let data     = event.target.result.split(",")[1];
        let index    = offset / chunkSize;

        $.ajax({
            "type"    : "POST",
            "url"     : baseurl + "ajax_upload_file.php",
            "dataType": "json",
            "data"    : {
                "filename": filename,
                "index"   : index,
                "data"    : data,
                "eof"     : offset + chunkSize >= file.size,
                "dest"    : $("#media-manager-content").attr("data-current-path"),
                "filter"  : $("#filter").val(),
            },
            "beforeSend": function() {
            }
        })
        .done(function(response) {
            if ( debug == true ) {
                console.log("uploadChunk() :: Server Response:", response);
            }

            if ( response.done == true ) {
                progressStatus.html( message_upload_complete );

                // update file manager
                if ( response.error == true ) {
                    alert(response.content);
                }
                else {
                    $("#media-manager-explorer").html(response.content);
                }

                setTimeout(function() {
                    resetUploadForm();
                }, 1000);

                if ( debug == true ) {
                    console.log("uploadChunk() :: Done " + filename);
                }
            }
            else {
                progressStatus.html( message_upload_progress + ": " + index + " " + message_upload_progress_pice + " [" + filename + "]" );
            }

            if (offset + chunkSize < file.size) {
                uploadChunk(file, offset + chunkSize);
            } else {
                setProgressBar("upload-progress", 100);
            }
        })
        .fail(function(jqXHR, textStatus){
            alert( ajax_error + textStatus );
        });

        let progress = ((offset + chunkSize) / file.size) * 100;
        setProgressBar("upload-progress", progress);
    };

    let chunk = file.slice(offset, offset + chunkSize);
    fileReader.readAsDataURL(chunk);
}