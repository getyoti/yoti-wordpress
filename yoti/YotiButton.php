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
        $testToken = NULL;
        if (YotiHelper::mockRequests()) {
            $testToken = file_get_contents(__DIR__ . '/sdk/sample-data/connect-token.txt');
        }

        // No config? no button
        $config = YotiHelper::getConfig();
        if (!$config && !$testToken) {
            return NULL;
        }

        // If connect url starts with 'https://staging' then we are in staging mode
        $isStaging = strpos(\Yoti\YotiClient::CONNECT_BASE_URL, 'https://staging') === 0;
        if ($isStaging)
        {
            // Base url for connect
            $baseUrl = preg_replace('/^(.+)\/connect$/', '$1', \Yoti\YotiClient::CONNECT_BASE_URL);

            $script[] = sprintf('_ybg.config.qr = "%s/qr/";', $baseUrl);
            $script[] = sprintf('_ybg.config.service = "%s/connect/";', $baseUrl);
        }

        // Add init()
        $script[] = '_ybg.init();';
        $linkButton = '<span
            data-yoti-application-id="' . $config['yoti_app_id'] . '"
            data-yoti-type="inline"
            data-yoti-scenario-id="' . $config['yoti_scenario_id'] . '"
            data-size="small">
            %s
        </span>
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