<?php
declare(strict_types=1);

namespace RltSquare\StockUpdate\Observer;

use GuzzleHttp\Exception\GuzzleException;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Checkout\Model\Session;
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
     * @var Session
     */
    private Session $checkoutSession;

    /**
     * @param StockRegistryInterface $stockRegistry
     * @param LoggerInterface $logger
     * @param CollectSingleProductStockData $singleProductStockData
     * @param ManagerInterface $manager
     * @param CollectQuantityData $collectQuantityData
     * @param Session $checkoutSession
     */
    public function __construct(
        StockRegistryInterface        $stockRegistry,
        LoggerInterface               $logger,
        CollectSingleProductStockData $singleProductStockData,
        ManagerInterface              $manager,
        CollectQuantityData           $collectQuantityData,
        Session                       $checkoutSession

    )
    {
        $this->stockRegistry = $stockRegistry;
        $this->logger = $logger;
        $this->singleProductStockData = $singleProductStockData;
        $this->manager = $manager;
        $this->collectQuantityData = $collectQuantityData;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @throws GuzzleException
     * @throws LocalizedException
     */
    public function execute(Observer $observer,): void
    {

        $products = $this->checkoutSession->getQuote()->getAllVisibleItems();

        foreach ($products as $product) {
            $productId = $product->getProductId();

            $newQuantity = $this->collectQuantityData->GetQuantityData($productId);

            $stockItem = $this->stockRegistry->getStockItem($productId);

            if ($stockItem->getQty() <= 2) {

                // Check if the quantity needs updating
                if ($newQuantity !== $stockItem->getQty()) {
                    $stockItem->setQty($newQuantity); // Corrected line
                    $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);

                    // Log the quantity update (optional)
                    $this->logger->info("Product {$product->getSku()} quantity updated to {$newQuantity}.");

                    // Add a success message to notify the user or admin about the quantity update.
                    $message = __('Product stock quantity updated to %1.', $newQuantity);

                    // Return the success message (You can remove this if it's not necessary to return anything)
                    $this->manager->addSuccessMessage($message);

                }
            }
        }
        $this->logger->error("Product ID {$productId} not found in the API response data or quantity does not need updating.");
    }
}
