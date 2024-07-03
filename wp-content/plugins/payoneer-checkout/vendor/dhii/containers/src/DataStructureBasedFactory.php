<?php

declare (strict_types=1);
namespace Syde\Vendor\Dhii\Container;

use Syde\Vendor\Dhii\Collection\WritableMapFactoryInterface;
use Syde\Vendor\Psr\Container\ContainerInterface;
/**
 * @inheritDoc
 */
class DataStructureBasedFactory implements DataStructureBasedFactoryInterface
{
    /**
     * @var WritableMapFactoryInterface
     */
    protected $containerFactory;
    public function __construct(WritableMapFactoryInterface $containerFactory)
    {
        $this->containerFactory = $containerFactory;
    }
    /**
     * @inheritDoc
     */
    public function createContainerFromArray(array $structure) : ContainerInterface
    {
        $map = [];
        foreach ($structure as $key => $value) {
            if (is_object($value)) {
                $value = get_object_vars($value);
            }
            if (is_array($value)) {
                $value = $this->createContainerFromArray($value);
            }
            $map[$key] = $value;
        }
        $container = $this->containerFactory->createContainerFromArray($map);
        return $container;
    }
}
