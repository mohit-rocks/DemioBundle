<?php

declare(strict_types=1);

namespace MauticPlugin\DemioBundle\Connection;

use GuzzleHttp\Exception\ClientException;
use Mautic\CoreBundle\Helper\CacheStorageHelper;
use Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException;
use MauticPlugin\DemioBundle\Integration\Config;
use MauticPlugin\DemioBundle\Integration\DemioIntegration;
use Monolog\Logger;
use Symfony\Component\Routing\Router;

class Client
{
    private $apiUrl;

    /**
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var \Mautic\CoreBundle\Helper\CacheStorageHelper
     */
    private $cacheProvider;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        CacheStorageHelper $cacheProvider,
        Router $router,
        Logger $logger,
        Config $config
    ) {
        $this->httpClient       = new \GuzzleHttp\Client();
        $this->logger           = $logger;
        $this->router           = $router;
        $this->cacheProvider    = $cacheProvider;
        $this->config           = $config;

        // Get the API keys and initialize API Host.
        $apiKeys                = $this->config->getApiKeys();
        $this->apiUrl           = $apiKeys['host'];
    }

    public function validateCredentials(string $apiUrl, string $key, string $secret): bool
    {
        try {
            $response = $this->httpClient->get($apiUrl . '/ping', [
                'headers' => [
                    'Api-Key'       => $key,
                    'Api-Secret'    => $secret,
                ],
                'curl' => [
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_HEADER => FALSE,
                ],
            ]);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $this->logger->error(
                sprintf(
                    '%s: Error validating API credential: %s',
                    DemioIntegration::DISPLAY_NAME,
                    $response->getReasonPhrase()
                )
            );
        }

        if (200 !== (int) $response->getStatusCode()) {
            return false;
        }

        return true;
    }

    /**
     * Fetch the API data from the endpoint.
     *
     * @param string $url
     *   API endpoint URL.
     *
     * @return array
     *   Array with values or empty array.
     */
    public function get(string $url) {
        $results = [];
        $credentials = $this->getCredentials();

        try {
            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $this->httpClient->get($this->apiUrl . $url, [
                'headers' => [
                    'Api-Key'       => $credentials->getKey(),
                    'Api-Secret'    => $credentials->getApiSecret(),
                ],
                'curl' => [
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_HEADER => FALSE,
                ],
            ]);
            if ($response->getStatusCode() == 200) {
                return $response->getBody()->getContents();
            }
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $this->logger->error(
                sprintf(
                    'Something went wrong with the request. Please check API endpoints.',
                    DemioIntegration::DISPLAY_NAME,
                    $response->getReasonPhrase()
                )
            );
        }
    }

    /**
     * @return \MauticPlugin\DemioBundle\Connection\Credentials
     *
     * @throws \Mautic\IntegrationsBundle\Exception\PluginNotConfiguredException
     */
    private function getCredentials(): Credentials
    {
        if (!$this->config->isConfigured()) {
            throw new PluginNotConfiguredException();
        }

        $apiKeys = $this->config->getApiKeys();

        return new Credentials($apiKeys['key'], $apiKeys['secret']);
    }
}
