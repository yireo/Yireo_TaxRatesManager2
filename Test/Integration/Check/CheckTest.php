<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\Check;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
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
}