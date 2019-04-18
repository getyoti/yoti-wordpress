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
     *
     * @return string
     */
    /**
     * Display Yoti button.
     *
     * @param null $redirect
     * @param bool $fromWidget
     *
     * @return null|string
     */
    public static function render($redirect = NULL, $fromWidget = FALSE)
    {
        // No config? no button
        $config = YotiHelper::getConfig();
        if (!$config) {
            return NULL;
        }

        // Use YOTI_CONNECT_BASE_URL environment variable if configured.
        if (getenv('YOTI_CONNECT_BASE_URL'))
        {
            // Base url for connect
            $baseUrl = preg_replace('/^(.+)\/connect$/', '$1', getenv('YOTI_CONNECT_BASE_URL'));

            $script[] = sprintf('_ybg.config.qr = "%s/qr/";', $baseUrl);
            $script[] = sprintf('_ybg.config.service = "%s/connect/";', $baseUrl);
        }

        // Add init()
        $script[] = '_ybg.init();';

        // Required button attributes.
        $button_attributes = [
            'data-yoti-application-id' => $config['yoti_app_id'],
            'data-yoti-scenario-id' => $config['yoti_scenario_id'],
            'data-size' => 'small',
        ];

        // Markup for the QR type.
        if (($qr_type = YotiHelper::getQrType()) && $qr_type !== 'connect') {
            $button_attributes['data-yoti-type'] = $qr_type;
        }

        $button_attributes_markup = [];
        foreach ($button_attributes as $key => $value) {
            $button_attributes_markup[] = $key . '="' . esc_attr($value) . '"';
        }

        $linkButton = '<span ' . implode(' ', $button_attributes_markup) .  '>%s</span>
          <script>' . implode("\r\n", $script) . '</script>';

        if (!is_user_logged_in()) {
            $button = sprintf($linkButton, YotiButton::YOTI_LINK_BUTTON_TEXT);
        }
        else {
            $currentUser = wp_get_current_user();
            $yotiId = get_user_meta($currentUser->ID, 'yoti_user.identifier');
            if (!$yotiId) {
                $button = sprintf($linkButton, 'Link to Yoti');
            }
            else if ($fromWidget) {
                $button = '<strong>Yoti</strong> Linked';
            }
            else {
                $promptMessage = 'This will unlink your account from Yoti.';
                $onClikEvent = "onclick=\"return confirm('{$promptMessage}')\"";
                $url = site_url('wp-login.php') . '?yoti-select=1&action=unlink&redirect=' . ($redirect ? '&redirect=' . rawurlencode($redirect) : '');
                $label = 'Unlink Yoti Account';
                $button = "<a class=\"yoti-connect-button\" href=\"{$url}\" {$onClikEvent}>{$label}</a>";
            }
        }

        $message = YotiHelper::getFlash();
        $html = '<div class="yoti-connect"> ';
        if ($message) {
            $html .= '<div class="' . ($message['type'] == 'error' ? 'error' : 'message') . ' notice">' .
                '<p><strong>' . $message['message'] . '</strong></p>' .
                '</div>';
        }
        $html .= $button;
        $html .= '</div>';

        return $html;
    }
}