<?php

namespace AllediaFramework;

class Text_Domain {
    /**
     * @var string
     */
    protected $locale;

    /**
     * @var Container
     */
    protected $container;

    /**
     * TextDomain constructor.
     *
     * @param Container $container
     */
    public function __construct( Container $container ) {
        $this->container = $container;
        $this->locale    = get_locale();
    }

    /**
     * Load the framework's text domain.
     */
    public function load() {
        $mo_file = __DIR__ . 'languages/alledia-framework-' . $this->locale . '.mo';

        if ( file_exists( $mo_file ) ) {
            load_textdomain( 'alledia-framework', $mo_file );
        }
    }
}
