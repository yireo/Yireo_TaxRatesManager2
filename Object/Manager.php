<?php
/**
 * Yireo TaxRatesManager extension for Magento 1
 *
 * @package     Yireo_TaxRatesManager
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     Open Source License (OSL v3)
 */

declare(strict_types=1);

/**
 * Class Yireo_TaxRatesManager_Object_Manager
 */
class Yireo_TaxRatesManager_Object_Manager
{
    /**
     * @param string $className
     * @return object
     * @throws ReflectionException
     */
    public function get(string $className)
    {
        if ($object = $this->getMagentoObject($className)) {
            return $object;
        }

        if ($object = $this->getPreferenceObject($className)) {
            return $object;
        }

        $reflectionClass = new ReflectionClass($className);
        $constructorParameters = $this->getConstructorParameters($reflectionClass);
        return $reflectionClass->newInstanceArgs($constructorParameters);
    }

    /**
     * @param string $className
     * @return object|null
     * @throws ReflectionException
     */
    private function getMagentoObject(string $className)
    {
        if ($className === Mage_Core_Model_App::class) {
            return Mage::app();
        }

        if ($className === Yireo_TaxRatesManager_Api_LoggerInterface::class) {
            if (!$this->isCli()) {
                return $this->get(Yireo_TaxRatesManager_Logger_Messages::class);
            }

            return $this->get(Yireo_TaxRatesManager_Logger_Console::class);
        }
    }

    /**
     * @param string $className
     * @return object|null
     * @throws ReflectionException
     */
    private function getPreferenceObject(string $className)
    {
        $preferences = $this->getPreferences();
        if (isset($preferences[$className])) {
            return $this->get($preferences[$className]);
        }
    }

    /**
     * @return string[]
     */
    private function getPreferences() : array
    {
        return [];
    }

    /**
     * @param ReflectionClass $reflectionClass
     * @return array
     * @throws ReflectionException
     */
    private function getConstructorParameters(ReflectionClass $reflectionClass): array
    {
        $constructorParameters = [];
        $constructor = $reflectionClass->getConstructor();
        if (!$constructor) {
            return $constructorParameters;
        }

        $parameters = $constructor->getParameters();

        foreach ($parameters as $parameter) {
            if ($parameterClass = $parameter->getClass()) {
                $parameter = $this->get((string)$parameterClass->getName());
                $constructorParameters[] = $parameter;
                continue;
            }

            $constructorParameters[] = $parameter->getDefaultValue();
        }

        return $constructorParameters;
    }

    /**
     * @return bool
     * @throws ReflectionException
     */
    private function isCli(): bool
    {
        /** @var Yireo_TaxRatesManager_Util_CommandLine $cli */
        $cli = $this->get(Yireo_TaxRatesManager_Util_CommandLine::class);
        return $cli->isCli();
    }
}
