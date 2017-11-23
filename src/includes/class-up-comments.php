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
    }

    static public function storeComment()
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
                // @todo: Change to "Invalid request.".
                throw new \Exception(__("Invalid nonce.", 'upstream'));
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

            $comment = new Comment($_POST['content'], $project_id);

            $comment->created_by->ip = preg_replace('/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR']);
            $comment->created_by->agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;

            $comment->save();

            update_comment_meta($comment->id, 'type', $commentTargetItemType);

            if ($commentTargetItemType !== "project") {
                update_comment_meta($comment->id, 'id', $item_id);
            }

            $useAdminLayout = is_admin();

            $response['data'] = $comment;
            $response['comment_html'] = $comment->render(true, $useAdminLayout);
            $response['success'] = true;
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        wp_send_json($response);
    }
}