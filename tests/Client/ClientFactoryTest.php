<?php

namespace Yoti\WP\Test\Client;

use Yoti\DocScan\DocScanClient;
use Yoti\WP\Client\ClientFactory;
use Yoti\WP\Service;
use Yoti\WP\Test\TestBase;
use Yoti\YotiClient;

/**
 * @group yoti
 */
class ClientFactoryTest extends TestBase
{
    /**
     * @var ClientFactory
     */
    private $clientFactory;

    public function setup()
    {
        parent::setup();

        $this->clientFactory = new ClientFactory(Service::config());
    }

    public function testGetClient()
    {
        $this->assertInstanceOf(YotiClient::class, $this->clientFactory->getClient());
    }

    public function testGetDocScanClient()
    {
        $this->assertInstanceOf(DocScanClient::class, $this->clientFactory->getDocScanClient());
    }
}
