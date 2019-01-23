<?php

/*
Plugin Name: Yoti
Plugin URI: https://wordpress.org/plugins/yoti/
Description: Let Yoti users quickly register on your site.
Version: 1.2.0
Author: Yoti SDK.
Author URI: https://yoti.com
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

require_once ABSPATH . 'wp-admin/includes/upgrade.php';
require_once __DIR__ . '/class.yoti.php';

// Register Yoti hooks.
register_uninstall_hook(__FILE__, array('Yoti', 'yoti_uninstall_hook'));
register_activation_hook(__FILE__, array('Yoti', 'yoti_activation_hook'));
add_action('init', array('Yoti', 'yoti_init'));
add_action('admin_menu', array('Yoti', 'yoti_admin_menu'));
add_action('login_form', array('Yoti', 'yoti_login_header'));
add_action('wp_login', array('Yoti', 'yoti_login'), 10, 2);
add_action('wp_logout', array('Yoti', 'yoti_logout'), 10, 2);
add_action('show_user_profile', array('Yoti', 'show_user_profile'), 10, 1);
add_action('edit_user_profile', array('Yoti', 'show_user_profile'), 10, 1);
add_action('widgets_init', array('Yoti', 'yoti_register_widget'));
add_action('wp_enqueue_scripts', array('Yoti', 'yoti_enqueue_scripts'));
add_action('admin_notices', array('Yoti', 'yoti_plugin_activate_notice'));
add_filter('plugin_action_links_' . plugin_basename(__FILE__), array('Yoti', 'yoti_plugin_action_links'), 10, 2);

