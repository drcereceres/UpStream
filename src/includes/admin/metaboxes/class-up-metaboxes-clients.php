<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

use \UpStream\Traits\Singleton;

// @todo
class UpStream_Metaboxes_Clients
{
    use Singleton;

    /**
     * Post type
     * @var string
     * @todo
     */
    public $type = 'client';

    /**
     * Post type
     * @var string
     * @todo
     */
    public $label = '';

    /**
     * Metabox prefix
     * @var string
     * @todo
     */
    public $prefix = '_upstream_client_';

    public function __construct()
    {
        $this->label = upstream_client_label();
        $this->label_plural = upstream_client_label_plural();
    }
}
