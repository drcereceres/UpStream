(function($){

    function initProject() {
        var $box = $( document.getElementById( 'post-body' ) );

        var groups = [
            '#_upstream_project_milestones',
            '#_upstream_project_tasks',
            '#_upstream_project_bugs',
            '#_upstream_project_files'
        ];

        $( groups ).each( function( index, element ) {

            var $group  = $box.find( element );
            var $items  = $group.find( '.cmb-repeatable-grouping' );

            // UI stuff
            $items.addClass( 'closed' );
            hideFirstItemIfEmpty( $group );
            hideFieldWrap( $group );

            // add dynamic data into group row title
            replaceTitles( $group );
            addAvatars( $group );

            // permissions
            publishPermissions( $group );
            deletePermissions( $group );
            fileFieldPermissions( $group );

            // when we do something
            $group
                .on( 'cmb2_add_row', function( evt ) {
                    addRow( $group );
                })
                .on( 'change cmb2_add_row cmb2_shift_rows_complete', function( evt ) {
                    resetGroup( $group );
                })
                .on( 'keyup', titleOnKeyUp );

            // milestone specific
            if( $group.attr('id') == '_upstream_project_milestones' ) {

                displayMilestoneProgress( $group );
                displayMilestoneIcon( $group );

                $group
                    .on( 'change cmb2_add_row cmb2_shift_rows_complete', function( evt ) {
                        displayMilestoneProgress( $group );
                        displayMilestoneIcon( $group );
                    });

            }

            // task specific
            if( $group.attr('id') == '_upstream_project_tasks' ) {

                displayStatusColor( $group );
                displayMilestoneIcon( $group );
                displayProgress( $group );
                displayEndDate( $group );

                $group
                    .on( 'change cmb2_add_row cmb2_shift_rows_complete', function( evt ) {
                        displayStatusColor( $group );
                        displayMilestoneIcon( $group );
                        displayProgress( $group );
                        displayEndDate( $group );
                    });
            }

            // bug specific
            if( $group.attr('id') == '_upstream_project_bugs' ) {

                displayStatusColor( $group );
                displayEndDate( $group );

                $group
                    .on( 'change cmb2_add_row cmb2_shift_rows_complete', function( evt ) {
                        displayStatusColor( $group );
                        displayEndDate( $group );
                    });
            }

        });

    }

    function resetGroup( $group ) {
        replaceTitles( $group );
        addAvatars( $group );
    }

    /*
     * Disable 'add new' button if permissions don't allow it.
     * Used in all groups.
     */
    function publishPermissions( $group ) {
        if( ! $group.find( '.hidden' ).attr( 'data-publish' ) ) {
            $group.find( '.cmb-add-row button' ).prop( 'disabled', true ).prop( 'title', 'You do not have permission for this' );
        }
    };

    /*
     * Disable 'delete' button if permissions don't allow it.
     * Used in all groups.
     */
    function deletePermissions( $group ) {
        $group.find( '.cmb-repeatable-grouping' ).each( function() {
            var isOwner = $( this ).find( '[data-owner]' ).attr( 'data-owner' );
            if( isOwner != 'true' ) {
                $( this ).find( 'button.cmb-remove-group-row' ).prop( 'disabled', true ).prop( 'title', 'You do not have permission for this' );
            }
        });
    };

    /*
     * Disable 'upload file' button if permissions don't allow it.
     * Used in bugs and files.
     */
    function fileFieldPermissions( $group ) {
        $group.find( '.cmb-repeatable-grouping' ).each( function() {
            var file        = $( this ).find( '.cmb-type-file' );
            var disabled    = $( file ).find( '[data-disabled]' ).attr( 'data-disabled' );
            if( disabled == 'true' ) {
                $( this ).on( 'click', '.cmb-attach-list li, .cmb2-media-status .img-status img, .cmb2-media-status .file-status > span', function() {
                    return false;
                });
                $( file ).find( 'input.cmb2-upload-button' ).prop( 'disabled', true ).prop( 'title', 'You do not have permission for this' );
                $( file ).find( '.cmb2-remove-file-button' ).hide();
            }
        });
    };

    /*
     * Hides the row if there is only 1 and it is empty.
     *
     */
    function hideFirstItemIfEmpty( $group ) {
        if( $group.attr( 'id' ) == '_upstream_project_milestones' ) {
            var $items = $group.find( '.cmb-repeatable-grouping' ).first();
            $items.removeClass( 'closed' );
            return;
        }

        if( $group.find( '.hidden' ).attr( 'data-empty' ) == '1' ) {
            if( $group.find('.cmb-repeatable-grouping').length == 1 ) {
                $group.find('.cmb-repeatable-grouping').hide();
            }
        }
    };

    /*
     * Hide the field wrapping row if an input field has been hidden.
     * Via a filter such as add_filter( 'upstream_bug_metabox_fields', 'upstream_bugs_hide_field_for_role', 99, 3 );
     */
    function hideFieldWrap( $group ) {
        $group.find( 'input, textarea, select' ).each( function() {
            if( $( this ).hasClass( 'hidden' ) ) {
                $( this ).parents('.cmb-repeat-group-field').addClass('hidden');
            }
        });
    };

    /*
     * Displays the avatar in the title.
     * Used in all groups.
     */
    function addAvatars( $group ) {

        $group.find( '.cmb-repeatable-grouping' ).each( function() {
            var $this           = $( this );
            var user_assigned   = $this.find( '[data-user_assigned]' ).attr( 'data-user_assigned' );
            var user_created    = $this.find( '[data-user_created_by]' ).attr( 'data-user_created_by' );
            var av_assigned     = $this.find( '[data-avatar_assigned]' ).attr( 'data-avatar_assigned' );
            var av_created      = $this.find( '[data-avatar_created_by]' ).attr( 'data-avatar_created_by' );

            // create the boxes to hold the images first
            $this.find( 'h3 span.title' ).prepend( '<div class="av-created"></div><div class="av-assigned"></div>' );

            if( av_created ) {
                $this.find( '.av-created' ).html( '<img title="Created by: ' + user_created + '" src="' + av_created + '" height="25" width="25" />' );
            }

            if( av_assigned && $this.attr( 'id' ) != '_upstream_project_files' ) {
                $this.find( '.av-assigned' ).html( '<img title="Assigned to: ' + user_assigned + '" src="' + av_assigned + '" height="25" width="25" />' );
            }

        });
    };


    /*
     * Displays the title in the title.
     * Used in all groups.
     */
    function replaceTitles( $group ) {

        if( $group.attr( 'id' ) == '_upstream_project_milestones' ) {

            $group.find( '.cmb-group-title' ).each( function() {
                var $this   = $( this );
                var title   = $this.next().find( '[id$="milestone"]' ).val();
                var start   = $this.next().find( '[id$="start_date"]' ).val();
                var end     = $this.next().find( '[id$="end_date"]' ).val();
                var dates   = '<div class="dates">' + start + ' - ' + end + '</div>';
                if ( title ) {
                    $this.html( '<span class="title">' + title + '</span>' + dates );
                }
            });

        } else {

            $group.find( '.cmb-group-title' ).each( function() {
                var $this       = $( this );
                var title       = $this.next().find( '[id$="title"]' ).val();
                var grouptitle  = $group.find( '[data-grouptitle]' ).data( 'grouptitle' );
                if ( ! title ) {
                    var $row        = $this.parents( '.cmb-row.cmb-repeatable-grouping' );
                    var rowindex    = $row.data( 'iterator' );
                    var newtitle    = grouptitle.replace( '{#}', ( rowindex + 1 ) );
                    $this.html( '<span class="title">' + newtitle + '</span>' );
                } else {
                    $this.html( '<span class="title">' + title + '</span>' );
                }
                if( grouptitle == 'Task {#}' )
                    displayProgress( $group );
            });

        }
    };

    function titleOnKeyUp( evt ) {
        var $group  = $( evt.target ).parents( '.cmb2-wrap.form-table' );
        replaceTitles( $group );
        addAvatars( $group );
    };

    /*
     * Displays the total milestone progress in the title.
     * Only used on the Milestones group.
     */
    function displayMilestoneProgress( $group ) {
        $group.find( '.cmb-repeatable-grouping' ).each( function() {
            var $this       = $( this );
            var title       = $this.find('.cmb-group-title .title').text();
            if( title ) {
                var progress = $('ul.milestones li .title:contains(' + title + ')').next().next().text();
            } else {
                var progress = '0';
            }
            progress = progress ? progress : '0';
            $this.find('.progress').remove();
            $this.append( '<span class="progress"><progress value="' + progress + '" max="100"></progress></span>' );
        });
    };


    /*
     * Displays the milestone icon in the title.
     * Used in tasks and bugs.
     */
    function displayMilestoneIcon( $group ) {
        $group.find( '.cmb-repeatable-grouping' ).each( function() {
            var $this       = $( this );
            var milestone   = $this.find('[id$="milestone"] option:selected').text();

            if( milestone ){
                $this.find('.on-title.dashicons').remove();
                var color   = $('ul.milestones .title:contains(' + milestone + ')').next().text();

                $this.find('button.cmb-remove-group-row.dashicons-before').after( '<span style="color: ' + color + '" class="dashicons dashicons-flag on-title"></span> ' );
            }
        });
    };

    /*
     * Displays the status in the title.
     * Used in bugs and tasks.
     */
    function displayStatusColor( $group ) {
        $group.find( '.cmb-group-title' ).each( function() {
            var $this       = $( this );
            var status      = $this.next().find( '[id$="status"] option:selected' ).text();
            if( status ){
                var $parent = $this.parents( '.cmb2-wrap.form-table' );
                color = $parent.find('ul.statuses li .status:contains(' + status + ')').next().text();
                color = color ? color : 'transparent';
                $this.append( '<span class="status" style="background: ' + color + '">' + status + '</span>' );
            }
        });
    };

    /*
     * Displays the task end date in the title.
     */
    function displayEndDate( $group ) {
        $group.find( '.cmb-group-title' ).each( function() {
            var $this       = $( this );
            var date        = $this.next().find( '[id$="end_date"], [id$="due_date"]' ).val();
            //console.log(date);
            if( date ){
                $this.append( '<span class="dates">End: ' + date + '</span>' );
            }
        });
    };


    /*
     * Displays the currently selected progress in the title.
     * Only used on the Tasks group.
     */
    function displayProgress( $group ) {
        $group.find( '.cmb-repeatable-grouping' ).each( function() {
            var $this       = $( this );
            var progress    = $this.find('[id$="progress"]').val();
            progress = progress ? progress : '0';
            $this.find('.progress').remove();
            $this.append( '<span class="progress"><progress value="' + progress + '" max="100"></progress></span>' );
        });
    };

    /*
     * When adding a new row
     *
     */
    function addRow( $group ) {

        // if first item is hidden, then show it
        var first = $group.find( '.cmb-nested .cmb-row' )[0];
        if( $(first).is(":hidden") ) {
            $(first).show();
            $(first).removeClass( 'closed' );
            $(first).next().remove();
        }

        // enable all fields in this row and reset them
        var $row = $group.find( '.cmb-repeatable-grouping' ).last();
        $row.find( 'input, textarea, select' ).not(':button,:hidden').val("");
        $row.find( ':input' ).prop({ 'disabled': false, 'readonly': false });
        $row.find( '[data-user_assigned]' ).attr( 'data-user_assigned', '' );
        $row.find( '[data-user_created_by]' ).attr( 'data-user_created_by', '' );
        $row.find( '[data-avatar_assigned]' ).attr( 'data-avatar_assigned', '' );
        $row.find( '[data-avatar_created_by]' ).attr( 'data-avatar_created_by', '' );

        $group.find( '.cmb-add-row span' ).remove();

        window.wp.autosave.server.triggerSave();
    }

    /*
     * Adds a comment dynamically via AJAX
     */
    function addDiscussion() {

        var $group = $( document.getElementById( '_upstream_project_discussions' ) );

        $group
            .on('click', '#new_message', function( evt ) {
                evt.preventDefault();
                var content = window.tinyMCE.editors['_upstream_project_new_message'].getContent();
                var post_id = $('#post_ID').val();
                addPost( content, post_id );
            });

        addPost = function( content, post_id ) {

            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'upstream_admin_new_message',
                    content: content,
                    upstream_security: cmb2_l10.ajax_nonce,
                    post_id: post_id
                },
                success: function(response){
                    window.tinyMCE.editors['_upstream_project_new_message'].setContent('');
                    $(response).hide().prependTo(".admin-discussion").fadeIn("slow");

                    window.wp.autosave.server.triggerSave();
                }
            });

            return false;

        };

    }

    /*
     * Deletes a comment dynamically via AJAX
     */
    function deleteDiscussion() {

        var $group = $( document.getElementById( '_upstream_project_discussions' ) );

        $group
            .on('click', '#delete_message', function( evt ) {
                evt.preventDefault();
                var $this   = $( this );
                var item_id = $this.attr( 'data-id' );
                var post_id = $('#post_ID').val();
                deleteMessage( item_id, post_id );
            });

        deleteMessage = function( item_id, post_id ) {

            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'upstream_admin_delete_message',
                    item_id: item_id,
                    upstream_security: cmb2_l10.ajax_nonce,
                    post_id: post_id
                },
                success: function(response){
                    var $item = $group.find("[data-id='" + item_id + "']");
                    $item.parents('li').remove();

                    window.wp.autosave.server.triggerSave();
                }
            });

            return false;

        };

    }

    /*
     * Shows a clients users dynamically via AJAX
     */
    function showClientUsers() {

        var $box    = $( document.getElementById( '_upstream_project_details' ) );
        var $ul     = $box.find('.cmb2-id--upstream-project-client-users ul');

        getUsers = function( evt ) {

            var $this       = $( evt.target );
            var client_id   = $this.val();

            $.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    action: 'upstream_admin_ajax_get_clients_users',
                    client_id: client_id
                },
                success: function(response){
                    $ul.empty();
                    $ul.append(response.data);
                    noUsers();
                }
            });

            return false;

        };

        noUsers = function() {
            if( $ul.find('li').length == 0 ) {
                $ul.append('<li>No client selected</li>');
            }
        };

        noUsers();

        $box
            .on('keyup change', '#_upstream_project_client', function ( evt ) {
                getUsers( evt );
            });

    }


    // kick it all off
    initProject();
    addDiscussion();
    deleteDiscussion();
    showClientUsers();

})(jQuery);
