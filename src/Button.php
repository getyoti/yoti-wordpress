<?php

namespace Yoti\WP;

/**
 * Class Button
 *
 * @author Yoti Ltd <sdksupport@yoti.com>
 */
class Button
{
    /**
     * Default text for Yoti link button
     */
    private const YOTI_LINK_BUTTON_TEXT = 'Use Yoti';

    /**
     * Display Yoti button.
     *
     * @param string|null $redirect
     * @param bool $from_widget
     * @param array<string,string> $instance_config
     */
    public static function render($redirect = null, $from_widget = false, $instance_config = []): void
    {
        // Increment button ID
        static $button_id_suffix = 0;
        $button_id = 'yoti-button-' . ++$button_id_suffix;

        // Do not show the button if the plugin has not been configured.
        $config = Service::config()->load();
        if (!$config) {
            return;
        }

        // Merge instance config with global config.
        $config = array_merge($config, array_filter($instance_config));

        // Default button text and linked status.
        $button_text = Button::YOTI_LINK_BUTTON_TEXT;
        $is_linked = false;

        // Button text and linked status for logged in users.
        if (is_user_logged_in()) {
            $button_text = 'Link to Yoti';
            $current_user = wp_get_current_user();
            $is_linked = !empty(get_user_meta($current_user->ID, 'yoti_user.identifier'));
        }

        // Override button text if set for widget instance.
        if (!empty($config['yoti_button_text'])) {
            $button_text = $config['yoti_button_text'];
        }

        // Build unlink URL.
        $query_params = [
            'yoti-select' => '1',
            'action' => 'unlink',
        ];
        if ($redirect) {
            $query_params['redirect'] = $redirect;
        }
        $unlink_url = site_url('wp-login.php') . '?' . http_build_query($query_params, '', '&', PHP_QUERY_RFC3986);
        $unlink_url = wp_nonce_url($unlink_url, 'yoti_verify', 'yoti_verify');

        View::render('button', [
            'is_linked' => $is_linked,
            'message' => Message::getFlash(),
            'button_text' => $button_text,
            'from_widget' => $from_widget,
            'config' => $config,
            'unlink_url' => $unlink_url,
            'button_id' => $button_id
        ]);
    }
}
