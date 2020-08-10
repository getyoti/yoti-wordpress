<?php

namespace Yoti\WP;

use Yoti\WP\Client\ClientFactory;
use Yoti\WP\Service\Profile;

/**
 * Class Service
 */
class Service
{
    /**
     * @var Profile
     */
    private static $profile;

    /**
     * @return Profile
     */
    public static function profile(): Profile
    {
        if (static::$profile === NULL) {
            static::$profile = new Profile(new ClientFactory());
        }
        return static::$profile;
    }
}
