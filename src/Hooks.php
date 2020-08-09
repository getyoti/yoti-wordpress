<?php

namespace Yoti\WP;

/**
 * Class Yoti used in the plugin main file yoti.php
 */
class Hooks
{
    /**
     * Activation hook.
     */
    public static function yoti_activation_hook()
    {
        // Create upload dir
        if (!is_dir(User::uploadDir()))
        {
            mkdir(User::uploadDir(), 0777, TRUE);
        }
    }

    /**
     * Uninstall hook.
     */
    public static function yoti_uninstall_hook()
    {
        Config::delete();
    }

    /**
     * Yoti WP init hook.
     */
    public static function yoti_init()
    {
        if (!session_id())
        {
            session_start();
        }

        // Verifiy the action.
        $verified = !empty($_GET['yoti_verify']) && wp_verify_nonce($_GET['yoti_verify'], 'yoti_verify');

        if (!empty($_GET['yoti-select']))
        {
            $user = new User();
            // Action
            $action = !empty($_GET['action']) ? $_GET['action'] : '';
            $redirect = !empty($_GET['redirect']) ? $_GET['redirect'] : home_url();
            switch ($action)
            {
                case 'link':
                    if (!$user->link())
                    {
                        $redirect = home_url();
                    }
                    wp_safe_redirect($redirect);
                    exit;
                    break;

                case 'unlink':
                    if (!$verified)
                    {
                        Message::setFlash('Yoti profile could not be unlinked, please try again.');
                        $redirect = home_url();
                    }
                    elseif (!$user->unlink())
                    {
                        $redirect = home_url();
                    }

                    wp_safe_redirect($redirect);
                    exit;
                    break;

                case 'bin-file':
                    if ($verified) {
                        $user->binFile('selfie', !empty($_GET['user_id']) ? $_GET['user_id'] : NULL);
                        exit;
                    }
                    break;
            }
        }
    }

    /**
     * Add items to admin menu.
     */
    public static function yoti_admin_menu()
    {
        wp_enqueue_style('yoti-asset-css', plugin_dir_url(__FILE__) . 'assets/styles.css', FALSE);
        add_options_page('Yoti', 'Yoti', 'manage_options', 'yoti', [Admin::class, 'init']);
    }

    /**
     * Add to login footer.
     */
    public static function yoti_login_header()
    {
        // Don't allow unless there is an existing session
        if (!User::getYotiUserFromStore())
        {
            return;
        }
        // On page refresh clear the YotiUserStore session and don't display the message
        elseif($_REQUEST['REQUEST_METHOD'] != 'POST' && !isset($_REQUEST['redirect_to']))
        {
            User::clearYotiUserStore();

            return;
        }

        $config = Config::load();
        $companyName = 'WordPress';
        if(isset($config['yoti_company_name']) && !empty($config['yoti_company_name'])) {
            $companyName = $config['yoti_company_name'];
        }

        // Verifiy the action.
        $verified = !empty($_POST['yoti_verify']) && wp_verify_nonce($_POST['yoti_verify'], 'yoti_verify');
        if ($verified) {
            $noLink = !empty($_POST['yoti_nolink']);
        }
        else {
            $noLink = FALSE;
        }

        View::render('login-header', [
            'companyName' => $companyName,
            'noLink' => $noLink,
        ]);
    }

    /**
     * WP login hook.
     *
     * @param null $user_login
     * @param null $user
     */
    public static function yoti_login($user_login = NULL, $user = NULL)
    {
        if (!$user) {
            return;
        }

        // Return when the login form doesn't have Yoti verification.
        if (empty($_POST['yoti_verify'])) {
            return;
        }

        // Verify the action.
        if (!wp_verify_nonce($_POST['yoti_verify'], 'yoti_verify')) {
            Message::setFlash('Yoti profile could not be linked, please try again.');
        }
        else {
            $activityDetails = User::getYotiUserFromStore();
            $yotiNoLinkIsNotChecked = (!isset($_POST['yoti_nolink']) || empty($_POST['yoti_nolink']));

            // Check that activityDetails exists and yoti_nolink button is not checked
            if ($activityDetails && $yotiNoLinkIsNotChecked)
            {
                // Link account to Yoti
                $yotiUser = new User();
                $yotiUser->createYotiUser($user->ID, $activityDetails);
            }
        }

        // Remove Yoti session
        User::clearYotiUserStore();
    }

    /**
     * WP logout hook.
     */
    public static function yoti_logout()
    {
        Message::clearFlash();
    }

    /**
     * Display Yoti user profile.
     *
     * @param WP_User $user.
     */
    public static function show_user_profile($user)
    {
        // Do not display profile if account is not linked.
        if (empty(get_user_meta($user->ID, 'yoti_user.identifier'))) {
            return;
        }

        $dbProfile = (array) User::getUserProfile($user->ID);

        $profileUserId = $user->ID;
        $currentUser = wp_get_current_user();
        $isAdmin = in_array('administrator', $currentUser->roles, TRUE);
        $userId = (!empty($_GET['user_id'])) ? $_GET['user_id'] : NULL;

        // Set userId if admin user is viewing his own profile
        // and the userId is NULL
        if(
            $isAdmin
            && $profileUserId === $currentUser->ID
            && is_null($userId)
        ) {
            $userId = $profileUserId;
        }

        if (!empty($dbProfile)) {
            // Move selfie attr to the top
            if (isset($dbProfile[User::SELFIE_FILENAME])) {
                $selfieDataArr = [User::SELFIE_FILENAME => $dbProfile[User::SELFIE_FILENAME]];
                unset($dbProfile[User::SELFIE_FILENAME]);
                $dbProfile = array_merge(
                    $selfieDataArr,
                    $dbProfile
                );
            }
        }

        // Flag to display button.
        $displayButton = FALSE;

        if (!$isAdmin) {
            // Display for non-admin accounts.
            $displayButton = TRUE;
        }
        elseif (!$userId) {
            // Display for anonymous users.
            $displayButton = TRUE;
        }
        elseif ($currentUser->ID === $userId) {
            // Display for current user.
            $displayButton = TRUE;
        }

        // Add profile scope
        View::render('profile', [
            'dbProfile' => $dbProfile,
            'displayButton' => $displayButton,
            'userId' => $userId,
        ]);
    }

    /**
     * Register Yoti widget.
     */
    public static function yoti_register_widget()
    {
        register_widget(Widget::class);
    }

    /**
     * Add Yoti js.
     */
    public static function yoti_enqueue_scripts()
    {
        wp_enqueue_script('yoti-asset-js', Constants::YOTI_SDK_JAVASCRIPT_LIBRARY, [], NULL, TRUE);
        wp_add_inline_script('yoti-asset-js', "
            if (typeof yotiConfig != 'undefined') {
                window.Yoti.Share.init(yotiConfig);
            }");
    }

    /**
     * Add Yoti settings link to the admin plugins page.
     *
     * @param $links
     * @param $file
     *
     * @return mixed
     */
    public static function yoti_plugin_action_links($links, $file)
    {
        $settingsLink = '<a href="'. admin_url( 'options-general.php?page=yoti' ) . '">' .
                __('Settings', 'yoti') . '</a>';
        // Add Yoti settings to the plugin links.
        array_unshift($links, $settingsLink);

        return $links;
    }

    /**
     * Display a notice for successful activation.
     */
    public static function yoti_plugin_activate_notice()
    {
        global $pagenow;

        // Display the notice only on the plugins page.
        if ($pagenow === "plugins.php") {
            View::render('activate-notice');
        }
    }
}
