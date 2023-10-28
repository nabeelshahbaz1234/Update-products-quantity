<?php

declare(strict_types=1);

namespace RltSquare\StockUpdate\Helper;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RltSquare\BigBuyConnector\Model\Config;


/**
 * @class GatheringManufactureData
 */
class CollectSingleProductStockData
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param LoggerInterface $logger
     * @param Config $config
     */
    public function __construct(
        LoggerInterface $logger,
        Config          $config
    )
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */

    public function CollectProductStockData($productId): array
    {
        if ($this->config->isEnabled()) {
            $apiToken = $this->config->getApiToken();
//            $apiUrl = $this->config->returnAllProductWithAvailableStock();
            $apiUrl = "https://api.sandbox.bigbuy.eu/rest/catalog/productstock/$productId.json";


            $client = new Client();
            $options = [
                'headers' => [
                    'Context-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiToken,
                ],
            ];
            try {
                // Delay the execution by 5 seconds
                sleep(5);
                $response = $client->get($apiUrl, $options);
                $response = $this->processResponse($response);
            } catch (GuzzleException $guzzleException) {
                $this->logger->error($guzzleException->getMessage());
                throw $guzzleException;
            }
            return $response;
        } else {
            throw new LocalizedException(__('BigBuy export module is disabled'));
        }
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws LocalizedException
     */
    private function processResponse(ResponseInterface $response): array
    {
        $responseBody = (string)$response->getBody();
        try {
            $responseData = json_decode($responseBody, true);
        } catch (Exception $e) {
            $responseData = [];
        }
        if ($response->getStatusCode() !== 200) {
            throw new LocalizedException(__('There was Problem making Connection!'));
        }
        return $responseData;
    }
}
