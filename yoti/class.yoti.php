<?php

require_once __DIR__ . '/YotiHelper.php';
require_once __DIR__ . '/YotiAdmin.php';
require_once __DIR__ . '/YotiButton.php';
require_once __DIR__ . '/YotiWidget.php';

/**
 * Class Yoti used in the plugin main file yoti.php
 */
class Yoti
{
    /**
     * Activation hook.
     */
    public static function yoti_activation_hook()
    {
        // Create upload dir
        if (!is_dir(YotiHelper::uploadDir()))
        {
            mkdir(YotiHelper::uploadDir(), 0777, TRUE);
        }
    }

    /**
     * Uninstall hook.
     */
    public static function yoti_uninstall_hook()
    {
        YotiHelper::deleteYotiConfigData();
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

        if (!empty($_GET['yoti-select']))
        {
            $yotiHelper = new YotiHelper();
            // Action
            $action = !empty($_GET['action']) ? $_GET['action'] : '';
            $redirect = !empty($_GET['redirect']) ? $_GET['redirect'] : home_url();
            switch ($action)
            {
                case 'link':
                    if (!$yotiHelper->link())
                    {
                        $redirect = home_url();
                    }
                    wp_safe_redirect($redirect);
                    exit;
                    break;

                case 'unlink':
                    if (!$yotiHelper->unlink())
                    {
                        $redirect = home_url();
                    }
                    wp_safe_redirect($redirect);
                    exit;
                    break;

                case 'bin-file':
                    $yotiHelper->binFile('selfie', !empty($_GET['user_id']) ? $_GET['user_id'] : NULL);
                    exit;
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
        add_options_page('Yoti', 'Yoti', 'manage_options', 'yoti', 'YotiAdmin::init');
    }

    /**
     * Add to login footer.
     */
    public static function yoti_login_header()
    {
        // Don't allow unless there is an existing session
        if (!YotiHelper::getYotiUserFromStore())
        {
            return;
        }
        // On page refresh clear the YotiUserStore session and don't display the message
        elseif($_REQUEST['REQUEST_METHOD'] != 'POST' && !isset($_REQUEST['redirect_to']))
        {
            YotiHelper::clearYotiUserStore();

            return;
        }

        $config = YotiHelper::getConfig();
        $companyName = 'WordPress';
        if(isset($config['yoti_company_name']) && !empty($config['yoti_company_name'])) {
            $companyName = $config['yoti_company_name'];
        }

        $noLink = !empty($_POST['yoti_nolink']);

        echo '<div style="margin: 0 0 25px 0" class="message">
        <div style="font-weight: bold; margin-bottom: 5px;">Warning: You are about to link your ' . $companyName . ' account to your Yoti account. If you don\'t want this to happen, tick the checkbox below.</div>
        <input type="checkbox" id="edit-yoti-link" name="yoti_nolink" value="1" class="form-checkbox"' . ($noLink ? ' checked="checked"' : '') . '>
        <label class="option" for="edit-yoti-link">Don\'t link my Yoti account</label>
    </div>';
    }

    /**
     * WP login hook.
     *
     * @param null $user_login
     * @param null $user
     */
    public static function yoti_login($user_login=NULL, $user=NULL)
    {
        if (!$user) {
            return;
        }

        $activityDetails = YotiHelper::getYotiUserFromStore();
        $yotiNoLinkIsNotChecked = (!isset($_POST['yoti_nolink']) || empty($_POST['yoti_nolink']));

        // Check that activityDetails exists and yoti_nolink button is not checked
        if ($activityDetails && $yotiNoLinkIsNotChecked)
        {
            // Link account to Yoti
            $yotiHelper = new YotiHelper();
            $yotiHelper->createYotiUser($user->ID, $activityDetails);
        }

        // Remove Yoti session
        unset($_SESSION['yoti_nolink']);
        YotiHelper::clearYotiUserStore();
    }

    /**
     * WP logout hook.
     */
    public static function yoti_logout()
    {
        YotiHelper::clearFlash();
    }

    /**
     * Display Yoti user profile.
     *
     * @param WP_User $user.
     */
    public static function show_user_profile($user)
    {
        $dbProfile = YotiHelper::getUserProfile($user->ID);
        $profileUserId = $user->ID;

        // Add profile scope
        $show = function () use ($dbProfile, $profileUserId) {
            include_once __DIR__ . '/views/profile.php';
        };
        $show();
    }

    /**
     * Register Yoti widget.
     */
    public static function yoti_register_widget()
    {
        register_widget('YotiWidget');
    }

    /**
     * Add Yoti js.
     */
    public static function yoti_enqueue_scripts()
    {
        wp_enqueue_script('yoti-asset-js', YotiHelper::YOTI_SDK_JAVASCRIPT_LIBRARY, [], NULL);
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
            $noticeHTML = '<div class="notice notice-success is-dismissible">' .
                '<p><strong>Almost done</strong> - Complete Yoti <a style="text-decoration: none;" href="'.
                admin_url( 'options-general.php?page=yoti' ) .'">'. __('settings here', 'yoti') .
                '</a>.</p></div>';

            echo $noticeHTML;
        }
    }
}