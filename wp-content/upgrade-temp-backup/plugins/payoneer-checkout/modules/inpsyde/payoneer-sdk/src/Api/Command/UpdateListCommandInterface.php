<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Command;

use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerInterface;

interface UpdateListCommandInterface extends ListCommandInterface, ListAwareCommandInterface
{
}
