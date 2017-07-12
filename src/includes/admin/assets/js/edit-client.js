(function(window, document, $, undefined) {
    $(document).ready(function() {
        $('#post').on('submit', function(e) {
            var usersWrapper = $('#_upstream_client_users_repeat');
            var usersList = $('.postbox.cmb-row.cmb-repeatable-grouping', usersWrapper);
            if (usersList.length > 0) {
                for (var userIndex = 0; userIndex < usersList.length; userIndex++) {
                    var emailField = $('input[type="email"]', usersList[userIndex]);
                    var emailFieldValue = emailField.val();
                    if (!emailFieldValue.length) {
                        e.preventDefault();
                        emailField.focus();
                        return false;
                    }
                }
            }
        });
    });
})(window, window.document, jQuery || null);
