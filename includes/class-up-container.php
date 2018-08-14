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
            $instance['PLUGIN_BASENAME'] = function ( $c ) {
                return plugin_basename( 'upstream/upstream.php' );
            };

            $instance['framework'] = function ( $c ) {
                return new Allex\Core( $c['PLUGIN_BASENAME'] );
            };

            if ( is_admin() ) {
                $instance['reviews'] = function ( $c ) {
                    return new UpStream_Admin_Reviews( $c );
                };
            }

            static::$instance = $instance;
        }

        return static::$instance;
    }
}
