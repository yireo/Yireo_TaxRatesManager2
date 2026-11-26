<?php
declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Plugin\Rate\Toolbar;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Tax\Block\Adminhtml\Rate\Toolbar\Add as Subject;

class AddPlugin
{
    /**
     * @var UrlInterface
     */
    private $url;
    /**
     * @var ButtonList
     */
    private $buttonList;
    /**
     * @var ToolbarInterface
     */
    private $toolbar;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * AddPlugin constructor.
     * @param UrlInterface $url
     * @param ButtonList $buttonList
     * @param ToolbarInterface $toolbar
     * @param Escaper $escaper
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        UrlInterface $url,
        ButtonList $buttonList,
        ToolbarInterface $toolbar,
        Escaper $escaper,
        AuthorizationInterface $authorization
    ) {
        $this->url = $url;
        $this->buttonList = $buttonList;
        $this->toolbar = $toolbar;
        $this->escaper = $escaper;
        $this->authorization = $authorization;
    }

    public function beforeSetLayout(Subject $subject)
    {
        if (!$this->authorization->isAllowed('Magento_Tax::manage_tax')
            || !$this->authorization->isAllowed('Yireo_TaxRatesManager2::index')) {
            return;
        }

        $url = $this->url->getUrl('taxratesmanager/index/clean');
        $this->buttonList->add(
            'clean',
            [
                'label' => __('Clean Existing Rates'),
                'onclick' => sprintf(
                    "deleteConfirm('%s', '%s', {data: {}})",
                    $this->escaper->escapeJs((string)__('Are you sure you want to delete all existing tax rates?')),
                    $this->escaper->escapeJs($url)
                ),
                'class' => 'close'
            ],
            0,
            0,
            'toolbar'
        );

        $this->toolbar->pushButtons($subject, $this->buttonList);
    }
}
