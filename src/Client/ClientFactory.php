<?php

namespace Yoti\WP\Client;

use Yoti\DocScan\DocScanClient;
use Yoti\Util\Config as YotiConfig;
use Yoti\WP\Config;
use Yoti\WP\Constants;
use Yoti\WP\Exception\ClientConfigException;
use Yoti\YotiClient;

/**
 * Class ClientFactory
 */
class ClientFactory implements ClientFactoryInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @return array<string,string>
     */
    private function getOptions(): array
    {
        return [
            YotiConfig::SDK_IDENTIFIER => Constants::SDK_IDENTIFIER,
            YotiConfig::SDK_VERSION => Constants::SDK_VERSION,
        ];
    }

    /**
     * @return string
     */
    private function getClientSdkId(): string
    {
        $clientSdkId = $this->config->getClientSdkId();
        if ($clientSdkId === null) {
            throw new ClientConfigException('Client SDK ID has not been configured');
        }
        return $clientSdkId;
    }

    /**
     * @return string
     */
    private function getPemContent(): string
    {
        $pemContent = $this->config->getPemContent();
        if ($pemContent === null) {
            throw new ClientConfigException('PEM file has not been configured');
        }
        return $pemContent;
    }

    /**
     * @inheritDoc
     */
    public function getClient(): YotiClient
    {
        return new YotiClient(
            $this->getClientSdkId(),
            $this->getPemContent(),
            $this->getOptions()
        );
    }

    /**
     * @inheritDoc
     */
    public function getDocScanClient(): DocScanClient
    {
        return new DocScanClient(
            $this->getClientSdkId(),
            $this->getPemContent(),
            $this->getOptions()
        );
    }
}
