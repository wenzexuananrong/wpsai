<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Product;

/**
 * Contains possible product types.
 */
class ProductType
{
    const PHYSICAL = 'PHYSICAL';
    const DIGITAL = 'DIGITAL';
    const SERVICE = 'SERVICE';
    const TAX = 'TAX';
    const OTHER = 'OTHER';
}
