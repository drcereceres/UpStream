<?php

namespace AllediaFramework;

class Assets extends AbstractService {
    /**
     * Enqueue styles for the admin UI.
     */
    public function admin_enqueue_styles() {
        wp_enqueue_style(
            'alledia-framework',
            $this->container['ASSETS_BASE_URL'] . '/css/admin.css',
            [],
            $this->container['VERSION']
        );
    }

    /**
     * Enqueue scripts for the admin UI.
     */
    public function admin_enqueue_scripts() {
        wp_enqueue_script(
            'alledia-framework',
            $this->container['ASSETS_BASE_URL'] . '/js/admin.js',
            [ 'jquery' ],
            $this->container['VERSION']
        );
    }
}
