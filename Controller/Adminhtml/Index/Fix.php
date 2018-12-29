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
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\TaxRatesManager2\Check\Check;
use Yireo\TaxRatesManager2\Provider\OnlineRates as OnlineRatesProvider;
use Yireo\TaxRatesManager2\Provider\StoredRates as StoredRatesProvider;

/**
 * Class Fix
 *
 * @package Yireo\TaxRatesManager2\Controller\Adminhtml\Index
 */
class Fix extends Action
{
    const ADMIN_RESOURCE = 'Yireo_TaxRatesManager2::index';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Check
     */
    private $check;

    /**
     * @var StoredRatesProvider
     */
    private $storedRatesProvider;

    /**
     * @var OnlineRatesProvider
     */
    private $onlineRatesProvider;

    /**
     * Fix constructor.
     *
     * @param Context $context
     * @param RedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param Check $check
     * @param StoredRatesProvider $storedRatesProvider
     * @param OnlineRatesProvider $onlineRatesProvider
     */
    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        RequestInterface $request,
        Check $check,
        StoredRatesProvider $storedRatesProvider,
        OnlineRatesProvider $onlineRatesProvider
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request = $request;
        $this->check = $check;
        $this->storedRatesProvider = $storedRatesProvider;
        $this->onlineRatesProvider = $onlineRatesProvider;
    }

    /**
     * @return ResultInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $id = (int)$this->request->getParam('id');
        $this->fix($id);

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('tax/rate');
        return $resultRedirect;
    }

    /**
     * Fix a rate with specific ID
     *
     * @param int $id
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function fix(int $id)
    {
        $rate = $this->storedRatesProvider->getRateById($id);
        $onlineRates = $this->onlineRatesProvider->getRates();
        $this->check->setFixAutomatically(true);
        $this->check->checkStoredRate($rate, $onlineRates);
    }
}
