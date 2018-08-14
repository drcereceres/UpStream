(function ($) {
    // Highlight the extensions submenu.
    var allex = new Allex('upstream');
    allex.highlight_submenu('admin.php?page=upstream_extensions');

    // Mailchimp subscription form
    var attempts = 0,
        checkExist;

    checkExist = setInterval(function () {
        ++attempts;

        if (attempts >= 20) {
            clearInterval(checkExist);
            return;
        }

        if ($('#upstream_subscription_ad').length > 0) {
            clearInterval(checkExist);

            $('#upstream_subscription_ad form').on('submit', function (e) {
                if ($(this).find('#mce-EMAIL').val().trim() === '') {
                    e.preventDefault();
                    e.stopPropagation();

                    $(this).find('#mce-EMAIL').addClass('error');
                    $(this).find('#mce-EMAIL').focus();
                }
            });

            $('#upstream_subscription_ad form #mce-EMAIL').on('blur', function () {
                if ($(this).val().trim() !== '') {
                    $(this).removeClass('error');
                }
            });
        }
    }, 500); // check every 100ms
})(jQuery);
