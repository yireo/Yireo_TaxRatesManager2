<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Controller\Adminhtml\Index;

use Magento\Framework\Acl\Builder as AclBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Tax\Api\TaxRateRepositoryInterface;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class CleanTest extends AbstractControllerTestCase
{
    protected $resource = 'Yireo_TaxRatesManager2::index';
    protected $uri = 'backend/taxratesmanager/index/clean';

    public function testPostDeletesAllRates()
    {
        $this->assertGreaterThan(0, $this->countRates());

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch($this->uri);

        $this->assertSame(0, $this->countRates());
        $this->assertRedirect($this->stringContains('tax/rate'));
    }

    public function testGetDoesNotDeleteRates()
    {
        $count = $this->countRates();
        $this->assertGreaterThan(0, $count);

        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $this->dispatch($this->uri);

        $this->assertSame($count, $this->countRates());
    }

    public function testPostWithoutModulePermissionDoesNotDeleteRates()
    {
        $count = $this->countRates();
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $acl = $this->_objectManager->get(AclBuilder::class)->getAcl();
        $acl->deny($this->_auth->getUser()->getRoles(), $this->resource);

        $this->dispatch($this->uri);

        $this->assertSame(403, $this->getResponse()->getHttpResponseCode());
        $this->assertSame($count, $this->countRates());
    }

    private function countRates(): int
    {
        $searchCriteria = $this->_objectManager->create(SearchCriteriaBuilder::class)->create();
        $repository = $this->_objectManager->get(TaxRateRepositoryInterface::class);

        return count($repository->getList($searchCriteria)->getItems());
    }
}
