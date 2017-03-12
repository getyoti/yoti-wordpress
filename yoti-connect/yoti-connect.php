<?php
/*
Plugin Name: Yoti Connect Plugin
Plugin URI:
Description: Let Yoti users quickly register on your site
Version: 1.0
Author: Simon Tong
Author URI: http://yoti.com
*/

use Yoti\ActivityDetails;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once __DIR__ . '/YotiConnectHelper.php';
require_once __DIR__ . '/YotiConnectAdmin.php';
require_once __DIR__ . '/YotiConnectButton.php';

/**
 * Activation hook
 */
function yoti_connect_activation_hook()
{
    // create upload dir
    if (!is_dir(YotiConnectHelper::uploadDir()))
    {
        mkdir(YotiConnectHelper::uploadDir(), 0777, true);
    }

    //    $table_name = YotiConnectHelper::tableName();
    //    $sql = "CREATE TABLE IF NOT EXISTS `{$table_name}` (
    //        `wp_userid` bigint(20) UNSIGNED NOT NULL,
    //        `identifier` TEXT NOT NULL,
    //        `nationality` VARCHAR(255) NULL,
    //        `date_of_birth` VARCHAR(255) NULL,
    //        `selfie_filename` VARCHAR(255) NULL,
    //        `phone_number` VARCHAR(255) NULL,
    //        KEY `wp_userid` (`wp_userid`)
    //    )";
    //    dbDelta($sql);
}

/**
 * Deactivation hook
 */
function yoti_connect_deactivation_hook()
{
    //    $table_name = YotiConnectHelper::tableName();
    //    $sql = "DROP TABLE IF EXISTS `{$table_name}`";
    //    dbDelta($sql);
}

/**
 *  init
 */
function yoti_connect_init()
{
    if (!empty($_GET['yoti-connect']))
    {
        $yc = new YotiConnectHelper();

        // action
        $action = !empty($_GET['action']) ? $_GET['action'] : '';
        $redirect = (!empty($_GET['redirect'])) ? $_GET['redirect'] : '/';
        switch ($action)
        {
            case 'link':
                if ($yc->link())
                {
                    wp_safe_redirect($redirect);
                }
                break;

            case 'unlink':
                if ($yc->unlink())
                {
                    wp_redirect($redirect);
                }
                break;

            case 'bin-file':
                $yc->binFile('selfie', !empty($_GET['user_id']) ? $_GET['user_id'] : null);
                exit;
                break;
        }
    }
}

/**
 * Add items to admin menu
 */
function yoti_connect_admin_menu()
{
    wp_enqueue_style('yoti-connect', plugin_dir_url(__FILE__) . 'assets/styles.css', false);
    add_options_page('Yoti Connect', 'Yoti Connect', 'manage_options', 'yoti-connect', 'YotiConnectAdmin::init');
}

/**
 * add to login footer
 */
function yoti_connect_login_footer()
{
    $config = YotiConnectHelper::getConfig();
    if (!empty($config['yoti_sdk_id']) && !empty($config['yoti_pem']['contents']))
    {
        wp_enqueue_style('yoti-connect', plugin_dir_url(__FILE__) . 'assets/styles.css', false);
        echo YotiConnectButton::render();
    }
}

/**
 * @param WP_User $user
 */
function show_user_profile($user)
{
    $yotiId = get_user_meta($user->ID, 'yoti_connect.identifier');
    $dbProfile = YotiConnectHelper::getUserProfile($user->ID);

    $profile = null;
    if ($yotiId && $dbProfile)
    {
        $profile = new ActivityDetails($dbProfile, $yotiId);
    }

    // add scope
    $show = function () use ($profile, $dbProfile)
    {
        require_once __DIR__ . '/views/profile.php';
    };
    $show();
}

// register hooks
register_deactivation_hook(__FILE__, 'yoti_connect_deactivation_hook');
register_activation_hook(__FILE__, 'yoti_connect_activation_hook');
add_action('admin_menu', 'yoti_connect_admin_menu');
add_action('init', 'yoti_connect_init');
add_action('login_form', 'yoti_connect_login_footer');
add_action('show_user_profile', 'show_user_profile', 10, 1);
add_action('edit_user_profile', 'show_user_profile', 10, 1);
