<?php

namespace Yoti\WP;

use Yoti\WP\Client\ClientFactory;
use Yoti\WP\Client\ClientFactoryInterface;
use Yoti\WP\Config;
use Yoti\WP\User;

/**
 * Class Service
 */
class Service
{
    /**
     * @var User
     */
    private static $user;

    /**
     * @var User
     */
    private static $config;

    /**
     * @var ClientFactoryInterface
     */
    private static $clientFactory;

    /**
     * @return User
     */
    public static function user(): User
    {
        if (static::$user === NULL) {
            static::$user = new User(self::clientFactory(), self::config());
        }
        return static::$user;
    }

    /**
     * @return Config
     */
    public static function config(): Config
    {
        if (static::$config === NULL) {
            static::$config = new Config();
        }
        return static::$config;
    }

    /**
     * @return ClientFactoryInterface
     */
    public static function clientFactory(): ClientFactoryInterface
    {
        if (static::$clientFactory === NULL) {
            static::$clientFactory = new ClientFactory(self::config());
        }
        return static::$clientFactory;
    }
}
