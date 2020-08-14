<?php

namespace Yoti\WP\Exception;

interface UserMessageExceptionInterface extends \Throwable
{
    /**
     * Provides user friendly messages to display.
     *
     * @return string
     */
    public function getUserMessage(): string;
}
