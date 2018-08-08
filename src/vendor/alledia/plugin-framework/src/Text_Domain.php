<?php

namespace AllediaFramework;

class Text_Domain extends AbstractService {
    /**
     * @var string
     */
    protected $locale;

    /**
     * TextDomain constructor.
     *
     * @param Container $container
     */
    public function __construct( Container $container ) {
        parent::__construct( $container );

        $this->locale = get_locale();
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
