<?php

namespace Yoti\WP;

use Yoti\YotiClient;

/**
 * Class Config
 */
class Config
{
    /** Yoti config option name */
    const YOTI_CONFIG_OPTION_NAME = 'yoti_config';

    /**
     * Load Yoti Config.
     *
     * @return array
     */
    public static function load()
    {
        return maybe_unserialize(get_option(self::YOTI_CONFIG_OPTION_NAME));
    }

    /**
     * Remove Yoti config option data from WordPress option table.
     */
    public static function delete()
    {
        delete_option(self::YOTI_CONFIG_OPTION_NAME);
    }

    /**
     * Save Yoti Config.
     *
     * @return array
     */
    public static function save($config)
    {
        update_option(self::YOTI_CONFIG_OPTION_NAME, maybe_serialize($config));
    }
}
