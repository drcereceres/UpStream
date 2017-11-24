<?php
namespace UpStream;

use UpStream\Traits\Singleton;
use UpStream\Comment;

// @todo: doc everything

class Comments
{
    use Singleton;

    private static $namespace;

    public function __construct()
    {
        self::$namespace = get_class(self::$instance);

        $this->attachHooks();
    }

    private function attachHooks()
    {
        add_action('wp_ajax_upstream:project.add_comment', array(self::$namespace, 'storeComment'));
        add_action('wp_ajax_upstream:project.add_comment_reply', array($this, 'storeCommentReply'));
        add_action('wp_ajax_upstream:project.trash_comment', array(self::$namespace, 'trashComment'));
        add_action('wp_ajax_upstream:project.unapprove_comment', array(self::$namespace, 'unapproveComment'));
        add_action('wp_ajax_upstream:project.approve_comment', array(self::$namespace, 'approveComment'));
    }

    public static function storeComment()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'error'   => null
        );

        try {
            // Check if the request payload is potentially invalid.
            if (
                !defined('DOING_AJAX')
                || !DOING_AJAX
                || empty($_POST)
                || !isset($_POST['nonce'])
                || !isset($_POST['project_id'])
                || !isset($_POST['item_type'])
                || !isset($_POST['content'])
            ) {
                throw new \Exception(__("Invalid request.", 'upstream'));
            }

            // Prepare data to verify nonce.
            $commentTargetItemType = strtolower($_POST['item_type']);
            if ($commentTargetItemType !== 'project') {
                if (
                    !isset($_POST['item_index'])
                    || !is_numeric($_POST['item_index'])
                    || !isset($_POST['item_id'])
                    || empty($_POST['item_id'])
                ) {
                    throw new \Exception(__("Invalid request.", 'upstream'));
                }

                $item_id = $_POST['item_id'];

                $nonceIdentifier = 'upstream:project.' . $commentTargetItemType . 's.add_comment:' . $_POST['item_index'];
            } else {
                $nonceIdentifier = 'upstream:project.add_comment';
            }

            // Verify nonce.
            if (!wp_verify_nonce($_POST['nonce'], $nonceIdentifier)) {
                throw new \Exception(__("Invalid request.", 'upstream'));
            }

            // Check if the user has enough permissions to insert a new comment.
            if (!upstream_admin_permissions('publish_project_discussion')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            // Check if the project exists.
            $project_id = (int)$_POST['project_id'];
            if ($project_id <= 0) {
                throw new \Exception(__("Invalid Project.", 'upstream'));
            }

            // Check if commenting is disabled on the given project.
            if (upstream_are_comments_disabled($project_id)) {
                throw new \Exception(__("Commenting is disabled on this project.", 'upstream'));
            }

            $user_id = get_current_user_id();

            $comment = new Comment($_POST['content'], $project_id, $user_id);

            $comment->created_by->ip = preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']);
            $comment->created_by->agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

            $comment->save();

            update_comment_meta($comment->id, 'type', $commentTargetItemType);

            if ($commentTargetItemType !== "project") {
                update_comment_meta($comment->id, 'id', $item_id);
            }

            $useAdminLayout = is_admin();

            $response['comment_html'] = $comment->render(true, $useAdminLayout);
            $response['success'] = true;
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        wp_send_json($response);
    }

    /**
     * AJAX endpoint that adds a new comment reply to a given project.
     *
     * @since   @todo
     * @static
     */
    public static function storeCommentReply()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'error'   => null
        );

        try {
            // Check if the request payload is potentially invalid.
            if (
                !defined('DOING_AJAX')
                || !DOING_AJAX
                || empty($_POST)
                || !isset($_POST['nonce'])
                || !isset($_POST['project_id'])
                || !isset($_POST['item_type'])
                || !in_array($_POST['item_type'], array('project', 'milestone', 'task', 'bug'))
                || !isset($_POST['content'])
                || !isset($_POST['parent_id'])
                || !is_numeric($_POST['parent_id'])
                || !wp_verify_nonce($_POST['nonce'], 'upstream:project.add_comment_reply:' . $_POST['parent_id'])
            ) {
                throw new \Exception(__("Invalid request.", 'upstream'));
            }

            $commentTargetItemType = strtolower($_POST['item_type']);
            if ($commentTargetItemType !== 'project') {
                if (
                    !isset($_POST['item_id'])
                    || empty($_POST['item_id'])
                ) {
                    throw new \Exception(__("Invalid request.", 'upstream'));
                }
            }

            // Check if the user has enough permissions to insert a new comment.
            if (!upstream_admin_permissions('publish_project_discussion')) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            // Check if the project exists.
            $project_id = (int)$_POST['project_id'];
            if ($project_id <= 0) {
                throw new \Exception(__("Invalid Project.", 'upstream'));
            }

            // Check if commenting is disabled on the given project.
            if (upstream_are_comments_disabled($project_id)) {
                throw new \Exception(__("Commenting is disabled on this project.", 'upstream'));
            }

            $user_id = get_current_user_id();

            $comment = new Comment($_POST['content'], $project_id, $user_id);
            $comment->parent_id = (int)$_POST['parent_id'];
            $comment->created_by->ip = preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']);
            $comment->created_by->agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

            $comment->save();

            update_comment_meta($comment->id, 'type', $commentTargetItemType);

            if ($commentTargetItemType !== "project") {
                update_comment_meta($comment->id, 'id', $_POST['item_id']);
            }

            $useAdminLayout = is_admin();

            $parent = get_comment($comment->parent_id);

            $commentsCache = array(
                $parent->comment_ID => json_decode(json_encode(array(
                    'created_by' => array(
                        'name' => $parent->comment_author
                    )
                )))
            );

            $response['comment_html'] = $comment->render(true, $useAdminLayout, $commentsCache);

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        wp_send_json($response);
    }

    /**
     * AJAX endpoint that removes a comment.
     *
     * @since   @todo
     * @static
     */
    public static function trashComment()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'error'   => null
        );

        try {
            // Check if the request payload is potentially invalid.
            if (
                !defined('DOING_AJAX')
                || !DOING_AJAX
                || empty($_POST)
                || !isset($_POST['nonce'])
                || !isset($_POST['project_id'])
                || !isset($_POST['comment_id'])
                || !wp_verify_nonce($_POST['nonce'], 'upstream:project.trash_comment:' . $_POST['comment_id'])
            ) {
                throw new \Exception(__("Invalid request.", 'upstream'));
            }

            // Check if the project exists.
            $project_id = (int)$_POST['project_id'];
            if ($project_id <= 0) {
                throw new \Exception(__("Invalid Project.", 'upstream'));
            }

            // Check if the Discussion/Comments section is disabled for the current project.
            if (upstream_are_comments_disabled($project_id)) {
                throw new \Exception(__("Comments are disabled for this project.", 'upstream'));
            }

            // Check if the parent comment exists.
            $comment_id = (int)$_POST['comment_id'];
            $comment = get_comment($comment_id);

            if (empty($comment)
                // Check if the comment belongs to that project.
                || (isset($comment->comment_post_ID)
                    && (int)$comment->comment_post_ID !== $project_id
                )
            ) {
                throw new \Exception(_x('Comment not found.', 'Removing a comment in projects', 'upstream'));
            }

            $user_id = (int)get_current_user_id();

            if (!upstream_admin_permissions('delete_project_discussion')
                && !current_user_can('moderate_comments')
                && (int)$comment->user_id !== $user_id
            ) {
                throw new \Exception(__("You're not allowed to do this.", 'upstream'));
            }

            $success = wp_trash_comment($comment);
            if (!$success) {
                throw new \Exception(__("It wasn't possible to delete this comment.", 'upstream'));
            }

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        wp_send_json($response);
    }

    /**
     * Either approves/unapproves a given comment.
     *
     * @since   @todo
     * @access  private
     * @static
     *
     * @throws  \Exception when something went wrong or failed on validations.
     *
     * @param   int     $comment_id         Comment ID being edited.
     * @param   bool    $newApprovalStatus  Either the comment will be approved or not.
     *
     * @param   Comment $comment
     */
    private static function toggleCommentApprovalStatus($comment_id, $isApproved)
    {
        // Check if the request payload is potentially invalid.
        if (
            !defined('DOING_AJAX')
            || !DOING_AJAX
            || empty($_POST)
            || !isset($_POST['nonce'])
            || !isset($_POST['project_id'])
            || !isset($_POST['comment_id'])
            || !wp_verify_nonce($_POST['nonce'], 'upstream:project.' . ($isApproved ? 'approve_comment' : 'unapprove_comment') . ':' . $_POST['comment_id'])
        ) {
            throw new \Exception(__('Invalid request.', 'upstream'));
        }

        // Check if the user has enough permissions to do this.
        if (!current_user_can('moderate_comments')) {
            throw new \Exception(__("You're not allowed to do this.", 'upstream'));
        }

        // Check if the project potentially exists.
        $project_id = (int)$_POST['project_id'];
        if ($project_id <= 0) {
            throw new \Exception(sprintf(__('Invalid "%s" parameter.', 'upstream'), 'project_id'));
        }

        // Check if the Discussion/Comments section is disabled for the current project.
        if (upstream_are_comments_disabled($project_id)) {
            throw new \Exception(__('Comments are disabled for this project.', 'upstream'));
        }

        $comment = Comment::load($_POST['comment_id']);
        if (!($comment instanceof Comment)) {
            throw new \Exception(__('Comment not found.', 'upstream'));
        }

        $success = (bool)$isApproved ? $comment->approve() : $comment->unapprove();
        if (!$success) {
            throw new \Exception(__('Unable to save the data into database.', 'upstream'));
        }

        return $comment;
    }

    /**
     * AJAX endpoint that unapproves a comment.
     *
     * @since   @todo
     * @static
     */
    public static function unapproveComment()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'error'   => null,
        );

        try {
            $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
            $comment = self::toggleCommentApprovalStatus($comment_id, false);

            $comments = array();
            if ($comment->parent_id > 0) {
                $parentComment = get_comment($comment->parent_id);
                if (is_numeric($parentComment->comment_approved)) {
                    if ((bool)$parentComment->comment_approved) {
                        $comments = array(
                            $comment->parent_id => json_decode(json_encode(array(
                                'created_by' => array(
                                    'name' => $parentComment->comment_author
                                )
                            )))
                        );
                    } else {
                        $user = wp_get_current_user();
                        $userHasAdminCapabilities = isUserEitherManagerOrAdmin($user);
                        $userCanModerateComments = !$userHasAdminCapabilities ? user_can($user, 'moderate_comments') : true;

                        if ($userCanModerateComments) {
                            $comments = array(
                                $comment->parent_id => json_decode(json_encode(array(
                                    'created_by' => array(
                                        'name' => $parentComment->comment_author
                                    )
                                )))
                            );
                        }
                    }
                }
                unset($parentComment);
            }

            $useAdminLayout = !isset($_POST['teeny']) ? true : (bool)$_POST['teeny'];

            $response['comment_html'] = $comment->render(true, $useAdminLayout, $comments);

            wp_new_comment_notify_moderator($comment->id);

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        wp_send_json($response);
    }

    /**
     * AJAX endpoint that approves a comment.
     *
     * @since   @todo
     * @static
     */
    public static function approveComment()
    {
        header('Content-Type: application/json');

        $response = array(
            'success' => false,
            'error'   => null,
        );

        try {
            $comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
            $comment = self::toggleCommentApprovalStatus($comment_id, true);

            $comments = array();
            if ($comment->parent_id > 0) {
                $parentComment = get_comment($comment->parent_id);
                if (is_numeric($parentComment->comment_approved)) {
                    if ((bool)$parentComment->comment_approved) {
                        $comments = array(
                            $comment->parent_id => json_decode(json_encode(array(
                                'created_by' => array(
                                    'name' => $parentComment->comment_author
                                )
                            )))
                        );
                    } else {
                        $user = wp_get_current_user();
                        $userHasAdminCapabilities = isUserEitherManagerOrAdmin($user);
                        $userCanModerateComments = !$userHasAdminCapabilities ? user_can($user, 'moderate_comments') : true;

                        if ($userCanModerateComments) {
                            $comments = array(
                                $comment->parent_id => json_decode(json_encode(array(
                                    'created_by' => array(
                                        'name' => $parentComment->comment_author
                                    )
                                )))
                            );
                        }
                    }
                }
                unset($parentComment);
            }

            $useAdminLayout = !isset($_POST['teeny']) ? true : (bool)$_POST['teeny'];

            $response['comment_html'] = $comment->render(true, $useAdminLayout, $comments);

            $response['success'] = true;
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        wp_send_json($response);
    }
}