<?php

namespace Yoti\WP\Exception;

final class LinkException extends AbstractUserMessageException
{
    private const CODE_CONNECT = 0;
    private const CODE_ALREADY_LINKED = 1;
    private const CODE_NO_TOKEN = 2;
    private const CODE_AGE_VERIFICATION = 3;
    private const CODE_MISSING_REMEMBER_ME_ID = 4;
    private const CODE_ACCOUNT_CREATION = 5;

    /**
     * @inheritDoc
     */
    protected static function userMessages(): array
    {
        return [
            self::CODE_CONNECT => 'Yoti failed to connect to your account.',
            self::CODE_ALREADY_LINKED => 'This Yoti account is already linked to another account.',
            self::CODE_NO_TOKEN => 'Could not get Yoti token.',
            self::CODE_AGE_VERIFICATION => "Could not log you in as you haven't passed the age verification.",
            self::CODE_MISSING_REMEMBER_ME_ID => 'Could not create user account without Remember Me ID.',
            self::CODE_ACCOUNT_CREATION => 'Could not create user account.',
        ];
    }

    public static function alreadyLinked(): self
    {
        return new static(self::CODE_ALREADY_LINKED);
    }

    public static function noToken(): self
    {
        return new static(self::CODE_NO_TOKEN);
    }

    public static function couldNotConnect(): self
    {
        return new static(self::CODE_CONNECT);
    }

    public static function failedAgeVerification(): self
    {
        return new static(self::CODE_AGE_VERIFICATION);
    }

    public static function missingRememberMeId(): self
    {
        return new static(self::CODE_MISSING_REMEMBER_ME_ID);
    }

    public static function couldNotCreateAccount(): self
    {
        return new static(self::CODE_ACCOUNT_CREATION);
    }
}
