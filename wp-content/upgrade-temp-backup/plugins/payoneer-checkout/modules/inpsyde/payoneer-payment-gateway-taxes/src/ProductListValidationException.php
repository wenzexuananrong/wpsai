<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\Taxes;

use Inpsyde\PayoneerForWoocommerce\Core\Exception\PayoneerExceptionInterface;
use RangeException;

class ProductListValidationException extends RangeException implements PayoneerExceptionInterface
{
}
