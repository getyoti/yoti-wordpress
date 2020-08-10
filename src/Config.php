<?php

namespace Yoti\WP;

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

    /**
     * Get Yoti upload dir.
     *
     * @return string
     */
    public static function uploadDir()
    {
        if (!defined('YOTI_UPLOAD_DIR')) {
            return WP_CONTENT_DIR . '/uploads/yoti';
        }
        return rtrim(YOTI_UPLOAD_DIR, '/');
    }
}
