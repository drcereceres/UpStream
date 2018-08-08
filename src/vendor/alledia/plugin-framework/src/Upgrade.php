<?php

namespace AllediaFramework;

class Upgrade extends AbstractService {
    /**
     * @var string
     */
    protected $url;

    /**
     * Add an Upgrade link to the action links in the plugin list.
     */
    public function add_action_upgrade_link( $url ) {
        $this->url = $url;


        add_action( 'plugin_action_links_' . $this->container['PLUGIN_BASENAME'], [ $this, 'plugin_action_links' ],
            999 );
    }

    /**
     * @param array $links
     */
    public function plugin_action_links( $links ) {
        $twig = $this->container['twig'];

        $context = [
            'url'   => $this->url,
            'label' => __( 'Upgrade', 'alledia-framework' ),
            'class' => $this->container['PLUGIN_NAME'],
        ];

        $link = $twig->render( 'action_link_upgrade.twig', $context );

        $links = array_merge( $links, [ $link ] );

        return $links;
    }
}
