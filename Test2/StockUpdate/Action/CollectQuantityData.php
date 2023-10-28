<?php
declare(strict_types=1);

namespace RltSquare\StockUpdate\Action;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use RltSquare\StockUpdate\Helper\CollectSingleProductStockData;

/**
 * @class CollectQuantityData
 */
class CollectQuantityData
{
    /**
     * @var CollectSingleProductStockData
     */
    private CollectSingleProductStockData $singleProductStockData;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param CollectSingleProductStockData $singleProductStockData
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectSingleProductStockData $singleProductStockData,
        LoggerInterface               $logger
    )
    {
        $this->singleProductStockData = $singleProductStockData;
        $this->logger = $logger;
    }

    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function GetQuantityData(): array
    {

        // Big Buy API endpoint and credentials
        $data = $this->singleProductStockData->CollectProductStockData();
        $results = [];

        foreach ($data as $item) {
            $productId = $item['id'];

            // Assume there is only one stock item in the stocks array
            if (isset($item['stocks'][0]['quantity'])) {
                $quantity = $item['stocks'][0]['quantity'];
            } else {
                $quantity = 0;
                $this->logger->error("Error retrieving stock quantity for product ID: {$productId}");
            }

            $result = [
                'product_id' => $productId,
                'quantity' => $quantity,
            ];

            $results[] = $result;
        }

        return $results;
    }
}
