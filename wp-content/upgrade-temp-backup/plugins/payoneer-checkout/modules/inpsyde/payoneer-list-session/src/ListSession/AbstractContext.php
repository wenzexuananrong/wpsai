<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\ListSession\ListSession;

use ReturnTypeWillChange;

abstract class AbstractContext implements ContextInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    protected $listWasCreated = false;

    /**
     * @param string|int $offset
     *
     * @return bool
     */
    public function offsetExists($offset = ''): bool
    {
        return isset($this->data[$offset]);
    }

    #[ReturnTypeWillChange]
    /**
     * @param string|int $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset = '')
    {
        /**
         * @psalm-suppress MixedArrayOffset
         * @psalm-suppress MixedReturnStatement
         */
        return $this->data[$offset];
    }

    /**
     * @param string|int $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset = '', $value = ''): void
    {
        $this->data[$offset] = $value;
    }

    /**
     * @param string|int $offset
     *
     * @return void
     */
    public function offsetUnset($offset = ''): void
    {
        unset($this->data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function listWasCreated(): bool
    {
        return $this->listWasCreated;
    }

    public function setListWasCreated(bool $listWasCreated): void
    {
        $this->listWasCreated = $listWasCreated;
    }
}
