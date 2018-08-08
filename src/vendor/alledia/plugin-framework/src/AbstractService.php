<?php

namespace AllediaFramework;

abstract class AbstractService {
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
    }
}
