<?php

namespace Yoti\WP\Exception;

use Throwable;

interface UserMessageExceptionInterface extends Throwable
{
    /**
     * Provides user friendly messages to display.
     *
     * @return string
     */
    public function getUserMessage(): string;
}
