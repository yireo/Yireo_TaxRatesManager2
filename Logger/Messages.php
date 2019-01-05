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

namespace Yireo\TaxRatesManager2\Logger;

use Magento\Framework\Message\ManagerInterface;
use Yireo\TaxRatesManager2\Api\LoggerInterface;

/**
 * Class Messages
 */
class Messages implements LoggerInterface
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Messages constructor.
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
    }

    /**
     * @param string $msg
     */
    public function info(string $msg)
    {
        $this->messageManager->addNoticeMessage($msg);
    }

    /**
     * @param string $msg
     */
    public function success(string $msg)
    {
        $this->messageManager->addSuccessMessage($msg);
    }

    /**
     * @param string $msg
     */
    public function warning(string $msg)
    {
        $this->messageManager->addComplexWarningMessage('htmlWarning', ['msg' => $msg]);
    }

    /**
     * @param string $msg
     */
    public function error(string $msg)
    {
        $this->messageManager->addErrorMessage($msg);
    }
}

