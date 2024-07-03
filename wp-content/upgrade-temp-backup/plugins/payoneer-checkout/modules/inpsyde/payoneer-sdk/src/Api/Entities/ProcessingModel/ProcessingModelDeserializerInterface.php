<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel;

interface ProcessingModelDeserializerInterface
{
    /**
     * Convert array into ProcessingModelInterface instance.
     *
     * @param array $processingModelData
     *
     * @return ProcessingModelInterface
     */
    public function deserializeProcessingModel(array $processingModelData): ProcessingModelInterface;
}
