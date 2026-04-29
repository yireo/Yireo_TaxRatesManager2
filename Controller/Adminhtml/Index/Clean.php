<?php
/**
 * Yireo TaxRatesManager2 extension for Magento 2
 *
 * @package     Yireo_TaxRatesManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Yireo\TaxRatesManager2\Check\Check;
use Yireo\TaxRatesManager2\Provider\OnlineRates as OnlineRatesProvider;
use Yireo\TaxRatesManager2\Provider\StoredRates as StoredRatesProvider;

/**
 * Class Clean
 *
 * @package Yireo\TaxRatesManager2\Controller\Adminhtml\Index
 */
class Clean extends Action
{
    const ADMIN_RESOURCE = 'Yireo_TaxRatesManager2::index';

    /**
     * @var StoredRatesProvider
     */
    private $storedRatesProvider;

    /**
     * Fix constructor.
     *
     * @param Context $context
     * @param RedirectFactory $resultRedirectFactory
     * @param Check $check
     * @param StoredRatesProvider $storedRatesProvider
     * @param OnlineRatesProvider $onlineRatesProvider
     */
    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        StoredRatesProvider $storedRatesProvider,
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->storedRatesProvider = $storedRatesProvider;
    }


    /**
     * @return ResultInterface
     */
    public function execute()
    {
        $this->clean();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('tax/rate');
        return $resultRedirect;
    }

    /**
     * Remove all existing rates
     */
    private function clean()
    {
        $rates = $this->storedRatesProvider->getRates();
        foreach ($rates as $rate) {
            try {
                $this->storedRatesProvider->deleteRate($rate);
            } catch (CouldNotDeleteException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                continue;
            }
        }
    }
}
