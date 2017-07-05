<?php
// Prevent direct access.
if (!defined('ABSPATH')) exit;

use \UpStream\Traits\Singleton;
use \Cmb2Grid\Grid\Cmb2Grid;

// @todo
class UpStream_Metaboxes_Clients
{
    use Singleton;

    /**
     * @var string
     * @todo
     */
    protected static $postType = 'client';

    /**
     * @var string
     * @todo
     */
    protected static $postTypeLabelSingular = null;

    /**
     * @var string
     * @todo
     */
    protected static $postTypeLabelPlural = null;

    /**
     * @todo
     */
    protected static $prefix = '_upstream_client_';

    public function __construct()
    {
        self::$postTypeLabelSingular = upstream_client_label();
        self::$postTypeLabelPlural = upstream_client_label_plural();

        self::renderDetailsMetabox();
        self::renderLogoMetabox();
    }

    public static function renderDetailsMetabox()
    {
        $metabox = new_cmb2_box(array(
            'id'           => self::$prefix . 'details',
            'title'        => '<span class="dashicons dashicons-admin-generic"></span>' . __("Details", 'upstream'),
            'object_types' => array(self::$postType),
            'context'      => 'side',
            'priority'     => 'high'
        ));

        $phoneField = $metabox->add_field(array(
            'name' => __('Phone Number', 'upstream'),
            'id'   => self::$prefix . 'phone',
            'type' => 'text'
        ));

        $websiteField = $metabox->add_field(array(
            'name' => __('Website', 'upstream'),
            'id'   => self::$prefix . 'website',
            'type' => 'text_url'
        ));

        // @todo: may we should use tinymce?
        $addressField = $metabox->add_field(array(
            'name' => __('Address', 'upstream'),
            'id'   => self::$prefix . 'address',
            'type' => 'textarea_small'
        ));

        // @todo: description/notes field

        $metaboxGrid = new Cmb2Grid($metabox);
        $metaboxGridRow = $metaboxGrid->addRow(array($phoneField, $websiteField, $addressField));
        /*
        $metaboxGridRow->addColumns(array(
            array($phoneField, 'class' => 'col-md-4'),
            array($websiteField, 'class' => 'col-md-4')
        ));
        */
        //$metaboxGridRow->addColumns(array($phoneField, $websiteField, $addressField));
    }

    public static function renderLogoMetabox()
    {
        $metabox = new_cmb2_box(array(
            'id'           => self::$prefix . 'logo',
            'title'        => '<span class="dashicons dashicons-format-image"></span>' . __("Logo", 'upstream'),
            'object_types' => array(self::$postType),
            'context'      => 'side',
            'priority'     => 'core'
        ));

        $logoField = $metabox->add_field(array(
            'name' => __('Logo', 'upstream'),
            'id'   => self::$prefix . 'logo',
            'type' => 'file'
        ));


        $metaboxGrid = new Cmb2Grid($metabox);
        $metaboxGridRow = $metaboxGrid->addRow(array($logoField));
    }
}
