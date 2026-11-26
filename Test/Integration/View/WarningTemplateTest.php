<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration\View;

use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Yireo\TaxRatesManager2\Logger\Messages;
use Yireo\TaxRatesManager2\Test\Integration\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class WarningTemplateTest extends TestCase
{
    public function testWarningIsEscapedAndActionIsPostForm()
    {
        $messageManager = $this->getObjectManager()->get(ManagerInterface::class);
        $messageManager->getMessages(true);

        $logger = $this->getObjectManager()->get(Messages::class);
        $logger->warningWithAction('Existing rate "<img src=x onerror=alert(1)>" seems incorrect.', [
            'url' => 'https://example.com/admin/taxratesmanager/index/add/',
            'label' => 'Add',
            'params' => ['country' => 'x"><script>alert(1)</script>', 'percentage' => 21.0],
        ]);

        $messages = $messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_WARNING);
        $this->assertCount(1, $messages);

        $html = $this->getObjectManager()->get(InterpretationStrategyInterface::class)->interpret(reset($messages));

        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringContainsString('&lt;img src=x onerror=alert(1)&gt;', $html);
        $this->assertStringContainsString('method="post"', $html);
        $this->assertStringContainsString('name="form_key"', $html);
        $this->assertStringContainsString('name="percentage"', $html);
    }

    public function testPlainWarningIsEscaped()
    {
        $messageManager = $this->getObjectManager()->get(ManagerInterface::class);
        $messageManager->getMessages(true);

        $this->getObjectManager()->get(Messages::class)->warning('<a href="javascript:alert(1)">Fix</a>');

        $messages = $messageManager->getMessages()->getItemsByType(MessageInterface::TYPE_WARNING);
        $html = $this->getObjectManager()->get(InterpretationStrategyInterface::class)->interpret(reset($messages));

        $this->assertStringNotContainsString('<a href', $html);
        $this->assertStringNotContainsString('<form', $html);
    }
}
