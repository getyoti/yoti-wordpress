<?php

namespace Yoti\WP\Client;

use Yoti\DocScan\DocScanClient;
use Yoti\YotiClient;

/**
 * Class ClientFactoryInterface
 */
interface ClientFactoryInterface
{
    /**
     * @return YotiClient
     */
    public function getClient(): YotiClient;

    /**
     * @return DocScanClient
     */
    public function getDocScanClient(): DocScanClient;
}
