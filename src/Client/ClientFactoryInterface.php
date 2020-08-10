<?php

namespace Yoti\WP\Client;

use Yoti\DocScan\DocScanClient;
use Yoti\YotiClient;

/**
 * Class ClientFactoryInterface
 */
interface ClientFactoryInterface
{
    public function getClient(): YotiClient;
    public function getDocScanClient(): DocScanClient;
}
