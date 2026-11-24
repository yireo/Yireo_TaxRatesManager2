<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class AddTest extends AbstractControllerTestCase
{
    protected $resource = 'Yireo_TaxRatesManager2::index';
    protected $uri = 'backend/taxratesmanager/index/add';

    public function testPostWithInvalidCountryRedirectsWithError()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(['country' => 'x"><img src=x>', 'percentage' => '21']);
        $this->dispatch($this->uri);

        $this->assertRedirect($this->stringContains('tax/rate'));
        $this->assertSessionMessages($this->logicalNot($this->isEmpty()), MessageInterface::TYPE_ERROR);
    }
}
