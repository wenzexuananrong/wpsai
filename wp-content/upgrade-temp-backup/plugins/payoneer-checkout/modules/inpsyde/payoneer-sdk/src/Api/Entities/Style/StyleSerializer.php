<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Style;

use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;

class StyleSerializer implements StyleSerializerInterface
{
    /**
     * @inheritDoc
     */
    public function serializeStyle(StyleInterface $style): array
    {
        $serializedStyle = [];

        try {
            $serializedStyle['language'] = $style->getLanguage();
        } catch (ApiExceptionInterface $exception) {
            //Silence
        }

        try {
            $serializedStyle['hostedVersion'] = $style->getHostedVersion();
        } catch (ApiExceptionInterface $exception) {
            //Silence
        }

        return $serializedStyle;
    }
}
