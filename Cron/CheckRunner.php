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

namespace Yireo\TaxRatesManager2\Cron;

use Exception;
use Magento\Store\Model\Store;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Psr\Log\LoggerInterface as GenericLoggerInterface;
use Yireo\TaxRatesManager2\Api\LoggerInterface;
use Yireo\TaxRatesManager2\Check\Check;
use Yireo\TaxRatesManager2\Config\Config;

/**
 * Class CheckRunner
 * @package Yireo\TaxRatesManager2\Cron
 */
class CheckRunner
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Check
     */
    private $check;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var GenericLoggerInterface
     */
    private $genericLogger;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * CheckRunner constructor.
     * @param Config $config
     * @param LoggerInterface $logger
     * @param Check $check
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param GenericLoggerInterface $genericLogger
     * @param StateInterface $inlineTranslation
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger,
        Check $check,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        GenericLoggerInterface $genericLogger,
        StateInterface $inlineTranslation
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->check = $check;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->genericLogger = $genericLogger;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * @return bool
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute(): bool
    {
        ob_start();

        $this->check->execute();
        $contents = ob_get_clean();

        if (!$contents) {
            return false;
        }

        if ($this->config->sendEmail() === false) {
            return false;
        }

        $this->sendMail($contents);

        return true;
    }

    /**
     * Send a mail
     *
     * @param string $contents
     */
    private function sendMail(string $contents)
    {
        $name = $this->scopeConfig->getValue('trans_email/ident_support/name');
        $email = $this->scopeConfig->getValue('trans_email/ident_support/email');

        $templateParams = [];
        $templateParams['output'] = $contents;
        $templateParams['store_name'] = $this->scopeConfig->getValue('general/store_information/name');

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('yireo_taxratesmanager_check')
            ->setTemplateOptions([
                'area' => 'adminhtml',
                'store' => Store::DEFAULT_STORE_ID,
                ])
            ->addTo($email, $name)
            ->setTemplateVars($templateParams)
            ->setFrom('general')
            ->getTransport();

        try {
            $this->inlineTranslation->suspend();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (Exception $e) {
            $this->genericLogger->critical($e->getMessage());
        }
    }
}
