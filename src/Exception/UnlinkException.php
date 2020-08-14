<?php

namespace Yoti\WP\Exception;

final class UnlinkException extends AbstractUserMessageException
{
    private const CODE_UNLINK = 0;
    private const CODE_DELETE_IMAGE = 1;

    /**
     * @inheritDoc
     */
    protected static function userMessages(): array
    {
        return [
            self::CODE_UNLINK => 'Could not unlink from Yoti.',
            self::CODE_DELETE_IMAGE => 'Could not delete user image.',
        ];
    }

    public static function couldNotUnlink(): self
    {
        return new static(self::CODE_UNLINK);
    }

    public static function couldNotDeleteImage(): self
    {
        return new static(self::CODE_DELETE_IMAGE);
    }
}
