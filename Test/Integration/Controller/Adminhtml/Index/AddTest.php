<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Controller\Adminhtml\Index;

use Magento\Framework\App\Request\Http as HttpRequest;
use Yireo\TaxRatesManager2\Provider\OnlineRates;
use Yireo\TaxRatesManager2\Provider\StoredRates;
use Yireo\TaxRatesManager2\Rate\Rate;

/**
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class AddTest extends AbstractControllerTestCase
{
    protected $resource = 'Yireo_TaxRatesManager2::index';
    protected $uri = 'backend/taxratesmanager/index/add';

    protected function setUp(): void
    {
        parent::setUp();

        $onlineRates = $this->createMock(OnlineRates::class);
        $onlineRates->method('getRates')->willReturn([new Rate(0, 'Yireo Test (standard)', 'NL', '*', 21.0)]);
        $this->_objectManager->addSharedInstance($onlineRates, OnlineRates::class);
        $this->getRequest()->setPostValue(['country' => 'NL', 'percentage' => '21']);
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
        $codes = array_map(function (Rate $rate) {
            return $rate->getCode();
        }, $this->_objectManager->get(StoredRates::class)->getRates());
        $this->assertContains('Yireo Test (standard)', $codes);
    }
}
