<?php

namespace Yoti\WP;

use Yoti\Exception\PemFileException;
use Yoti\Util\PemFile;

/**
 * Class Admin
 *
 * @author Yoti SDK <sdksupport@yoti.com>
 */
class Admin
{
    /**
     * @var self
     */
    private static $instance;

    /**
     * POST data.
     *
     * @var array<string,mixed>
     */
    private $postData;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var string[]
     */
    private $messages = [];

    /**
     * @var string[]
     */
    private $errors = [];

    /**
     * @var array<string,mixed>
     */
    private $formData = [];

    /**
     * init
     */
    public static function init(): void
    {
        if (self::$instance === null) {
            $config = Service::config();

            self::$instance = new self($config);

            self::$instance->options();
        }
    }

    /**
     * singleton
     */
    private function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * singleton
     */
    private function __clone()
    {
    }

    /**
     * options page for admin
     */
    private function options(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $this->checkExtensions();

        $this->formData = $this->config->load() ?? [];

        $this->handleSubmit();

        View::render('admin-options', [
            'data' => $this->formData,
            'errors' => $this->errors,
            'messages' => $this->messages,
        ]);
    }

    /**
     * Form submit handler
     */
    private function handleSubmit(): void
    {
        if (
            !isset($_SERVER['REQUEST_METHOD']) ||
            $_SERVER['REQUEST_METHOD'] !== 'POST'
        ) {
            return;
        }

        try {
            $this->setPostData();

            $this->formData[Config::KEY_APP_ID] = trim((string) $this->postValue(Config::KEY_APP_ID));
            if (!$this->formData[Config::KEY_APP_ID]) {
                $this->errors[] = 'App ID is required.';
            }

            $this->formData[Config::KEY_SCENARIO_ID] = trim((string) $this->postValue(Config::KEY_SCENARIO_ID));

            $this->formData[Config::KEY_CLIENT_SDK_ID] = trim((string) $this->postValue(Config::KEY_CLIENT_SDK_ID));
            if (!$this->formData[Config::KEY_CLIENT_SDK_ID]) {
                $this->errors[] = 'Client SDK ID is required.';
            }

            $this->formData[Config::KEY_COMPANY_NAME] = trim((string) $this->postValue(Config::KEY_COMPANY_NAME));
            $this->formData[Config::KEY_ONLY_EXISTING] = $this->postValue(Config::KEY_ONLY_EXISTING);
            $this->formData[Config::KEY_USER_EMAIL] = $this->postValue(Config::KEY_USER_EMAIL);
            $this->formData[Config::KEY_AGE_VERIFICATION] = $this->postValue(Config::KEY_AGE_VERIFICATION);

            if ($this->postValue('yoti_delete_pem')) {
                $this->formData[Config::KEY_PEM] = [];
            } else {
                try {
                    $this->formData[Config::KEY_PEM] = $this->uploadedPemFile() ?? $this->config->getPem();
                } catch (PemFileException $e) {
                    $this->errors[] = 'PEM file is invalid.';
                }
                if (empty($this->formData[Config::KEY_PEM]['name'])) {
                    $this->errors[] = 'PEM file is required.';
                }
            }
        } catch (\Exception $e) {
            $this->errors[] = 'There was a problem saving form data. Please try again.';
        }

        if (!$this->errors) {
            $this->config->save($this->formData);
            $this->messages[] = 'Yoti settings saved.';
        }
    }

    /**
     * Check that required extensions are available.
     */
    private function checkExtensions(): void
    {
        if (!function_exists('curl_version')) {
            $this->errors[] = "PHP module 'curl' not installed. Yoti requires it to work." .
                "Please contact your server administrator.";
        }
        if (!function_exists('json_decode')) {
            $this->errors[] = "PHP module 'json' not installed. Yoti requires it to work." .
                "Please contact your server administrator.";
        }
        if (phpversion() === false || version_compare(phpversion(), '7.2', '<')) {
            $this->errors[] = 'Yoti could not be installed. Yoti PHP SDK requires PHP 7.2 or higher.';
        }
    }

    /**
     * Sets POST data from request.
     */
    private function setPostData(): void
    {
        if (
            !isset($_POST[Constants::NONCE_ACTION])
            || !wp_verify_nonce($_POST[Constants::NONCE_ACTION], Constants::NONCE_ACTION)
        ) {
            throw new \RuntimeException('Could not verify request');
        }
        $this->postData = $_POST;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    private function postValue($key): ?string
    {
        if (
            !isset($this->postData[$key]) ||
            !is_string($this->postData[$key])
        ) {
            return null;
        }

        $value = wp_unslash($this->postData[$key]);
        if (!is_string($value)) {
            return null;
        }

        return sanitize_text_field($value);
    }

    /**
     * Returns submitted pem file data.
     *
     * @return array<string,string>|null
     *
     * @throws PemFileException
     */
    private function uploadedPemFile(): ?array
    {
        $pemFile = $this->filesVar(Config::KEY_PEM);

        if (!empty($pemFile['tmp_name'])) {
            $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $pemFile['name']);
            if (!$name) {
                $name = md5($pemFile['name']) . '.pem';
            }
            return [
                'name' => $name,
                'contents' => (string) PemFile::fromFilePath($pemFile['tmp_name']),
            ];
        }

        return null;
    }

    /**
     * @param string $var
     *
     * @return array<string,string>|null
     */
    private function filesVar($var): ?array
    {
        return isset($_FILES[$var]['name']) ? $_FILES[$var] : null;
    }
}
