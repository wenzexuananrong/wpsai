<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Dictionary;

use Psr\Container\ContainerInterface;

class DictionaryFactory extends \Dhii\Container\DictionaryFactory
{
    /**
     * @inheritDoc
     */
    public function createContainerFromArray(array $data): ContainerInterface
    {

        return new Dictionary($data);
    }
}
