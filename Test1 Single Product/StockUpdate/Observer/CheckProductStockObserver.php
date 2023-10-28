<?php
declare(strict_types=1);

namespace RltSquare\StockUpdate\Observer;

use GuzzleHttp\Exception\GuzzleException;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use RltSquare\StockUpdate\Action\CollectQuantityData;
use RltSquare\StockUpdate\Helper\CollectSingleProductStockData;

/**
 * @class CheckProductStockObserver
 */
class CheckProductStockObserver implements ObserverInterface
{
    /**
     * @var StockRegistryInterface
     */
    protected StockRegistryInterface $stockRegistry;
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;
    /**
     * @var CollectSingleProductStockData
     */
    private CollectSingleProductStockData $singleProductStockData;
    /**
     * @var ManagerInterface
     */
    private ManagerInterface $manager;
    /**
     * @var CollectQuantityData
     */
    private CollectQuantityData $collectQuantityData;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @param LoggerInterface $logger
     * @param CollectSingleProductStockData $singleProductStockData
     * @param ManagerInterface $manager
     * @param CollectQuantityData $collectQuantityData
     */
    public function __construct(
        StockRegistryInterface        $stockRegistry,
        LoggerInterface               $logger,
        CollectSingleProductStockData $singleProductStockData,
        ManagerInterface              $manager,
        CollectQuantityData           $collectQuantityData

    )
    {
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
        $this->singleProductStockData = $singleProductStockData;
        $this->manager = $manager;
        $this->collectQuantityData = $collectQuantityData;
    }

    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function execute(Observer $observer): ManagerInterface
    {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();

        $stockItem = $this->stockRegistry->getStockItem($productId);

        if ($stockItem->getQty() <= 2) {

            // Get Data From Big Buy API endpoint
            $quantity = $this->collectQuantityData->GetQuantityData();

            // Update the product stock quantity in Magento
            $stockItem->setQty((float)$quantity);

            $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);

            // Log the quantity update (optional)
            $this->logger->info("Product {$product->getSku()} quantity updated to {$quantity}.");

            $this->logger->info("Product {$product->getSku()} quantity updated to {$quantity}.");

            // Add a success message to notify the user or admin about the quantity update.
            $message = __('Product stock quantity updated to %1.', $quantity);

            // Return the success message
            return $this->manager->addSuccessMessage($message);
        }

        // If the product stock quantity is not updated, return an empty message or null.
        return $this->manager->addErrorMessage('Product Quantity is not update');
    }
}
