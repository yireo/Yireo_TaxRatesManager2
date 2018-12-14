<?php
/**
 * TaxRatesManager2 module for Magento
 *
 * @package     Yireo_TaxRatesManager2
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License 3
 */

declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Observer\Backend\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Yireo\TaxRatesManager2\Check\Check;

/**
 * Class ActionPredispatch
 * @package Yireo\TaxRatesManager2\Observer\Backend\Controller
 */
class ActionPredispatch implements ObserverInterface
{
    /**
     * @var Check
     */
    private $check;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * ActionPredispatch constructor.
     * @param Check $check
     * @param RequestInterface $request
     */
    public function __construct(
        Check $check,
        RequestInterface $request
    ) {
        $this->check = $check;
        $this->request = $request;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return ActionPredispatch
     */
    public function execute(
        Observer $observer
    ) {
        $controllerName = $this->request->getControllerName();
        if ($controllerName !== 'tax_rate') {
            return $this;
        }

        $this->check->execute();

        return $this;
    }
}
