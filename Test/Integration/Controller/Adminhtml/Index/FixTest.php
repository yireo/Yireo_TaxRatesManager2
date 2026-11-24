<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\MessageInterface;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class FixTest extends AbstractControllerTestCase
{
    protected $resource = 'Yireo_TaxRatesManager2::index';
    protected $uri = 'backend/taxratesmanager/index/fix';

    public function testPostWithInvalidIdRedirectsWithError()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue('id', '0');
        $this->dispatch($this->uri);

        $this->assertRedirect($this->stringContains('tax/rate'));
        $this->assertSessionMessages($this->logicalNot($this->isEmpty()), MessageInterface::TYPE_ERROR);
    }

    public function testPostWithUnknownIdRedirectsWithError()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue('id', '999999');
        $this->dispatch($this->uri);

        $this->assertRedirect($this->stringContains('tax/rate'));
        $this->assertSessionMessages($this->logicalNot($this->isEmpty()), MessageInterface::TYPE_ERROR);
    }
}
