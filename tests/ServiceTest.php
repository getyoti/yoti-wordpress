<?php

namespace Yoti\WP\Test;

use Yoti\WP\Client\ClientFactoryInterface;
use Yoti\WP\Config;
use Yoti\WP\Service;
use Yoti\WP\User;

/**
 * @group yoti
 */
class ServiceTest extends TestBase
{
    public function testUser()
    {
        $user = Service::user();
        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($user, Service::user());
    }

    public function testConfig()
    {
        $config = Service::config();
        $this->assertInstanceOf(Config::class, Service::config());
        $this->assertSame($config, Service::config());
    }

    public function testClientFactory()
    {
        $clientFactory = Service::clientFactory();
        $this->assertInstanceOf(ClientFactoryInterface::class, $clientFactory);
        $this->assertSame($clientFactory, Service::clientFactory());
    }
}
