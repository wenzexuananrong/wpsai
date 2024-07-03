<?php

namespace Inpsyde\PayoneerSdk\Api;

interface OperationExceptionInterface
{
    public function getRawResponse(): array;
    public function getRawRequest(): array;
}
