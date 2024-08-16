let reqFields = ["#contact-name", "#contact-email", "#contact-message"];

$(document).ready(function() {
    resetForm();
});

function sendForm()
{
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
                          $(reqFields).each(function(index, element) {
                              if ( $(element).val() == "" ) {
                                  alert( _fixedSpecialCharacters(contact_form_required_field_missing) );
                                  return false;
                              }
                          });
                      }
    })
    .done(function(result) {
        let ajaxReturn = $.parseJSON(result);

        if ( ajaxReturn.error == true ) {
            alert( ajaxReturn.message );
        }
        else {
            let newmsgBlock = $("<div></div>", {
                                  "html" : ajaxReturn.message,
                                  "class": "message-success"
                              });

            $("#message-block").append(newmsgBlock);
            resetForm();
        }
    })
    .fail(function(jqXHR, textStatus){
        alert( ajax_error + textStatus );
    });
}

function resetForm()
{
    $("#contact-name").val("");
    $("#contact-phone").val("");
    $("#contact-email").val("");
    $("#contact-message").val("");
    $("#contact-copy").prop("checked", false);
}