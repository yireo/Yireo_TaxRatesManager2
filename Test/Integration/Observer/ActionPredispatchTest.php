<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Observer;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Event\ConfigInterface as EventConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use RuntimeException;
use Yireo\TaxRatesManager2\Check\Check;
use Yireo\TaxRatesManager2\Observer\Backend\Controller\ActionPredispatch;
use Yireo\TaxRatesManager2\Test\Integration\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class ActionPredispatchTest extends TestCase
{
    private const OBSERVER_NAME = 'yireo_taxratesmanager2_observer_backend_controller_actionpredispatch_controller_action_predispatch';

    public function testObserverIsOnlyRegisteredForTaxRateGrid()
    {
        $eventConfig = $this->getObjectManager()->get(EventConfig::class);

        $this->assertArrayHasKey(
            self::OBSERVER_NAME,
            $eventConfig->getObservers('controller_action_predispatch_tax_rate_index')
        );
        $this->assertArrayNotHasKey(self::OBSERVER_NAME, $eventConfig->getObservers('controller_action_predispatch'));
    }

    public function testCheckIsSkippedForPostRequests()
    {
        $check = $this->createMock(Check::class);
        $check->expects($this->never())->method('execute');

        $this->createObserver($check, HttpRequest::METHOD_POST)->execute(new Observer());
    }

    public function testFailingCheckAddsErrorMessageInsteadOfThrowing()
    {
        $check = $this->createMock(Check::class);
        $check->expects($this->once())->method('execute')->willThrowException(new RuntimeException('Feed down'));

        $messageManager = $this->getObjectManager()->get(ManagerInterface::class);
        $messageManager->getMessages(true);

        $this->createObserver($check, HttpRequest::METHOD_GET)->execute(new Observer());

        $errors = $messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_ERROR);
        $this->assertCount(1, $errors);
    }

    private function createObserver(Check $check, string $method): ActionPredispatch
    {
        $request = $this->getObjectManager()->create(HttpRequest::class);
        $request->setMethod($method);

        return $this->getObjectManager()->create(ActionPredispatch::class, [
            'check' => $check,
            'request' => $request,
        ]);
    }
}
