<?php

declare(strict_types=1);

use Inpsyde\PayoneerForWoocommerce\ListSession\Factory\RedirectInjectingListFactory;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListFactoryInterface;
use Psr\Container\ContainerInterface;

return static function (): array {
    return [
        'payoneer_sdk.list_factory' => static function (
            ListFactoryInterface $previous
        ): ListFactoryInterface {
            return new RedirectInjectingListFactory($previous);
        },
        'list_session.middlewares' => static function (
            array $middlewares,
            ContainerInterface $container
        ) {
            $isFrontend = $container->get('wp.is_frontend_request');
            if (!$isFrontend) {
                return $middlewares;
            }
            /**
             * The order is important here!
             */
            array_unshift($middlewares, $container->get('list_session.middlewares.wc-session'));
            array_unshift($middlewares, $container->get('list_session.middlewares.wc-session-update'));

            return $middlewares;
        },
    ];
};
