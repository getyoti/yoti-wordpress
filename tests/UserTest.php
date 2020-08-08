<?php

namespace Yoti\WP\Test;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Yoti\WP\User;

/**
 * @coversDefaultClass Yoti\WP\User
 *
 * @group yoti
 */
class UserTest extends TestBase
{
    use ExpectDeprecationTrait;

    /**
     * @group legacy
     */
    public function testClassAlias()
    {
        $this->expectDeprecation(sprintf('%s is deprecated, use %s instead', \YotiHelper::class, User::class));
        $this->assertInstanceOf(User::class, new \YotiHelper());
    }
}
