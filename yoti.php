<?php

/*
Plugin Name: Yoti
Plugin URI: https://wordpress.org/plugins/yoti/
Description: Let Yoti users quickly register on your site.
Version: 2.0.0
Author: Yoti SDK.
Author URI: https://yoti.com
*/

// Make sure we don't expose any info if called directly

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use Yoti\WP\Hooks;

// Register Yoti hooks.
register_uninstall_hook(__FILE__, [Hooks::class, 'uninstall']);
register_activation_hook(__FILE__, [Hooks::class, 'activation']);
add_action('init', [Hooks::class, 'init']);
add_action('admin_menu', [Hooks::class, 'adminMenu']);
add_action('login_form', [Hooks::class, 'loginHeader']);
add_action('wp_login', [Hooks::class, 'login'], 10, 2);
add_action('wp_logout', [Hooks::class, 'logout'], 10, 2);
add_action('show_user_profile', [Hooks::class, 'showUserProfile'], 10, 1);
add_action('edit_user_profile', [Hooks::class, 'showUserProfile'], 10, 1);
add_action('widgets_init', [Hooks::class, 'registerWidget']);
add_action('wp_enqueue_scripts', [Hooks::class, 'enqueueScripts']);
add_action('admin_notices', [Hooks::class, 'pluginActivateNotice']);
add_filter('plugin_action_links_' . plugin_basename(__FILE__), [Hooks::class, 'pluginActionLinks'], 10, 2);
