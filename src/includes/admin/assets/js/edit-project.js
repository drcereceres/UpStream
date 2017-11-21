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
                .on('click button.cmb-remove-group-row', function(evt) {
                    if ($(evt.target).hasClass('cmb-remove-group-row')) {
                        $($group).each(function(i, e) {
                            var e = $(e);
                            var e_id = e.attr('id');

                            //resetGroup(e);

                            $(groups).each( function(i, e) {
                                var $g = $box.find(e);

                                resetGroup($g);

                                if ($g.attr('id') === '_upstream_project_tasks' || $g.attr('id') === '_upstream_project_bugs') {
                                    displayEndDate($g);
                                }
                            });

                            var $m = $('#_upstream_project_milestones');
                            displayMilestoneProgress($m);
                            displayMilestoneIcon($m);

                            var $t = $('#_upstream_project_tasks');
                            displayStatusColor($t);
                            displayMilestoneIcon($t);
                            displayProgress($t);

                            displayStatusColor($('#_upstream_project_bugs'));
                        });
                    }
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
                $this.find( '.av-created' ).html( '<img title="Created by: ' + user_created + '" src="' + av_created + '" height="25" width="25" />' ).show();
            } else {
                $this.find( '.av-created' ).hide();
            }

            if( av_assigned && $this.attr( 'id' ) != '_upstream_project_files' ) {
                $this.find( '.av-assigned' ).html( '<img title="Assigned to: ' + user_assigned + '" src="' + av_assigned + '" height="25" width="25" />' ).show();
            } else {
                $this.find( '.av-assigned' ).hide();
            }
        });
    };


    /*
     * Displays the title in the title.
     * Used in all groups.
     */
    function replaceTitles( $group ) {

        if( $group && $group.attr( 'id' ) == '_upstream_project_milestones' ) {

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

    var emptyClickEvent = function(e) {
        e.preventDefault();
        e.stopPropagation();
    };

    $(document).ready(function() {
        $('.postbox.cmb-row.cmb-repeatable-grouping[data-iterator] button.cmb-remove-group-row').each(function() {
            var self = $(this);

            if (self.attr('disabled') === 'disabled') {
                self.attr('data-disabled', 'disabled');
                self.on('click', emptyClickEvent);
            }
        });

        $('div[data-groupid]').on('click', 'button.cmb-remove-group-row', function(e) {
            var self = $(this);
            var groupWrapper = $(self.parents('div[data-groupid].cmb-nested.cmb-field-list.cmb-repeatable-group'));

            setTimeout(function() {
                $('.postbox.cmb-row.cmb-repeatable-grouping .cmb-remove-group-row[data-disabled]', groupWrapper).attr('disabled', 'disabled');
            }, 50);
        });
    });

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

        $('.cmb-remove-group-row[data-disabled]', $row).attr('data-disabled', null);
        $('.cmb-remove-group-row[data-disabled]', $group).each(function() {
            $(this).attr('disabled', 'disabled');
        });
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
                    client_id: client_id,
                    project_id: $('#post_ID').val()
                },
                success: function(response){
                    $ul.empty();

                    if (typeof response.data === "string" && response.data) {
                        $ul.append(response.data);
                    } else if (response.data.msg) {
                        $ul.append('<li>'+ response.data.msg +'</li>');
                    }
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
    showClientUsers();

    $('form#post').on('submit', function(e) {
        var tasksWrapper = $('#_upstream_project_tasks_repeat');
        var tasks = $('.postbox.cmb-row.cmb-repeatable-grouping', tasksWrapper);
        for (var t = 0; t < tasks.length; t++) {
            var taskWrapper = $(tasks[t]);
            if (taskWrapper.css('display') !== 'none') {
                var taskTitleField = $('input.task-title', taskWrapper);
                if (taskTitleField.val().trim().length === 0) {
                    taskTitleField.addClass('has-error');

                    $(taskTitleField.parents('.postbox.cmb-row.cmb-repeatable-grouping')).removeClass('closed');
                    $(taskTitleField.parents('.postbox.cmb2-postbox')).removeClass('closed');

                    e.preventDefault();
                    e.stopPropagation();

                    taskTitleField.focus();

                    return false;
                }
            }
        }

        $('input.task-title.has-error', tasksWrapper).removeClass('has-error');

        var wrapperMilestones = $('#_upstream_project_milestones_repeat, #_upstream_project_tasks_repeat, #_upstream_project_bugs_repeat');
        if (wrapperMilestones.length) {
            $('.postbox.cmb-row.cmb-repeatable-grouping .cmb-row *:disabled', wrapperMilestones).filter(function() {
                var self = $(this);
                if (['INPUT', 'SELECT', 'TEXTAREA'].indexOf(self.prop('tagName')) >= 0) {
                    $(this).prop({
                        'disabled': "",
                        'data-disabled': "",
                        'readonly': ""
                    });
                }
            });
        }
    });

    $('.upstream-filter').on('change', function onFilterChangeCallback(e) {
        var self = $(this);
        var targetColumn = self.data('column');

        var validColumns = ['assigned_to', 'milestone', 'status', 'severity'];
        if (validColumns.indexOf(targetColumn) >= 0) {
            var sectionWrapper = self.parents('.cmb2-metabox.cmb-field-list');
            var itemsListWrapper = $('.cmb-row.cmb-repeat-group-wrap.cmb-type-group.cmb-repeat', sectionWrapper);

            $('.no-items', itemsListWrapper).remove();

            var rows = $('.postbox.cmb-row[data-iterator]', itemsListWrapper);
            if (rows.length) {
                var newValue = this.value;
                if (newValue && newValue !== '- Show All -' && newValue !== '- Show Everyone -') {
                    var rowsLastRowIndex = rows.length - 1;
                    var itemsFound = 0;
                    rows.each(function(itemWrapperIndex, itemWrapper) {
                        var itemValue;
                        if (targetColumn === 'milestone') {
                            itemValue = $('select[name$="['+ targetColumn +']"] option:selected', itemWrapper).text();
                        } else {
                            itemValue = $('select[name$="['+ targetColumn +']"]', itemWrapper).val();
                        }

                        var displayProp = 'none';

                        if (itemValue === newValue) {
                            itemsFound++;
                            displayProp = 'block';
                        }

                        $(itemWrapper).css('display', displayProp);

                        if (itemWrapperIndex === rowsLastRowIndex) {
                            if (itemsFound === 0) {
                                var noItemsFoundWrapperHtml = $('<div class="postbox cmb-row cmb-repeatable-grouping no-items"><p>'+ self.data('no-items-found-message') +'</p></div>');
                                noItemsFoundWrapperHtml.insertBefore($('.cmb-row:not(.postbox):last-child', itemsListWrapper));
                            }
                        }
                    });
                } else {
                    rows.css('display', 'block');
                }
            }
        }
    });

    var titleHasFocus = false;
    $(document)
        .on( 'before-autosave.update-post-slug', function() {
            titleHasFocus = document.activeElement && document.activeElement.id === 'title';
        })
        .on('after-autosave.update-post-slug', function( e, data ) {
            if ( ! $('#edit-slug-box > *').length && ! titleHasFocus ) {
                $.post( ajaxurl, {
                        action: 'sample-permalink',
                        post_id: $('#post_ID').val(),
                        new_title: $('#title').val(),
                        samplepermalinknonce: $('#samplepermalinknonce').val()
                    },
                    function( data ) {
                        if ( data != '-1' ) {
                            $('#edit-slug-box').html(data);
                        }
                    }
                );
            }
        });
})(jQuery);

(function(window, document, $, upstream_project, undefined) {
  $(document).ready(function() {
    var newMessageLabel = $('#_upstream_project_discussions label[for="_upstream_project_new_message"]');
    var newMessageLabelText = newMessageLabel.text();

    function getCommentEditor() {
      var TinyMceSingleton = window.tinyMCE ? window.tinyMCE : (window.tinymce ? window.tinymce : null);
      var theEditor = TinyMceSingleton.get('_upstream_project_new_message');

      return theEditor;
    }

    function getCommentEditorTextarea() {
      return $('#_upstream_project_new_message');
    }

    function disableCommentArea() {
      var theEditor = getCommentEditor();

      if (theEditor) {
        theEditor.getDoc().designMode = 'off';

        var theEditorBody = theEditor.getBody();
        theEditorBody.setAttribute('contenteditable', 'false');
        theEditorBody.setAttribute('readonly', '1');
        theEditorBody.style.background = "#ECF0F1";
        theEditorBody.style.cursor = "progress";
      }

      var theEditorTextarea = getCommentEditorTextarea();
      theEditorTextarea.attr('disabled', 'disabled');
      theEditorTextarea.addClass('disabled');

      $('#wp-_upstream_project_new_message-wrap').css('cursor', 'progress');
      $('#insert-media-button').attr('disabled', 'disabled');
      $('button[data-action^="comment."]').attr('disabled', 'disabled');
      $('button[data-action^="comments."]').attr('disabled', 'disabled');
    }

    function enableCommentArea() {
      var theEditor = getCommentEditor();

      if (theEditor) {
        theEditor.getDoc().designMode = 'on';

        var theEditorBody = theEditor.getBody();
        theEditorBody.setAttribute('contenteditable', 'true');
        theEditorBody.setAttribute('readonly', '0');
        theEditorBody.style.background = null;
        theEditorBody.style.cursor = null;
      }

      var theEditorTextarea = getCommentEditorTextarea();
      theEditorTextarea.attr('disabled', null);
      theEditorTextarea.removeClass('disabled');

      $('#wp-_upstream_project_new_message-wrap').css('cursor', '');
      $('#insert-media-button').attr('disabled', null);
      $('button[data-action^="comment."]').attr('disabled', null);
      $('button[data-action^="comments."]').attr('disabled', null);
    }

    function resetCommentEditorContent() {
      var theEditor = getCommentEditor();
      if (theEditor) {
        theEditor.setContent('');
      }

      var theEditorTextarea = getCommentEditorTextarea();
      theEditorTextarea.val('');
    }

    function appendCommentHtmlToDiscussion(commentHtml) {
      var comment = $(commentHtml);
      comment.hide();

      commentHtml = comment.html()
        .replace(/\\'/g, "'")
        .replace(/\\"/g, '"');

      comment.html(commentHtml);

      comment.prependTo('.c-discussion');

      $('.c-discussion').animate({
        scrollTop: 0
      }, function() {
        comment.fadeIn();
      });
    }

    function replyCancelButtonClickCallback(e) {
      if (typeof e !== "undefined" && e) {
        e.preventDefault();
      }

      var self = $(this);

      $('label[for="_upstream_project_new_message"]').text(upstream_project.l.LB_ADD_NEW_COMMENT);
      $('#_upstream_project_discussions .button.u-to-be-removed').remove();
      $('#_upstream_project_discussions .button[data-action="comments.add_comment"]').show();

      $('.o-comment[data-id]').removeClass('is-mouse-over is-disabled is-being-replied');

      resetCommentEditorContent();
    }

    function replySendButtonCallback(e) {
      e.preventDefault();

      var theEditor = getCommentEditor();
      var commentContentHtml = null;
      var commentContentText = null;

      var isEditorInVisualMode = theEditor ? !theEditor.isHidden() : false;
      if (isEditorInVisualMode) {
        commentContentHtml = (theEditor.getContent() || "").trim();
        commentContentText = (theEditor.getContent({ format: 'text' }) || "").trim();

        if (commentContentText.length === 0 && commentContentHtml.length === 0) {
          theEditor.execCommand('mceFocus', false);
          return;
        }
      } else {
        theEditor = getCommentEditorTextarea();
        commentContentHtml = theEditor.val().trim();
        commentContentText = commentContentHtml;

        if (commentContentText.length === 0) {
          theEditor.focus();
          return;
        }
      }

      var self = $(this);

      var errorCallback = function() {
        self.text(upstream_project.l.LB_SEND_REPLY);
        $('#_upstream_project_discussions .button.u-to-be-removed').attr('disabled', null);
        $('#_upstream_project_discussions .o-comment.is-being-replied a[data-action="comment.reply"]').text(upstream_project.l.LB_REPLY);
      };

      $.ajax({
        type: 'POST',
        url : ajaxurl,
        data: {
          action    : 'upstream:project.discussion.add_comment_reply',
          nonce     : self.data('nonce'),
          project_id: $('#post_ID').val(),
          parent_id : self.attr('data-id'),
          content   : commentContentHtml
        },
        beforeSend: function() {
          disableCommentArea();
          self.text(upstream_project.l.LB_REPLYING);
          $('#_upstream_project_discussions .button.u-to-be-removed').attr('disabled', 'disabled');
          $('#_upstream_project_discussions .o-comment.is-being-replied a[data-action="comment.reply"]').text(upstream_project.l.LB_REPLYING);
        },
        success: function(response) {
          if (response.error) {
            errorCallback();
            console.error(response.error);
            alert(response.error);
          } else {
            if (!response.success) {
              errorCallback();
              console.error('Something went wrong.');
            } else {
              resetCommentEditorContent();
              replyCancelButtonClickCallback();

              appendCommentHtmlToDiscussion(response.comment_html);

              $('#_upstream_project_discussions .o-comment.is-being-replied a[data-action="comment.reply"]').text(upstream_project.l.LB_REPLY);
              $('#_upstream_project_discussions .o-comment').removeClass('is-disabled is-mouse-over is-being-replied');
            }
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          errorCallback();

          var response = {
            text_status: textStatus,
            errorThrown: errorThrown
          };

          console.error(response);
        },
        complete: function() {
          enableCommentArea();
        }
      });
    }

    function setFocus() {
      var theEditor = getCommentEditor();
      var isEditorInVisualMode = theEditor ? !theEditor.isHidden() : false;
      if (isEditorInVisualMode) {
        theEditor.execCommand('mceFocus', false);
      } else {
        theEditor = getCommentEditorTextarea();
        theEditor.focus();
      }
    }

    $('label[for="_upstream_project_new_message"]').on('click', setFocus);

    $('.c-discussion').on('click', '.o-comment[data-id] a[data-action="comment.reply"]', function(e) {
      e.preventDefault();

      var self = $(this);
      var commentWrapper = $(self.parents('.o-comment[data-id]'));
      var comment_id = commentWrapper.attr('data-id');

      commentWrapper.addClass('is-mouse-over is-being-replied');

      $('.o-comment[data-id!="'+ comment_id +'"]').addClass('is-disabled');

      var addCommentBtn = $('#_upstream_project_discussions .button[data-action="comments.add_comment"]');
      addCommentBtn.hide();
      var controlsWrapper = $(addCommentBtn.parent());

      $('#_upstream_project_discussions .button.u-to-be-removed').remove();

      var cancelButton = $('<button></button>', {
        type : 'button',
        class: 'button button-secondary u-to-be-removed'
      })
        .text(upstream_project.l.LB_CANCEL);
      cancelButton.on('click', replyCancelButtonClickCallback);
      controlsWrapper.append(cancelButton);

      var sendButton = $('<button></button>', {
        type     : 'button',
        class    : 'button button-primary u-to-be-removed',
        'data-id': comment_id,
        'data-nonce': self.data('nonce')
      })
        .text(upstream_project.l.LB_SEND_REPLY)
        .css('margin-left', '10px');
      sendButton.on('click', replySendButtonCallback);
      controlsWrapper.append(sendButton);

      resetCommentEditorContent();

      $('label[for="_upstream_project_new_message"]').text(upstream_project.l.LB_ADD_NEW_REPLY);

      var finished = false;
      $('html, body').animate({
        scrollTop: $('#_upstream_project_discussions').offset().top
      }, {
        complete: function(e) {
          if (!finished) {
            setFocus();
            finished = true;
          }
        }
      });
    });

    $('.c-discussion').on('click', '.o-comment[data-id] a[data-action="comment.trash"]', function(e) {
      e.preventDefault();

      var self = $(this);

      var comment = $(self.parents('.o-comment[data-id]'));
      if (!comment.length) {
        console.error('Comment wrapper not found.');
        return;
      }

      if (!confirm(upstream_project.l.MSG_ARE_YOU_SURE)) return;

      var errorCallback = function() {
        comment.removeClass('is-loading is-mouse-over is-being-removed');
        self.text(upstream_project.l.LB_DELETE);
      };

      $.ajax({
        type: 'POST',
        url : ajaxurl,
        data: {
          action    : 'upstream:project.discussion.trash_comment',
          nonce     : self.data('nonce'),
          project_id: $('#post_ID').val(),
          comment_id: comment.attr('data-id')
        },
        beforeSend: function() {
          comment.addClass('is-loading is-mouse-over is-being-removed');
          self.text(upstream_project.l.LB_DELETING);
        },
        success: function(response) {
          if (response.error) {
            errorCallback();

            console.error(response.error);
            alert(response.error);
          } else {
            if (!response.success) {
              console.error('Something went wrong.');

              errorCallback();
            } else {
              comment.css('background-color', '#E74C3C');

              comment.slideUp({
                complete: function() {
                  comment.remove();
                }
              });
            }
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          errorCallback();

          var response = {
            text_status: textStatus,
            errorThrown: errorThrown
          };

          console.error(response);
        }
      });
    });

    $('#cmb2-metabox-_upstream_project_discussions .cmb2-id--upstream-project-new-message').on('click', '.button[data-action="comments.add_comment"]', function(e) {
      e.preventDefault();

      var theEditor = getCommentEditor();
      var commentContentHtml = null;
      var commentContentText = null;

      var isEditorInVisualMode = theEditor ? !theEditor.isHidden() : false;
      if (isEditorInVisualMode) {
        commentContentHtml = (theEditor.getContent() || "").trim();
        commentContentText = (theEditor.getContent({ format: 'text' }) || "").trim();

        if (commentContentText.length === 0 && commentContentHtml.length === 0) {
          theEditor.execCommand('mceFocus', false);
          return;
        }
      } else {
        theEditor = getCommentEditorTextarea();
        commentContentHtml = theEditor.val().trim();
        commentContentText = commentContentHtml;

        if (commentContentText.length === 0) {
          theEditor.focus();
          return;
        }
      }

      var self = $(this);

      $.ajax({
        type: 'POST',
        url : ajaxurl,
        data: {
          action    : 'upstream:project.discussion.add_comment',
          nonce     : self.data('nonce'),
          project_id: $('#post_ID').val(),
          content   : commentContentHtml
        },
        beforeSend: function() {
          disableCommentArea();
          self.text(self.attr('data-label-active'));
        },
        success: function(response) {
          console.log(response);
          if (response.error) {
            console.error(response.error);
            alert(response.error);
          } else {
            if (!response.success) {
              console.error('Something went wrong.');
            } else {
              resetCommentEditorContent();

              appendCommentHtmlToDiscussion(response.comment_html);
            }
          }
        },
        error: function() {},
        complete: function() {
          enableCommentArea();
          self.text(self.attr('data-label'));
        }
      });
    });

    $('.c-discussion').on('click', '.o-comment[data-id] a[data-action="comment.unapprove"]', function(e) {
      e.preventDefault();

      var self = $(this);

      var comment = $(self.parents('.o-comment[data-id]'));
      if (!comment.length) {
        console.error('Comment wrapper not found.');
        return;
      }

      var errorCallback = function() {
        comment
          .removeClass('is-loading is-mouse-over is-being-unapproved')
          .css('background-color', '');
        self.text(upstream_project.l.LB_UNAPPROVE);
      };

      $.ajax({
        type: 'POST',
        url : ajaxurl,
        data: {
          action    : 'upstream:project.discussion.unapprove_comment',
          nonce     : self.data('nonce'),
          project_id: $('#post_ID').val(),
          comment_id: comment.attr('data-id')
        },
        beforeSend: function() {
          comment.addClass('is-loading is-mouse-over is-being-unapproved');
          self.text(upstream_project.l.LB_UNAPPROVING);
        },
        success: function(response) {
          if (response.error) {
            errorCallback();
            console.error(response.error);
            alert(response.error);
          } else {
            if (!response.success) {
              errorCallback();
              console.error('Something went wrong.');
            } else {
              comment.replaceWith($(response.comment_html));
            }
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          errorCallback();

          var response = {
            text_status: textStatus,
            errorThrown: errorThrown
          };

          console.error(response);
        }
      });
    });

    $('.c-discussion').on('click', '.o-comment[data-id] a[data-action="comment.approve"]', function(e) {
      e.preventDefault();

      var self = $(this);

      var comment = $(self.parents('.o-comment[data-id]'));
      if (!comment.length) {
        console.error('Comment wrapper not found.');
        return;
      }

      var errorCallback = function() {
        comment
          .removeClass('is-loading is-mouse-over is-being-approved')
          .css('background-color', '');
        self.text(upstream_project.l.LB_APPROVE);
      };

      $.ajax({
        type: 'POST',
        url : ajaxurl,
        data: {
          action    : 'upstream:project.discussion.approve_comment',
          nonce     : self.data('nonce'),
          project_id: $('#post_ID').val(),
          comment_id: comment.attr('data-id')
        },
        beforeSend: function() {
          comment.addClass('is-loading is-mouse-over is-being-approved');
          self.text(upstream_project.l.LB_APPROVING);
        },
        success: function(response) {
          if (response.error) {
            errorCallback();
            console.error(response.error);
            alert(response.error);
          } else {
            if (!response.success) {
              errorCallback();
              console.error('Something went wrong.');
            } else {
              comment.replaceWith($(response.comment_html));
            }
          }
        },
        error: function(jqXHR, textStatus, errorThrown) {
          errorCallback();

          var response = {
            text_status: textStatus,
            errorThrown: errorThrown
          };

          console.error(response);
        }
      });
    });

    // @todo: might have some bugs.
    $('.c-discussion').on('click', '.o-comment[data-id] a[data-action="comment.go_to_reply"]', function(e) {
      e.preventDefault();

      var targetComment = $($(this).attr('href'));
      if (!targetComment.length) {
        console.error('Comment not found.');
        return;
      }

      var wrapper = $(targetComment.parents('.c-discussion'));
      var wrapperOffset = wrapper.offset();

      if (!wrapperOffset) return;

      var offset = targetComment.offset() || null;
      if (!offset) return;

      var targetCommentOffsetTop = offset.top - wrapperOffset.top;

      wrapper.animate({
        scrollTop: targetCommentOffsetTop,
      }, function() {
        targetComment.addClass('s-highlighted');
        setTimeout(function() {
          targetComment.removeClass('s-highlighted');
        }, 750);
      });
    });

    // @todo: make sure the selectors are scopped within #_upstream_project_discussions
  });
})(window, window.document, jQuery, upstream_project || {});
