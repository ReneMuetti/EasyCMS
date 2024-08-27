let reqFields = ["#contact-name", "#contact-email", "#contact-message"];
let canSend;

$(document).ready(function() {
    resetForm();
});

function sendForm()
{
    $(reqFields).each(function(index, element) {
                          if ( $(element).val() == "" ) {
                              addMessage("error", contact_form_required_field_missing);

                              canSend = false;
                              return false;
                          }
                      });

    if ( canSend == true ) {
        $.ajax({
            "url"   : baseurl + "ajax_contact_form.php",
            "method": "POST",
            "data"  : {
                          "action" : "new_message",
                          "name"   : $("#contact-name").val(),
                          "phone"  : $("#contact-phone").val(),
                          "email"  : $("#contact-email").val(),
                          "message": $("#contact-message").val(),
                          "copy"   : $("#contact-copy").is(":checked"),
                      },
            "beforeSend": function() {
                          }
        })
        .done(function(result) {
            let ajaxReturn = $.parseJSON(result);

            if ( ajaxReturn.error == true ) {
                addMessage("error", ajaxReturn.message);
            }
            else {
                addMessage("success", ajaxReturn.message);
                resetForm();
            }
        })
        .fail(function(jqXHR, textStatus){
            alert( ajax_error + textStatus );
        });
    }
}

function addMessage(statusClass, messageText)
{
    let newmsgBlock = $("<div></div>", {
                          "html" : _fixedSpecialCharacters(messageText),
                          "class": "message-" + statusClass
                      });
    $("#message-block").append(newmsgBlock);
}

function resetForm()
{
    canSend = true;

    $("#contact-name").val("");
    $("#contact-phone").val("");
    $("#contact-email").val("");
    $("#contact-message").val("");
    $("#contact-copy").prop("checked", false);
}