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

use Magento\Framework\Data\Form\FormKey;
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
     * @param FormKey $formKey
     */
    public function __construct(
        ManagerInterface $messageManager,
        private readonly FormKey $formKey
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
     * @param array $action
     */
    public function warning(string $msg, array $action = [])
    {
        $data = ['msg' => $msg];
        if (!empty($action['url'])) {
            $data['action_url'] = (string)$action['url'];
            $data['action_label'] = (string)($action['label'] ?? $action['url']);
            $data['action_params'] = (array)($action['params'] ?? []);
            $data['form_key'] = $this->formKey->getFormKey();
        }

        $this->messageManager->addComplexWarningMessage('htmlWarning', $data);
    }

    /**
     * @param string $msg
     */
    public function error(string $msg)
    {
        $this->messageManager->addErrorMessage($msg);
    }
}
