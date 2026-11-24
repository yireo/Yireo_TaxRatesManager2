<?php declare(strict_types=1);

namespace Yireo\TaxRatesManager2\Test\Integration;

use Magento\Framework\Acl\AclResource\ProviderInterface;

class AclTest extends TestCase
{
    public function testModuleResourcesAreNestedUnderTaxManagement()
    {
        $resources = $this->getObjectManager()->get(ProviderInterface::class)->getAclResources();

        $taxResource = $this->findResource($resources, 'Magento_Tax::manage_tax');
        $this->assertNotNull($taxResource);

        $moduleResource = $this->findResource($taxResource['children'], 'Yireo_TaxRatesManager2::index');
        $this->assertNotNull($moduleResource, 'Module ACL resource should be a child of Magento_Tax::manage_tax');

        $cleanResource = $this->findResource($moduleResource['children'], 'Yireo_TaxRatesManager2::clean');
        $this->assertNotNull($cleanResource, 'Clean ACL resource should be a child of the module resource');
    }

    private function findResource(array $resources, string $id): ?array
    {
        foreach ($resources as $resource) {
            if ($resource['id'] === $id) {
                return $resource;
            }

            $child = $this->findResource($resource['children'] ?? [], $id);
            if ($child !== null) {
                return $child;
            }
        }

        return null;
    }
}
