<?php

namespace Yoti\WP;

class Views
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

    /**
     * Gets rendered view content as string.
     *
     * @param string $view
     * @param array $variables
     *
     * @return string
     */
    public static function getContent($view_name, $variables = [])
    {
        ob_start();
        self::render($view_name, $variables);
        return ob_get_clean();
    }
}
