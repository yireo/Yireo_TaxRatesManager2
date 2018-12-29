<?php
declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration;

use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

/**
 * Class TestCase
 * @package Yireo\TaxRatesManager2\Test\Integration
 */
class TestCase extends PhpUnitTestCase
{
    /**
     * @return ObjectManager
     */
    protected function getObjectManager(): ObjectManager
    {
        return ObjectManager::getInstance();
    }
}
