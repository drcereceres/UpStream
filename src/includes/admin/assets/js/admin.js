(function ($) {
    $('#toplevel_page_upstream_general ul.wp-submenu li').each(function() {
        if ($(this).find('a').length > 0) {
            if ($(this).find('a').attr('href') === 'admin.php?page=upstream_extensions') {
                // Check if the current menu links to the extensions page.
                $(this).addClass('upstream-extensions');
            }
        }
    });
})(jQuery);
