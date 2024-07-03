<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel;

interface ProcessingModelSerializerInterface
{
    /**
     * Convert ProcessingModelInterface instance into array.
     *
     * @param ProcessingModelInterface $processingModel
     *
     * @return array{code: string, type: string}
     */
    public function serializeProcessingModel(ProcessingModelInterface $processingModel): array;
}
