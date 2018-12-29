<?php
declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Plugin\Rate\Toolbar;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ToolbarInterface;
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
     * AddPlugin constructor.
     * @param UrlInterface $url
     * @param ButtonList $buttonList
     * @param ToolbarInterface $toolbar
     */
    public function __construct(
        UrlInterface $url,
        ButtonList $buttonList,
        ToolbarInterface $toolbar
    ) {
        $this->url = $url;
        $this->buttonList = $buttonList;
        $this->toolbar = $toolbar;
    }

    public function beforeSetLayout(Subject $subject)
    {
        $url = $this->url->getUrl('taxratesmanager/index/clean');
        $this->buttonList->add(
            'clean',
            [
                'label' => __('Clean Existing Rates'),
                'onclick' => 'window.location.href=\'' . $url . '\'',
                'class' => 'close'
            ],
            0,
            0,
            'toolbar'
        );

        $this->toolbar->pushButtons($subject, $this->buttonList);
    }
}
