<?php

namespace Yoti\WP\Client;

use Yoti\DocScan\DocScanClient;
use Yoti\Util\Config as YotiConfig;
use Yoti\WP\Constants;
use Yoti\WP\Config;
use Yoti\YotiClient;

/**
 * Class ClientFactory
 */
class ClientFactory implements ClientFactoryInterface
{
    /**
     * @var string
     */
    private $clientSdkId;

    /**
     * @var string
     */
    private $pem;

    /**
     * @var array<string, mixed>
     */
    private $options;

    public function __construct(Config $config)
    {
        $this->clientSdkId = $config->get('yoti_sdk_id');
        $this->pem = $config->get('yoti_pem')['contents'];

        $this->options = [
            YotiConfig::SDK_IDENTIFIER => Constants::SDK_IDENTIFIER,
            YotiConfig::SDK_VERSION => Constants::SDK_VERSION,
        ];
    }

    /**
     * @return YotiClient
     */
    public function getClient(): YotiClient
    {
        return new YotiClient(
            $this->clientSdkId,
            $this->pem,
            $this->options
        );
    }

    /**
     * @return DocScanClient
     */
    public function getDocScanClient(): DocScanClient
    {
        return new DocScanClient(
            $this->clientSdkId,
            $this->pem,
            $this->options
        );
    }
}
