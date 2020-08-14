<?php

namespace Yoti\WP;

use Yoti\WP\Exception\LinkException;
use Yoti\WP\Exception\UnlinkException;
use Yoti\WP\Exception\UserMessageExceptionInterface;
use Yoti\WP\Widget;
use Yoti\WP\User;

/**
 * Class Yoti used in the plugin main file yoti.php
 */
class Hooks
{
    /**
     * Activation hook.
     */
    public static function activation(): void
    {
        // Create upload dir
        if (!is_dir(Service::config()->uploadDir())) {
            mkdir(Service::config()->uploadDir(), 0777, true);
        }
    }

    /**
     * Uninstall hook.
     */
    public static function uninstall(): void
    {
        Service::config()->delete();
    }

    /**
     * Yoti WP init hook.
     */
    public static function init(): void
    {
        if (!session_id()) {
            session_start();
        }

        // Verifiy the action.
        $verified = !empty($_GET['yoti_verify']) && wp_verify_nonce($_GET['yoti_verify'], 'yoti_verify');

        if (!empty($_GET['yoti-select'])) {
            $userService = Service::user();

            // Action
            $action = !empty($_GET['action']) ? $_GET['action'] : '';
            $redirect = !empty($_GET['redirect']) ? $_GET['redirect'] : home_url();
            switch ($action) {
                case 'link':
                    try {
                        $userService->link();
                    } catch (UserMessageExceptionInterface $e) {
                        Message::setFlash($e->getUserMessage(), 'error');
                        $redirect = home_url();
                    }
                    wp_safe_redirect($redirect);
                    exit;

                case 'unlink':
                    if (!$verified) {
                        Message::setFlash('Yoti profile could not be unlinked, please try again.', 'error');
                        $redirect = home_url();
                    } else {
                        try {
                            $userService->unlink();
                            Message::setFlash('Your Yoti profile was successfully unlinked from your account.');
                        } catch (UserMessageExceptionInterface $e) {
                            $redirect = home_url();
                            Message::setFlash($e->getUserMessage(), 'error');
                        }
                    }

                    wp_safe_redirect($redirect);
                    exit;

                case 'bin-file':
                    if ($verified) {
                        $userService->binFile('selfie', !empty($_GET['user_id']) ? $_GET['user_id'] : null);
                        exit;
                    }
                    break;
            }
        }
    }

    /**
     * Add items to admin menu.
     */
    public static function adminMenu(): void
    {
        wp_enqueue_style('yoti-asset-css', plugin_dir_url(__FILE__) . 'assets/styles.css', []);
        add_options_page('Yoti', 'Yoti', 'manage_options', 'yoti', [Admin::class, 'init']);
    }

    /**
     * Add to login footer.
     */
    public static function loginHeader(): void
    {
        $userService = Service::user();

        if (!$userService->getYotiUserFromStore()) {
            // Don't allow unless there is an existing session
            return;
        } elseif ($_REQUEST['REQUEST_METHOD'] != 'POST' && !isset($_REQUEST['redirect_to'])) {
            // On page refresh clear the YotiUserStore session and don't display the message
            $userService->clearYotiUserStore();
            return;
        }

        $config = Service::config();
        $companyName = $config->getCompanyName() ?? 'WordPress';

        // Verify the action.
        $verified = !empty($_POST['yoti_verify']) && wp_verify_nonce($_POST['yoti_verify'], 'yoti_verify');
        if ($verified) {
            $noLink = !empty($_POST['yoti_nolink']);
        } else {
            $noLink = false;
        }

        View::render('login-header', [
            'companyName' => $companyName,
            'noLink' => $noLink,
        ]);
    }

    /**
     * WP login hook.
     *
     * @param string|null $user_login
     * @param \WP_User|null $user
     */
    public static function login($user_login = null, $user = null): void
    {
        if (!$user) {
            return;
        }

        // Return when the login form doesn't have Yoti verification.
        if (empty($_POST['yoti_verify'])) {
            return;
        }

        $userService = Service::user();

        // Verify the action.
        if (!wp_verify_nonce($_POST['yoti_verify'], 'yoti_verify')) {
            Message::setFlash('Yoti profile could not be linked, please try again.');
        } else {
            $activityDetails = $userService->getYotiUserFromStore();
            $yotiNoLinkIsNotChecked = (!isset($_POST['yoti_nolink']) || empty($_POST['yoti_nolink']));

            // Check that activityDetails exists and yoti_nolink button is not checked
            if ($activityDetails && $yotiNoLinkIsNotChecked) {
                // Link account to Yoti
                $userService->createYotiUser($user->ID, $activityDetails);
            }
        }

        // Remove Yoti session
        $userService->clearYotiUserStore();
    }

    /**
     * WP logout hook.
     */
    public static function logout(): void
    {
        Message::clearFlash();
    }

    /**
     * Display Yoti user profile.
     *
     * @param \WP_User $user.
     */
    public static function showUserProfile($user): void
    {
        // Do not display profile if account is not linked.
        if (empty(get_user_meta($user->ID, 'yoti_user.identifier'))) {
            return;
        }

        $userService = Service::user();

        $dbProfile = $userService->getUserProfile($user->ID);
        if ($dbProfile === false) {
            return;
        }

        $selfieUrl = $userService->selfieUrl($user->ID, $dbProfile);

        $profileUserId = $user->ID;
        $currentUser = wp_get_current_user();
        $isAdmin = in_array('administrator', $currentUser->roles, true);
        $userId = (!empty($_GET['user_id'])) ? $_GET['user_id'] : null;

        // Set userId if admin user is viewing his own profile
        // and the userId is NULL
        if (
            $isAdmin
            && $profileUserId === $currentUser->ID
            && is_null($userId)
        ) {
            $userId = $profileUserId;
        }

        if (!empty($dbProfile)) {
            // Selfie is displayed separately in template.
            unset($dbProfile[User::SELFIE_FILENAME]);
        }

        // Flag to display button.
        $displayButton = false;

        if (!$isAdmin) {
            // Display for non-admin accounts.
            $displayButton = true;
        } elseif (!$userId) {
            // Display for anonymous users.
            $displayButton = true;
        } elseif ($currentUser->ID === $userId) {
            // Display for current user.
            $displayButton = true;
        }

        // Add profile scope
        View::render('profile', [
            'dbProfile' => $dbProfile,
            'selfieUrl' => $selfieUrl,
            'displayButton' => $displayButton,
            'userId' => $userId,
        ]);
    }

    /**
     * Register Yoti widget.
     */
    public static function registerWidget(): void
    {
        register_widget(Widget::class);
    }

    /**
     * Add Yoti js.
     */
    public static function enqueueScripts(): void
    {
        wp_enqueue_script('yoti-asset-js', Constants::YOTI_SDK_JAVASCRIPT_LIBRARY, [], null, true);
        wp_add_inline_script('yoti-asset-js', "
            if (typeof yotiConfig != 'undefined') {
                window.Yoti.Share.init(yotiConfig);
            }");
    }

    /**
     * Add Yoti settings link to the admin plugins page.
     *
     * @param string[] $links
     * @param string $file
     *
     * @return string[]
     */
    public static function pluginActionLinks($links, $file)
    {
        $settingsLink = '<a href="' . admin_url('options-general.php?page=yoti') . '">' .
                __('Settings', 'yoti') . '</a>';
        // Add Yoti settings to the plugin links.
        array_unshift($links, $settingsLink);

        return $links;
    }

    /**
     * Display a notice for successful activation.
     */
    public static function pluginActivateNotice(): void
    {
        global $pagenow;

        // Display the notice only on the plugins page.
        if ($pagenow === "plugins.php") {
            View::render('activate-notice');
        }
    }
}
