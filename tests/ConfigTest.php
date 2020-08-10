<?php

namespace Yoti\WP\Test;

use Yoti\WP\Config;
use Yoti\WP\Service;

/**
 * @group yoti
 */
class ConfigTest extends TestBase
{
    public function testGetConfig()
    {
        $this->assertEquals($this->config, Service::config()->load());
    }

    /**
     * @runInSeparateProcess
     */
    public function testCustomUploadDir()
    {
        define('YOTI_UPLOAD_DIR', '/some/upload/dir/');

        $this->assertEquals(Service::config()->uploadDir(), '/some/upload/dir');
    }

    /**
     * @runInSeparateProcess
     */
    public function testUploadDir()
    {
        $this->assertEquals(Service::config()->uploadDir(), WP_CONTENT_DIR . '/uploads/yoti');
    }
}
