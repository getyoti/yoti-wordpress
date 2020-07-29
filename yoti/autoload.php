<?php

spl_autoload_register(function ($class) {
    // Alias older classes to new autoloaded namespaced class.
    $deprecated = [
        'Yoti' => Yoti\WP\Hooks::class,
        'YotiWidget' => Yoti\WP\Widget::class,
        'YotiHelper' => Yoti\WP\Helper::class,
        'YotiButton' => Yoti\WP\Button::class,
        'YotiAdmin' => Yoti\WP\Admin::class,
    ];
    if (isset($deprecated[$class])) {
        @trigger_error(
            sprintf(
                '%s is deprecated, use %s instead',
                esc_html($class),
                esc_html($deprecated[$class])
            ),
            E_USER_DEPRECATED
        );
        class_alias($deprecated[$class], $class);
    }

    $prefix = 'Yoti\\WP\\';

    $base_dir = __DIR__ . '/src/';

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
