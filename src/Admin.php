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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        try {
            $this->setPostData();

            $this->formData['yoti_app_id'] = trim((string) $this->postValue('yoti_app_id'));
            if (!$this->formData['yoti_app_id']) {
                $this->errors[] = 'App ID is required.';
            }

            $this->formData['yoti_scenario_id'] = trim((string) $this->postValue('yoti_scenario_id'));

            $this->formData['yoti_sdk_id'] = trim((string) $this->postValue('yoti_sdk_id'));
            if (!$this->formData['yoti_sdk_id']) {
                $this->errors[] = 'Client SDK ID is required.';
            }

            $this->formData['yoti_company_name'] = trim((string) $this->postValue('yoti_company_name'));
            $this->formData['yoti_only_existing'] = $this->postValue('yoti_only_existing');
            $this->formData['yoti_user_email'] = $this->postValue('yoti_user_email');
            $this->formData['yoti_age_verification'] = $this->postValue('yoti_age_verification');

            if ($this->postValue('yoti_delete_pem')) {
                $this->formData['yoti_pem'] = [];
            } else {
                try {
                    $this->formData['yoti_pem'] = $this->uploadedPemFile() ?? $this->config->get('yoti_pem');
                } catch (PemFileException $e) {
                    $this->errors[] = 'PEM file is invalid.';
                }
                if (empty($this->formData['yoti_pem']['name'])) {
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
            !isset($_POST['yoti_verify'])
            || !wp_verify_nonce($_POST['yoti_verify'], 'yoti_verify')
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
        $pemFile = $this->filesVar('yoti_pem');

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
