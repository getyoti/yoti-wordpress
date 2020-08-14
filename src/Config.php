<?php

namespace Yoti\WP;

/**
 * Class Config
 */
class Config
{
    /** Yoti config option name */
    private const YOTI_CONFIG_OPTION_NAME = 'yoti_config';

    /**
     * @var array<string,mixed>|null
     */
    private $config;

    /**
     * Load Yoti Config.
     *
     * @param bool $reload
     *
     * @return array<string,mixed>|null
     */
    public function load($reload = false)
    {
        if ($this->config === null || $reload === true) {
            $this->config = maybe_unserialize(get_option(self::YOTI_CONFIG_OPTION_NAME));
        }
        return $this->config;
    }

    /**
     * Remove Yoti config option data from WordPress option table.
     */
    public function delete(): void
    {
        delete_option(self::YOTI_CONFIG_OPTION_NAME);
        $this->config = null;
    }

    /**
     * Save Yoti Config.
     *
     * @param array<string,mixed> $config
     */
    public function save($config): void
    {
        update_option(self::YOTI_CONFIG_OPTION_NAME, maybe_serialize($config));
        $this->config = null;
    }

    /**
     * Get config value by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        $this->load();
        return $this->config[$key] ?? null;
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
