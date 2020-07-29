<?php
/**
 * PHPUnit bootstrap file
 */

// Start the session before any console output.
if (!session_id()) {
    session_start();
}

$tests_dir = rtrim(getenv('WP_TESTS_DIR'), '/');
if (!is_dir($tests_dir)) {
    $tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
    $plugin_dir = getenv('WP_PLUGIN_DIR') ?: __DIR__ . '/../yoti';
    if (!is_dir($plugin_dir)) {
        throw new RuntimeException(sprintf('%s is not a directory. Set plugin path using WP_PLUGIN_DIR environment variable', $plugin_dir));
    }
    require rtrim($plugin_dir, '/') . '/yoti.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment.
require $tests_dir . '/includes/bootstrap.php';

spl_autoload_register(function ($class) {
    $prefix = 'Yoti\\WP\\Test\\';

    $base_dir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);

    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
