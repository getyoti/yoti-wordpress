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
     * @param $message
     * @param string $type
     */
    public static function setFlash($message, $type = 'message')
    {
        $_SESSION['yoti-connect-flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * Get user notification message.
     *
     * @return mixed
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
    public static function clearFlash()
    {
        unset($_SESSION['yoti-connect-flash']);
    }
}
