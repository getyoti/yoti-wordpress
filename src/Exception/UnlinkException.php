<?php

namespace Yoti\WP\Exception;

/**
 * Provides user friendly messages to display when account cannot be unlinked.
 *
 * Unlink exceptions can only be created using predefined static methods.
 */
final class UnlinkException extends \RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function couldNotUnlink(): self
    {
        return new static('Could not unlink from Yoti.');
    }

    public static function couldNotDeleteImage(): self
    {
        return new static('Could not delete user image.');
    }
}
