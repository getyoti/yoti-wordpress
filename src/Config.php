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
     * @var array
     */
    private $config;

    /**
     * Load Yoti Config.
     *
     * @return array
     */
    public function load($reload = FALSE)
    {
        if ($this->config === NULL || $reload === TRUE) {
            $this->config = maybe_unserialize(get_option(self::YOTI_CONFIG_OPTION_NAME));
        }
        return $this->config;
    }

    /**
     * Remove Yoti config option data from WordPress option table.
     */
    public function delete()
    {
        delete_option(self::YOTI_CONFIG_OPTION_NAME);
        $this->config = NULL;
    }

    /**
     * Save Yoti Config.
     */
    public function save($config)
    {
        update_option(self::YOTI_CONFIG_OPTION_NAME, maybe_serialize($config));
        $this->config = NULL;
    }

    /**
     * @return mixed
     */
    public function get($key)
    {
        $this->load();
        return $this->config[$key] ?? NULL;
    }

    /**
     * Get Yoti upload dir.
     *
     * @return string
     */
    public function uploadDir()
    {
        if (!defined('YOTI_UPLOAD_DIR')) {
            return WP_CONTENT_DIR . '/uploads/yoti';
        }
        return rtrim(YOTI_UPLOAD_DIR, '/');
    }
}
