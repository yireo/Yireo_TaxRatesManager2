<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Controller\Adminhtml\Index;

use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
abstract class AbstractControllerTestCase extends AbstractBackendController
{
    protected $httpMethod = HttpRequest::METHOD_POST;

    public function testGetRequestIsRejected()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $this->dispatch($this->uri);

        $this->assertSame('noroute', $this->getRequest()->getControllerName());
    }

    public function testAccessIsDeniedWithoutTaxManagementPermission()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $acl = $this->_objectManager->get(AclBuilder::class)->getAcl();
        $acl->deny($this->_auth->getUser()->getRoles(), 'Magento_Tax::manage_tax');

        $this->dispatch($this->uri);

        $this->assertSame(403, $this->getResponse()->getHttpResponseCode());
    }
}
