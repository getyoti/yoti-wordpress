<?php

namespace Yoti\WP\Test;

use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Yoti\WP\Helper;

/**
 * @coversDefaultClass Yoti\WP\Helper;
 *
 * @group yoti
 */
class HelperTest extends TestBase
{
    use ExpectDeprecationTrait;

    /**
     * @covers ::getConfig
     */
    public function testGetConfig()
    {
        $this->assertEquals($this->config, Helper::getConfig());
    }

    /**
     * @group legacy
     */
    public function testClassAlias()
    {
        $this->expectDeprecation(sprintf('%s is deprecated, use %s instead', \YotiHelper::class, Helper::class));
        $this->assertInstanceOf(Helper::class, new \YotiHelper());
    }
}
