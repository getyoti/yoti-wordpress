<?php

namespace Yoti\WP;

class View
{
    /**
     * Get path to view.
     *
     * @param string $view
     *
     * @return string
     */
    private static function path($view_name) {
        return __DIR__ . '/../views/' . basename($view_name) . '.php';
    }

    /**
     * Renders view.
     *
     * @param string $view
     * @param array $variables
     */
    public static function render($view_name, $variables = [])
    {
        extract($variables, EXTR_SKIP);
        require self::path($view_name);
    }
}
