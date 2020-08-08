<?php

namespace Yoti\WP\Test;

use Yoti\WP\Config;

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
        $this->assertEquals($this->config, Config::load());
    }
}
