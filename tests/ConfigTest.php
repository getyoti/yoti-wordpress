<?php

namespace Yoti\WP\Test;

use Yoti\WP\Config;
use Yoti\WP\Service;

/**
 * @coversDefaultClass Yoti\WP\Config
 *
 * @group yoti
 */
class ConfigTest extends TestBase
{
    /**
     * @covers ::load
     */
    public function testGetConfig()
    {
        $this->assertEquals($this->config, Service::config()->load());
    }
}
