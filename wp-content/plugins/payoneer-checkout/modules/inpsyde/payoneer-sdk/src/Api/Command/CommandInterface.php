<?php

declare (strict_types=1);
namespace Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command;

use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Command\Exception\CommandExceptionInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Syde\Vendor\Inpsyde\PayoneerSdk\Client\ApiClientInterface;
/**
 * A command sending API requests
 */
interface CommandInterface
{
    /**
     * Update an existing session
     *
     * @throws CommandExceptionInterface
     */
    public function execute() : ListInterface;
    /**
     * @param string $transactionId
     *
     * @return static
     */
    public function withTransactionId(string $transactionId) : self;
    /**
     * Set API client to be used for requests.
     *
     * @param ApiClientInterface $apiClient
     *
     * @return static
     */
    public function withApiClient(ApiClientInterface $apiClient) : self;
}
