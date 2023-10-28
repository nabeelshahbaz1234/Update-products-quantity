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
    public function GetQuantityData($productId)
    {

        // Big Buy API endpoint and credentials
        $quantity = 0;
        // Big Buy API endpoint and credentials
        $data = $this->singleProductStockData->CollectProductStockData($productId);
        if (isset($data['stocks']) && is_array($data['stocks']) && count($data['stocks']) > 0) {
            $quantity = $data['stocks'][0]['quantity'];
        }
        return $quantity;
    }
}
