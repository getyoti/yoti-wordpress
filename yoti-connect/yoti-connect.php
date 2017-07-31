<?php

/*
Plugin Name: Yoti Connect Plugin
Plugin URI:
Description: Let Yoti users quickly register on your site
Version: 1.1.1
Author: Moussa Sidibe
Author URI: http://yoti.com
*/

use Yoti\ActivityDetails;

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
require_once __DIR__ . '/YotiConnectHelper.php';
require_once __DIR__ . '/YotiConnectAdmin.php';
require_once __DIR__ . '/YotiConnectButton.php';
require_once __DIR__ . '/YotiWidget.php';

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
}

/**
 * Uninstall hook
 */
function yoti_connect_uninstall_hook()
{
    YotiConnectHelper::deleteYotiConfigData();
}

/**
 *  init
 */
function yoti_connect_init()
{
    if (!session_id())
    {
        session_start();
    }

    if (!empty($_GET['yoti-connect']))
    {
        $yc = new YotiConnectHelper();

        // action
        $action = !empty($_GET['action']) ? $_GET['action'] : '';
        $redirect = (!empty($_GET['redirect'])) ? $_GET['redirect'] : home_url();
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
                    wp_safe_redirect($redirect);
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
function yoti_connect_login_header()
{
    // don't allow unless session
    if (!YotiConnectHelper::getYotiUserFromStore())
    {
        return;
    }

    $noLink = (!empty($_POST['yoti_nolink'])) ? 1 : null;

    echo '<div style="margin: 0 0 25px 0" class="message">
        <div style="font-weight: bold; margin-bottom: 5px;">Warning: You are about to link Wordpress to your Yoti account. Click the box below to keep them separate.</div>
        <input type="checkbox" id="edit-yoti-link" name="yoti_nolink" value="1" class="form-checkbox"' . ($noLink ? ' checked="checked"' : '') . '>
        <label class="option" for="edit-yoti-link">Don\'t link my Yoti account</label>
    </div>';
}

/**
 * @param $user_login
 * @param $user
 */
function yoti_connect_login($user_login=null, $user=null)
{
    if (!$user) {
        return;
    }

    $activityDetails = YotiConnectHelper::getYotiUserFromStore();
    $yotiNoLinkIsNotChecked = (!isset($_POST['yoti_nolink']) || empty($_POST['yoti_nolink']));
    
    // Check that activityDetails exists and yoti_nolink button is not checked
    if ($activityDetails && $yotiNoLinkIsNotChecked)
    {
        // link account
        $helper = new YotiConnectHelper();
        $helper->createYotiUser($user->ID, $activityDetails);
    }

    // remove session
    unset($_SESSION['yoti_nolink']);
    YotiConnectHelper::clearYotiUserStore();
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
    $show = function () use ($profile, $dbProfile) {
        require_once __DIR__ . '/views/profile.php';
    };
    $show();
}

function yoti_register_widget()
{
    register_widget('YotiWidget');
}

function yoti_enqueue_scripts()
{
    wp_enqueue_script('yoti-connect', 'https://sdk.yoti.com/clients/browser.js', array(), null);
}

// register hooks
register_uninstall_hook(__FILE__, 'yoti_connect_uninstall_hook');
register_activation_hook(__FILE__, 'yoti_connect_activation_hook');
add_action('admin_menu', 'yoti_connect_admin_menu');
add_action('init', 'yoti_connect_init');
add_action('login_form', 'yoti_connect_login_header');
add_action('wp_login', 'yoti_connect_login', 10, 2);
add_action('show_user_profile', 'show_user_profile', 10, 1);
add_action('edit_user_profile', 'show_user_profile', 10, 1);
add_action('widgets_init', 'yoti_register_widget');
add_action('wp_enqueue_scripts', 'yoti_enqueue_scripts');

