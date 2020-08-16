<?php

namespace Yoti\WP;

/**
 * Class Config
 */
class Config
{
    private const OPTION_NAME = 'yoti_config';

    public const KEY_APP_ID = 'yoti_app_id';
    public const KEY_SCENARIO_ID = 'yoti_scenario_id';
    public const KEY_CLIENT_SDK_ID = 'yoti_sdk_id';
    public const KEY_PEM = 'yoti_pem';
    public const KEY_COMPANY_NAME = 'yoti_company_name';
    public const KEY_ONLY_EXISTING = 'yoti_only_existing';
    public const KEY_USER_EMAIL = 'yoti_user_email';
    public const KEY_AGE_VERIFICATION = 'yoti_age_verification';

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
            $this->config = maybe_unserialize(get_option(self::OPTION_NAME));
        }
        return $this->config;
    }

    /**
     * Remove Yoti config option data from WordPress option table.
     */
    public function delete(): void
    {
        delete_option(self::OPTION_NAME);
        $this->config = null;
    }

    /**
     * Save Yoti Config.
     *
     * @param array<string,mixed> $config
     */
    public function save($config): void
    {
        update_option(self::OPTION_NAME, maybe_serialize($config));
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
     * @return string|null
     */
    public function getAppId(): ?string
    {
        return $this->get(self::KEY_APP_ID);
    }

    /**
     * @return string|null
     */
    public function getScenarioId(): ?string
    {
        return $this->get(self::KEY_SCENARIO_ID);
    }

    /**
     * @return string|null
     */
    public function getClientSdkId(): ?string
    {
        return $this->get(self::KEY_CLIENT_SDK_ID);
    }

    /**
     * @return array<string,string>|null
     */
    public function getPem(): ?array
    {
        return $this->get(self::KEY_PEM);
    }

    /**
     * @return string|null
     */
    public function getPemContent(): ?string
    {
        return $this->getPem()['contents'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->get(self::KEY_COMPANY_NAME);
    }

    /**
     * Prevent users who have not passed age verification to access your site
     *
     * @return bool
     */
    public function requireAgeVerification(): bool
    {
        return $this->get(self::KEY_AGE_VERIFICATION) === '1';
    }

    /**
     * Only allow existing WordPress users to link their Yoti account
     *
     * @return bool
     */
    public function onlyLinkExistingUsers(): bool
    {
        return $this->get(self::KEY_ONLY_EXISTING) === '1';
    }


    /**
     * Attempt to link Yoti email address with WordPress account for first time users
     *
     * @return bool
     */
    public function linkNewUsersByEmail(): bool
    {
        return $this->get(self::KEY_USER_EMAIL) === '1';
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
