<?php

class Container extends \Pimple\Container {
    /**
     * Instance of the Pimple container
     */
    protected static $instance;

    public static function get_instance() {
        if ( empty( static::$instance ) ) {
            $instance = new self;

            // Define the services

            $instance['framework'] = function ( $c ) {
                return new AllediaFramework\Core( $c['PLUGIN_BASENAME'] );
            };

            $instance['PLUGIN_BASENAME'] = function ( $c ) {
                return plugin_basename( 'upstream/upstream.php' );
            };

            $instance['reviews'] = function ( $c ) {
                return new UpStream_Admin_Reviews( $c );
            };

            static::$instance = $instance;
        }

        return static::$instance;
    }
}
