/*
(function($){
client_user = {

    init : function() {

        var $tasks  = $("#upstream_client_users_repeat");
        var task    = $tasks.find(".postbox");

        $.map( task, function(item) {

            $(item).addClass('closed');
            client_user.replaceTitle();
            client_user.makePrimary( item );

        });

        $(document).on('keyup change', '.user-email', function () {
            client_user.replaceTitle();
        });
        $(document).on('click', '.cmb-add-group-row', function () {
            client_user.addGroupRow();
        });
        $(document).on('keyup change', '.make-primary', function () {
            if( $(this).prop('checked') ) {
                $(this).parents('.cmb-repeatable-grouping').find('.cmbhandle').after( '<span class="primary">Primary User</span>' );
            } else {
                $(this).parents('.cmb-repeatable-grouping').find('.primary').remove();
            }
        });

    },

    makePrimary : function( item ) {

        if( $(item).find(".make-primary").prop('checked') ) {
            $(item).find('.cmbhandle').after( '<span class="primary">Primary User</span>' );
        } else {
            $(item).find('.primary').remove();
        }

    },

    replaceTitle : function() {

        var $tasks  = $("#upstream_client_users_repeat");
        var task    = $tasks.find(".postbox");

        $.map( task, function(item) {

            title = $(item).find('.user-email').val();
            if( title != '' ) {
                $(item).find('.cmb-group-title').html(title);
            }
        });
    },

    addGroupRow : function() {

        var $tasks  = $("#upstream_client_users_repeat");
        var task    = $tasks.find(".postbox");

        $(task).prev().addClass('closed');
        $(task).last().find('.primary').remove();
        $(task).last().find('.first-name').focus();
        client_user.replaceTitle();

    },


}


})(jQuery);

jQuery(document).ready(function($) {
    client_user.init();

    var form = $(document.getElementById('post'));
    var body = $('html, body');
    var requiredFields = $('[data-validation]');

    if (!requiredFields.length) {
        return;
    }

    console.log('hue');
});
*/

(function(window, document, $, undefined) {
  if (!$) {
    console.error('UpStream requires jQuery.');
  }

  $(document).ready(function() {
    /*
    function validateRequiredFields(e) {
      e.preventDefault();
      return false;
      var requiredFields = $('[data-validation]');
      if (!requiredFields.length) {
        return;
      }

      requiredFields.each(function() {
        var self = $(this);
        var value = self.val();

        if (self.is('[type="button"]') || self.is('.cmb2-upload-file-id')) {
          return true;
        }

        if (self.data('validation') === "required") {
          if (!value) {
            self.addClass('required-error');
          } else {
            self.removeClass('required-error');
          }
        }
      });

      if ($('.required-error').length) {
        e.preventDefault();
        alert('Errrrrrrror');
      }
    }

    $('#post').on('submit', validateRequiredFields);
    */

    $('#post').on('submit', function(e) {
    });

    $('.cmb2-id--upstream-client-users').on('blur', 'input[type="email"]', function(e) {
      var self = $(this);
      var email = self.val();

      if (!(/\S+@\S+/).test(email)) {
        self.addClass('has-error');
      } else {
        self.removeClass('has-error');

        $.ajax({
          type: 'POST',
          url : 'admin-ajax.php',
          data: {
            action : 'upstream_email_validation',
            subject: email
          },
          beforeSend: function(jqXHR, settings) {
            self.addClass('validating');
            self.attr('disabled', 'disabled');
            self.val(email + ' validating...');
          },
          success: function(data, textStatus, jqXHR) {
            console.log(data);
          },
          error: function(jqXHR, textStatus, errorThrown) {
            console.error(textStatus + ': ' + errorThrown);
          },
          complete: function(jqXHR, textStatus) {
            self.val(email);
            self.attr('disabled', null);
            self.removeClass('validating');
          }
        });
      }
    });
  });
})(window, window.document, jQuery || null);
