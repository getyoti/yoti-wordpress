<?php

/**
 * Class YotiButton
 *
 * @author Yoti Ltd <sdksupport@yoti.com>
 */
class YotiButton
{
    /**
     * Default text for Yoti link button
     */
    const YOTI_LINK_BUTTON_TEXT = 'Use Yoti';

    /**
     * Display Yoti button.
     *
     * @param null $redirect
     * @param bool $from_widget
     */
    public static function render($redirect = NULL, $from_widget = FALSE)
    {
        // No config? no button
        $config = YotiHelper::getConfig();
        if (!$config) {
            return NULL;
        }

        // Use YOTI_CONNECT_BASE_URL environment variable if configured.
        $qr_url = NULL;
        $service_url = NULL;
        if (getenv('YOTI_CONNECT_BASE_URL'))
        {
            // Base url for connect
            $baseUrl = preg_replace('/^(.+)\/connect$/', '$1', getenv('YOTI_CONNECT_BASE_URL'));
            $qr_url = sprintf('%s/qr/', $baseUrl);
            $service_url = sprintf('%s/connect/', $baseUrl);
        }

        $button_text = YotiButton::YOTI_LINK_BUTTON_TEXT;
        $is_linked = FALSE;

        if (is_user_logged_in()) {
            $button_text = 'Link to Yoti';
            $currentUser = wp_get_current_user();
            $is_linked = !empty(get_user_meta($currentUser->ID, 'yoti_user.identifier'));
        }

        $message = YotiHelper::getFlash();

        // Build unlink URL.
        $unlink_url = site_url('wp-login.php') . '?yoti-select=1&action=unlink&redirect=' . ($redirect ? '&redirect=' . rawurlencode($redirect) : '');
        $unlink_url = wp_nonce_url($unlink_url, 'yoti_verify', 'yoti_verify');

        $view = function () use (
            $is_linked,
            $service_url,
            $qr_url,
            $message,
            $button_text,
            $from_widget,
            $config,
            $unlink_url
        )
        {
            require __DIR__ . '/views/button.php';
        };
        $view();
    }
}