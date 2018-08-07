<?php

namespace AllediaFramework;

class Core {

    protected $container;

    /**
     * Core constructor.
     *
     * @param string $plugin_base_name
     */
    public function __construct( $plugin_base_name ) {
        $container = $this->set_container( $plugin_base_name );

        $container['text_domain']->load();

        add_action( 'admin_enqueue_scripts', [ $container['assets'], 'admin_enqueue_styles' ] );
        add_action( 'admin_enqueue_scripts', [ $container['assets'], 'admin_enqueue_scripts' ] );
    }

    /**
     * @return mixed
     */
    public function get_container() {
        return $this->container;
    }

    /**
     * @param $plugin_base_name
     */
    protected function set_container( $plugin_base_name ) {
        $this->container = new Container( [ 'PLUGIN_BASENAME' => $plugin_base_name ] );

        return $this->container;
    }

    /**
     * @param $service_name
     *
     * @return mixed
     * @throws \Exception
     */
    public function get_service( $service_name ) {
        if ( ! isset( $this->container[ $service_name ] ) ) {
            throw new \Exception( 'Service ' . $service_name . ' is undefined.' );
        }

        return $this->container[ $service_name ];
    }
}
