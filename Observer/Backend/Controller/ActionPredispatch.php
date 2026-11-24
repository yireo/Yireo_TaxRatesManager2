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

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Throwable;
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
     * @var HttpRequest
     */
    private $request;

    /**
     * ActionPredispatch constructor.
     * @param Check $check
     * @param HttpRequest $request
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Check $check,
        HttpRequest $request,
        private readonly ManagerInterface $messageManager,
        private readonly LoggerInterface $logger
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
        if (!$this->request->isGet() || $this->request->isAjax()) {
            return $this;
        }

        try {
            $this->check->execute();
        } catch (Throwable $exception) {
            $this->logger->error('Yireo_TaxRatesManager2: ' . $exception->getMessage(), ['exception' => $exception]);
            $this->messageManager->addErrorMessage(__('Unable to check the tax rates. See the logs for details.'));
        }

        return $this;
    }
}
