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
});
