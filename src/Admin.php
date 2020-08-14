<?php

namespace Yoti\WP;

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
     * init
     */
    public static function init(): void
    {
        if (self::$instance === null) {
            self::$instance = new self();

            self::$instance->options();
        }
    }

    /**
     * singleton
     */
    private function __construct()
    {
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
        // Make sure user can edit
        if (!current_user_can('manage_options')) {
            return;
        }

        // Get current config
        $config = Service::config();

        // Check curl has preliminary extensions to run
        $errors = [];
        if (!function_exists('curl_version')) {
            $errors[] = "PHP module 'curl' not installed. Yoti requires it to work." .
                "Please contact your server administrator.";
        }
        if (!function_exists('json_decode')) {
            $errors[] = "PHP module 'json' not installed. Yoti requires it to work." .
                "Please contact your server administrator.";
        }
        if (phpversion() === false || version_compare(phpversion(), '7.2', '<')) {
            $errors[] = 'Yoti could not be installed. Yoti PHP SDK requires PHP 7.2 or higher.';
        }

        // Get data
        $data = $config->load();
        $updateMessage = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                $this->setPostData();

                $data['yoti_app_id'] = trim((string) $this->postVar('yoti_app_id'));
                $data['yoti_scenario_id'] = trim((string) $this->postVar('yoti_scenario_id'));
                $data['yoti_sdk_id'] = trim((string) $this->postVar('yoti_sdk_id'));
                $data['yoti_company_name'] = trim((string) $this->postVar('yoti_company_name'));
                $data['yoti_only_existing'] = $this->postVar('yoti_only_existing');
                $data['yoti_user_email'] = $this->postVar('yoti_user_email');
                $data['yoti_age_verification'] = $this->postVar('yoti_age_verification');
                $pemFile = $this->filesVar('yoti_pem', $config->get('yoti_pem'));

                // Validation
                if (!$data['yoti_app_id']) {
                    $errors['yoti_app_id'] = 'App ID is required.';
                }
                if (!$data['yoti_sdk_id']) {
                    $errors['yoti_sdk_id'] = 'Client SDK ID is required.';
                }
                if (empty($pemFile['name'])) {
                    $errors['yoti_pem'] = 'PEM file is required.';
                } elseif (
                    !empty($pemFile['tmp_name']) &&
                    ($pemContents = file_get_contents($pemFile['tmp_name'])) !== false &&
                    !openssl_get_privatekey($pemContents)
                ) {
                    $errors['yoti_pem'] = 'PEM file is invalid.';
                }
            } catch (\Exception $e) {
                $errors['yoti_admin_options'] = 'There was a problem saving form data. Please try again.';
            }

            // No errors? proceed
            if (!$errors) {
                // If pem file uploaded then process
                $name = $contents = null;
                if (!empty($pemFile['tmp_name'])) {
                    $name = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $pemFile['name']);
                    if (!$name) {
                        $name = md5($pemFile['name']) . '.pem';
                    }
                    $contents = file_get_contents($pemFile['tmp_name']);
                } elseif (!$this->postVar('yoti_delete_pem')) {
                    // If delete not ticked
                    $pemConfig = $config->get('yoti_pem');
                    $name = $pemConfig['name'];
                    $contents = $pemConfig['contents'];
                }

                $data['yoti_pem'] = compact('name', 'contents');

                // Save config
                $config->save($data);
                $updateMessage = 'Yoti settings saved.';
            }
        }

        View::render('admin-options', [
            'data' => $data,
            'errors' => $errors,
            'updateMessage' => $updateMessage,
        ]);
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
            throw new \Exception('Could not verify request');
        }
        $this->postData = $_POST;
    }

    /**
     * @param string $var
     * @param mixed|null $default
     *
     * @return string|null
     */
    private function postVar($var, $default = null)
    {
        return array_key_exists($var, $this->postData) ? $this->postData[$var] : $default;
    }

    /**
     * @param string $var
     * @param null $default
     *
     * @return array<string,string>|null
     */
    private function filesVar($var, $default = null)
    {
        return (array_key_exists($var, $_FILES) && !empty($_FILES[$var]['name'])) ? $_FILES[$var] : $default;
    }
}
