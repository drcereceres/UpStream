<?php
// Prevent direct access.
if ( ! defined('ABSPATH')) {
    exit;
}

use UpStream\Traits\Singleton;

/**
 * @since   1.15.0
 */
class UpStream_Ajax
{
    use Singleton;

    public function __construct()
    {
        $this->setHooks();
    }

    public function setHooks()
    {
        add_action('wp_ajax_upstream_ordering_update', [$this, 'orderingUpdate']);
    }

    public function orderingUpdate()
    {
        if ( ! isset($_POST['nonce'])) {
            $this->output('security_error');

            return;
        }

        if ( ! wp_verify_nonce($_POST['nonce'], 'upstream-nonce')) {
            $this->output('security_error');

            return;
        }

        if ( ! isset($_POST['column'])) {
            $this->output('column_not_found');

            return;
        }

        if ( ! isset($_POST['orderDir'])) {
            $this->output('order_dir_not_found');

            return;
        }

        if ( ! isset($_POST['tableId'])) {
            $this->output('table_id_not_found');

            return;
        }

        // Sanitize data.
        $tableId  = sanitize_text_field($_POST['tableId']);
        $column   = sanitize_text_field($_POST['column']);
        $orderDir = sanitize_text_field($_POST['orderDir']);

        if (empty($column) || empty($orderDir) || empty($tableId)) {
            $this->output('error');

            return;
        }

        \UpStream\Frontend\updateTableOrder($tableId, $column, $orderDir);

        $this->output('success');
    }

    public function output($return)
    {
        echo wp_json_encode($return);
        wp_die();
    }
}

