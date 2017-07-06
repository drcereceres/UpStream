(function(window, document, $, ajaxurl, undefined) {
  'use strict';

  if (!$) {
    console.error('UpStream requires jQuery.');
  }

  if (!$('#titlewrap').length) {
    return;
  }

  // Make the Client Name field required.
  (function() {
    var titleWrap = $('#titlewrap');
    var titleLabel = $('#title-prompt-text', titlewrap);

    titleLabel.text(titleLabel.text() + ' *');

    $('#title', titlewrap).attr('required', 'required');
  })();

  $(document).ready(function() {
    $('#form-add-new-user button[type="submit"]').on('click', function(e) {
      e.preventDefault();

      var self = $(this);
      var form = $('#form-add-new-user');
      var hasError = false;

      var usernameField = $('[name="username"]', form);
      var usernameFieldValue = usernameField.val();
      // Check if username is potentially valid.
      if (usernameFieldValue.length < 3 || usernameFieldValue.length > 60 || !/^[a-z]+[a-z0-9\-\_]+$/i.test(usernameFieldValue)) {
        usernameField.focus();
        return;
      }

      var passwordField = $('[name="password"]', form);
      if (passwordField.val().length < 6) {
        passwordField.focus();
        return;
      }

      var inputsList = $('input', form);
      for (var inputIndex = 0; inputIndex < inputsList.length; inputIndex++) {
        var input = $(inputsList[inputIndex]);
        if (input.attr('required')) {
          var value = input.val() || "";
          if (value.trim().length === 0) {
            input.focus();
            hasError = true;
            break;
          }
        }
      }

      if (!hasError) {
        $.ajax({
          type: 'POST',
          url : ajaxurl,
          data: {
            action      : 'upstream:client.add_new_user',
            client      : $('#post_ID').val(),
            username    : usernameField.val(),
            email       : $('[name="email"]', form).val(),
            password    : passwordField.val(),
            first_name  : $('[name="first_name"]', form).val(),
            last_name   : $('[name="last_name"]', form).val(),
            notification: $('[name="notification"]', form).is(':checked'),
          },
          beforeSend: function(jqXHR, settings) {
            $('.error-message', form).remove();
          },
          success   : function(response, textStatus, jqXHR) {
            if (!response.success) {
              form.prepend($('<p class="error-message">' + response.err + '</p>'));
            } else {
              $('#TB_closeWindowButton').trigger('click');
              // @todo: reset form
              // @todo: close modal
              // @todo: append user to users-table

              var tr = $('<tr data-id="'+ response.data.id +'"></tr>');
              tr.append('<td>'+ response.data.name +'</td>');
              tr.append('<td>'+ response.data.username +'</td>');
              tr.append('<td>'+ response.data.email +'</td>');
              tr.append('<td>'+ response.data.assigned_at +'</td>');
              tr.append('<td>'+ response.data.assigned_by +'</td>');
              tr.append('<td>x</td>');

              var table = $('#table-users');
              $('tr[data-empty]', table).remove();

              $('tbody', table).append(tr);
            }
          },
          error     : function(jqXHR, textStatus, errorThrown) {},
          complete  : function(jqXHR, textStatus) {}
        });
      }
    });
  });
})(window, window.document, jQuery || null, ajaxurl);
