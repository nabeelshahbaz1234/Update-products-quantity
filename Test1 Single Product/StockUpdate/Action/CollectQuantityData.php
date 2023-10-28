<?php
declare(strict_types=1);

namespace RltSquare\StockUpdate\Action;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Exception\LocalizedException;
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
     * @param CollectSingleProductStockData $singleProductStockData
     */
    public function __construct(
        CollectSingleProductStockData $singleProductStockData
    )
    {
        $this->singleProductStockData = $singleProductStockData;
    }


    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function GetQuantityData()
    {
        $quantity = 0;
        // Big Buy API endpoint and credentials
        $data = $this->singleProductStockData->CollectProductStockData();
        if (isset($data['stocks']) && is_array($data['stocks']) && count($data['stocks']) > 0) {
            $quantity = $data['stocks'][0]['quantity'];
        }
        return $quantity;

    }
}
