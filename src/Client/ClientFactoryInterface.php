<?php

namespace Yoti\WP\Client;

use Yoti\DocScan\DocScanClient;
use Yoti\WP\Exception\ClientConfigException;
use Yoti\YotiClient;

/**
 * Class ClientFactoryInterface
 */
interface ClientFactoryInterface
{
    /**
     * @return YotiClient
     *
     * @throws ClientConfigException
     */
    public function getClient(): YotiClient;

    /**
     * @return DocScanClient
     *
     * @throws ClientConfigException
     */
    public function getDocScanClient(): DocScanClient;
}
