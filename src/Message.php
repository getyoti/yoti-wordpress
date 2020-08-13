<?php

namespace Yoti\WP;

/**
 * Class User
 */
class Message
{
    /**
     * Set user notification message.
     *
     * @param string $message
     * @param string $type
     */
    public static function setFlash($message, $type = 'message'): void
    {
        $_SESSION['yoti-connect-flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Get user notification message.
     *
     * @return array<string,string>
     */
    public static function getFlash()
    {
        $message = null;
        if (!empty($_SESSION['yoti-connect-flash'])) {
            $message = $_SESSION['yoti-connect-flash'];
            self::clearFlash();
        }
        return $message;
    }

    /**
     * Clear Yoti flash message.
     */
    public static function clearFlash(): void
    {
        unset($_SESSION['yoti-connect-flash']);
    }
}
