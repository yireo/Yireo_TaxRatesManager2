<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Yireo\TaxRatesManager2\Provider\OnlineRates;
use Yireo\TaxRatesManager2\Provider\StoredRates;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class FixTest extends AbstractControllerTestCase
{
    protected $resource = 'Yireo_TaxRatesManager2::index';
    protected $uri = 'backend/taxratesmanager/index/fix';

    protected function setUp(): void
    {
        parent::setUp();

        $onlineRates = $this->createMock(OnlineRates::class);
        $onlineRates->method('getRates')->willReturn([]);
        $this->_objectManager->addSharedInstance($onlineRates, OnlineRates::class);

        $rates = $this->_objectManager->get(StoredRates::class)->getRates();
        $this->getRequest()->setPostValue('id', (string)reset($rates)->getId());
    }

    protected function tearDown(): void
    {
        $this->_objectManager->removeSharedInstance(OnlineRates::class);
        parent::tearDown();
    }

    public function testAclHasAccess()
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch($this->uri);

        $this->assertRedirect($this->stringContains('tax/rate'));
    }
}
