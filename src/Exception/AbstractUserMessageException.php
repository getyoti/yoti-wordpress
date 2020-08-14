<?php

namespace Yoti\WP\Exception;

abstract class AbstractUserMessageException extends \RuntimeException implements UserMessageExceptionInterface
{
    private const DEFAULT_MESSAGE = 'An unknown error occurred.';

    /**
     * @param integer $code
     */
    protected function __construct(int $code)
    {
        parent::__construct(self::getUserMessageForCode($code), $code);
    }

    /**
     * @param integer $code
     *
     * @return string
     */
    private static function getUserMessageForCode(int $code): string
    {
        return static::userMessages()[$code] ?? self::DEFAULT_MESSAGE;
    }

    /**
     * @inheritDoc
     */
    public function getUserMessage(): string
    {
        return self::getUserMessageForCode($this->getCode());
    }

    /**
     * Provide user friendly messages for exception codes.
     *
     * @return array<int,string>
     */
    abstract protected static function userMessages(): array;
}
