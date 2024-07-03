<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Taxes;

use Inpsyde\PayoneerForWoocommerce\Core\Exception\PayoneerExceptionInterface;
use RangeException;

class MissingListDataException extends RangeException implements PayoneerExceptionInterface
{
}
