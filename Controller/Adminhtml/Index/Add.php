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
use Magento\Framework\Exception\NotFoundException;
use Yireo\TaxRatesManager2\Provider\StoredRates as StoredRatesProvider;
use Yireo\TaxRatesManager2\Provider\OnlineRates as OnlineRatesProvider;
use Yireo\TaxRatesManager2\Rate\Rate;

/**
 * Class Add
 *
 * @package Yireo\TaxRatesManager2\Controller\Adminhtml\Index
 */
class Add extends Action
{
    const ADMIN_RESOURCE = 'Yireo_TaxRatesManager2::index';

    /**
     * @var RequestInterface
     */
    private $request;

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
     * @param Context $context
     * @param RedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param StoredRatesProvider $storedRatesProvider
     * @param OnlineRatesProvider $onlineRatesProvider
     */
    public function __construct(
        Context $context,
        RedirectFactory $resultRedirectFactory,
        RequestInterface $request,
        StoredRatesProvider $storedRatesProvider,
        OnlineRatesProvider $onlineRatesProvider
    ) {
        parent::__construct($context);
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->request = $request;
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
        $country = (string)$this->request->getParam('country');
        $percentage = (float)$this->request->getParam('percentage');
        $code = $this->getCodeByCountryAndPercentage($country, $percentage);
        $this->add($code, $country, $percentage);

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('tax/rate');
        return $resultRedirect;
    }

    /**
     * Fix a rate with specific ID
     *
     * @param string $code
     * @param string $country
     * @param float $percentage
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function add(string $code, string $country, float $percentage)
    {
        $rate = new Rate(
            0,
            $code,
            $country,
            '*',
            $percentage
        );

        $this->storedRatesProvider->saveRate($rate);
    }

    /**
     * Locate the code by country ID and percentage
     *
     * @param string $country
     * @param float $percentage
     * @return string
     * @throws NotFoundException
     */
    private function getCodeByCountryAndPercentage(string $country, float $percentage): string
    {
        $onlineRates = $this->onlineRatesProvider->getRates();
        foreach ($onlineRates as $onlineRate) {
            if ($country !== $onlineRate->getCountryId()) {
                continue;
            }

            if ($percentage !== $onlineRate->getPercentage()) {
                continue;
            }

            return $onlineRate->getCode();
        }

        throw new NotFoundException(__('Unable to find rate'));
    }
}
