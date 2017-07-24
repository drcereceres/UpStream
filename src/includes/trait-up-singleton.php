<?php
namespace UpStream\Traits;

// Prevent direct access.
if (!defined('ABSPATH')) exit;

/**
 * Trait that abstracts the Singleton design pattern.
 *
 * @package     UpStream
 * @subpackage  Traits
 * @author      UpStream <https://upstreamplugin.com>
 * @copyright   Copyright (c) 2017 UpStream Project Management
 * @license     GPL-3
 * @since       @todo
 */
trait Singleton
{
    /**
     * @var     \ReflectionClass    $instance   The singleton class's instance.
     *
     * @since   @todo
     * @access  private
     * @static
     */
    private static $instance = null;

    /**
     * Initializes the singleton if it's not loaded yet.
     *
     * @since   @todo
     * @static
     * @final
     *
     * @uses    \ReflectionClass
     */
    final public static function instantiate()
    {
        if (empty(self::$instance)) {
            $reflection = new \ReflectionClass(__CLASS__);
            self::$instance = $reflection->newInstanceArgs(func_get_args());
        }
    }

    /**
     * Retrieve the singleton instance.
     * If the singleton it's not loaded, it will be initialized first.
     *
     * @since   @todo
     * @static
     *
     * @return  \ReflectionClass
     */
    public static function getInstance()
    {
        // Ensure the singleton is loaded.
        self::instantiate();

        return self::$instance;
    }

    /**
     * Prevent the class instance being serialized.
     *
     * @since   @todo
     * @access  private
     * @final
     *
     * @throws  \Exception
     */
    final private function __sleep()
    {
        throw new \Exception("You cannot serialize a singleton.");
    }

    /**
     * Prevent the class instance being unserialized.
     *
     * @since   @todo
     * @access  private
     * @final
     *
     * @throws  \Exception
     */
    final private function __wakeup()
    {
        throw new \Exception("You cannot unserialize a singleton.");
    }

    /**
     * Prevent the class instance being cloned.
     *
     * @since   @todo
     * @access  private
     * @final
     */
    final private function __clone()
    {
        // Do nothing.
    }
}
