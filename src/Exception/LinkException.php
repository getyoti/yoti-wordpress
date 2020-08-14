<?php

namespace Yoti\WP\Exception;

/**
 * Provides user friendly messages to display when account cannot be linked.
 *
 * Link exceptions can only be created using predefined static methods.
 */
final class LinkException extends \RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function alreadyLinked(): self
    {
        return new static('This Yoti account is already linked to another account.');
    }

    public static function noToken(): self
    {
        return new static('Could not get Yoti token.');
    }

    public static function couldNotConnect(): self
    {
        return new static('Yoti failed to connect to your account.');
    }

    public static function failedAgeVerification(): self
    {
        return new static('Could not log you in as you haven\'t passed the age verification.');
    }

    public static function missingRememberMeId(): self
    {
        return new static('Could not create user account without Remember Me ID.');
    }

    public static function couldNotCreateAccount(): self
    {
        return new static('Could not create user account.');
    }
}
