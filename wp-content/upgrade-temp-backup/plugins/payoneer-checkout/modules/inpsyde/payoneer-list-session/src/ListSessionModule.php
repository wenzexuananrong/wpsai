<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerForWoocommerce\ListSession;

use Inpsyde\Modularity\Module\ExecutableModule;
use Inpsyde\Modularity\Module\ExtendingModule;
use Inpsyde\Modularity\Module\ModuleClassNameIdTrait;
use Inpsyde\Modularity\Module\ServiceModule;
use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectInterface;
use Psr\Container\ContainerInterface;

class ListSessionModule implements ExecutableModule, ServiceModule, ExtendingModule
{
    use ModuleClassNameIdTrait;

    public function services(): array
    {
        static $services;

        if ($services === null) {
            $services = require_once dirname(__DIR__) . '/inc/services.php';
        }

        /** @var callable(): array<string, callable(\Psr\Container\ContainerInterface $container):mixed> $services */
        return $services();
    }

    /**
     * @inheritDoc
     */
    public function extensions(): array
    {
        static $extensions;

        if ($extensions === null) {
            $extensions = require_once dirname(__DIR__) . '/inc/extensions.php';
        }

        /** @var callable(): array<string, callable(mixed $service, \Psr\Container\ContainerInterface $container):mixed> $extensions */
        return $extensions();
    }

    public function run(ContainerInterface $container): bool
    {
        (new ListSessionInitializer())($container);
        $this->registerKeepingRedirectAfterUpdate();

        return true;
    }

     /**
     * Payoneer API doesn't return redirect object for UPDATE requests, but we need it in hosted
     * mode to redirect customer to the payment page. This is a workaround. We are keeping redirect
     * from existing List which we are going to update and inject it via filter into the List
     * factory when it creates a new List from the UPDATE response. This way, the initial redirect
     * we had from CREATE should be never lost.
     */
    public function registerKeepingRedirectAfterUpdate(): void
    {
        $injectRedirect = static function (
            array $args
        ): void {
            $list = $args['list'];
            assert($list instanceof ListInterface);

            add_filter(
                'payoneer-checkout.fallback_redirect',
                /**
                 * @param RedirectInterface|null $originalRedirect
                 * @param IdentificationInterface $identification
                 *
                 * @return RedirectInterface|null
                 */
                static function (
                    $originalRedirect,
                    IdentificationInterface $identification
                ) use (
                    $list
                ): ?RedirectInterface {
                //We have to be sure this is the same session before injecting redirect.
                    if ($list->getIdentification()->getLongId() !== $identification->getLongId()) {
                        return $originalRedirect;
                    }

                    try {
                        return $list->getRedirect();
                    } catch (ApiExceptionInterface $exception) {
                        return $originalRedirect;
                    }
                },
                10,
                2
            );
        };

        add_action('payoneer-checkout.before_update_list', $injectRedirect);
    }

    /**
     * Register a WordPress hook in a way that callback is executed only once.
     *
     * @param string $hookName
     * @param callable $callable
     * @param int $priority
     * @param int $acceptedArgs
     *
     * @return void
     */
    protected function hookOnce(
        string $hookName,
        callable $callable,
        int $priority = 10,
        int $acceptedArgs = 1
    ): void {
        /**
         * @psalm-suppress UnusedVariable
         */
        $once = static function () use (&$once, $hookName, $callable, $priority) {
            static $called = false;
            /** @var callable $once */
            ! $called and $callable(...func_get_args());
            $called = true;
        };
        add_action($hookName, $once, $priority, $acceptedArgs);
    }
}
