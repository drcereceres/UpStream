<?php

namespace AllediaFramework;

class Container extends \Pimple\Container {
    public function __construct( array $values = [] ) {
        parent::__construct( $values );

        $this['VERSION'] = function ( $c ) {
            return '0.2.0';
        };

        $this['PLUGIN_BASENAME'] = function ( $c ) use ( $values ) {
            return $values['PLUGIN_BASENAME'];
        };

        $this['TWIG_PATH'] = function ( $c ) {
            return __DIR__ . '/twig';
        };

        $this['ASSETS_BASE_URL'] = function ( $c ) {
            return get_site_url() . '/' . str_replace( ABSPATH, '', __DIR__ ) . '/assets';
        };

        $this['PLUGIN_NAME'] = function ( $c ) {
            return str_replace( '.php', '', basename( $c['PLUGIN_BASENAME'] ) );
        };

        $this['text_domain'] = function ( $c ) {
            return new Text_Domain( $c );
        };

        $this['upgrade'] = function ( $c ) {
            return new Upgrade( $c );
        };

        $this['twig_loader_filesystem'] = function ( $c ) {
            return new \Twig_Loader_Filesystem( $c['TWIG_PATH'] );
        };

        $this['twig'] = function ( $c ) {
            $twig = new \Twig_Environment(
                $c['twig_loader_filesystem'],
                // [ 'debug' => true ]
                []
            );

            // $twig->addExtension(new \Twig_Extension_Debug());

            return $twig;
        };

        $this['assets'] = function ( $c ) {
            return new Assets( $c );
        };

        $this['reviews'] = function ( $c ) {
            return new Reviews( $c );
        };
    }
}
