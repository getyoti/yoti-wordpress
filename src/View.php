<?php

namespace Yoti\WP;

class View
{
    /**
     * Get path to view.
     *
     * @param string $view_name
     *
     * @return string
     */
    private static function path($view_name)
    {
        return __DIR__ . '/../views/' . basename($view_name) . '.php';
    }

    /**
     * Renders view.
     *
     * @param string $view_name
     * @param array<string,mixed> $variables
     */
    public static function render($view_name, $variables = []): void
    {
        extract($variables, EXTR_SKIP);
        require self::path($view_name);
    }
}
