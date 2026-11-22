<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Check;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Yireo\TaxRatesManager2\Provider\OnlineRates;
use Yireo\TaxRatesManager2\Api\LoggerInterface;
use Yireo\TaxRatesManager2\Check\Check;
use Yireo\TaxRatesManager2\Logger\Messages;
use Yireo\TaxRatesManager2\Test\Integration\TestCase;

class CheckTest extends TestCase
{
    /**
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     * @magentoAppArea adminhtml
     */
    public function testExecute()
    {
        $logger = $this->getObjectManager()->get(LoggerInterface::class);
        $this->assertInstanceOf(Messages::class, $logger);

        $messageManager = $this->getObjectManager()->get(ManagerInterface::class);
        $messages = $messageManager->getMessages();
        $this->assertEquals(0, $messages->getCount());
        $check = $this->getObjectManager()->get(Check::class);

        $check->setFixAutomatically(false);
        $check->setVerbosity(1);
        $check->execute();

        $messages = (array)$messageManager->getMessages();
        $this->assertNotEquals(0, count($messages));
    }

    /**
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     * @magentoAppArea adminhtml
     */
    public function testExecuteWarnsAboutMissingOnlineRates()
    {
        $onlineRatesProvider = $this->createStub(OnlineRates::class);
        $onlineRatesProvider->method('getRates')->willReturn([]);

        $messageManager = $this->getObjectManager()->get(ManagerInterface::class);
        $messageManager->getMessages(true);

        $check = $this->getObjectManager()->create(Check::class, ['onlineRatesProvider' => $onlineRatesProvider]);
        $check->setFixAutomatically(false);
        $check->execute();

        $warnings = [];
        foreach ($messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_WARNING) as $message) {
            $warnings[] = $message->getData()['msg'] ?? $message->getText();
        }

        $this->assertContains('No online rates found', $warnings);
        $this->assertNotContains('No stored rates found', $warnings);
    }
}