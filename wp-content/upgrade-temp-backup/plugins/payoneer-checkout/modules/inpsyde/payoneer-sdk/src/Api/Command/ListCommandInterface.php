<?php

namespace Inpsyde\PayoneerSdk\Api\Command;

use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemInterface;

interface ListCommandInterface extends PaymentCommandInterface
{
    /**
     * @param CallbackInterface $callback
     *
     * @return static
     */
    public function withCallback(CallbackInterface $callback): self;

    /**
     * @param CustomerInterface $customer
     *
     * @return static
     */
    public function withCustomer(CustomerInterface $customer): self;

    /**
     * @param string $country Country code.
     *
     * @return static
     */
    public function withCountry(string $country): self;

    /**
     * Return a new instance with provided views only.
     *
     * @param string[] $views
     *
     * @return static
     */
    public function withViews(array $views): self;

    /**
     * Return a new instance with provided views merged with those already added.
     *
     * @param array $views
     *
     * @return static
     */
    public function withAddedViews(array $views): self;

    /**
     * Return a new instance without provided views.
     *
     * @param string[] $views Views to exclude.
     *
     * @return static
     */
    public function withoutViews(array $views): self;

    /**
     * Return a new instance with provided division.
     *
     * @param string $division Division to set
     *
     * @return static
     */
    public function withDivision(string $division): self;

    /**
     * Return a new instance without division.
     *
     * @return static
     */
    public function withoutDivision(): self;

    /**
     * Return a new instance with provided system.
     *
     * @param SystemInterface $system System to set
     *
     * @return static
     */
    public function withSystem(SystemInterface $system): self;
}
