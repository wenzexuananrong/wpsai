<?php

declare(strict_types=1);

use Inpsyde\PayoneerSdk\Api\Command\ChargeCommand;
use Inpsyde\PayoneerSdk\Api\Command\ChargeCommandInterface;
use Inpsyde\PayoneerSdk\Api\Command\CommandInterface;
use Inpsyde\PayoneerSdk\Api\Command\CreateListCommand;
use Inpsyde\PayoneerSdk\Api\Command\CreateListCommandInterface;
use Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorFactory;
use Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Command\Error\InteractionErrorInterface;
use Inpsyde\PayoneerSdk\Api\Command\Exception\InteractionException;
use Inpsyde\PayoneerSdk\Api\Command\PayoutCommand;
use Inpsyde\PayoneerSdk\Api\Command\PayoutCommandInterface;
use Inpsyde\PayoneerSdk\Api\Command\UpdateListCommand;
use Inpsyde\PayoneerSdk\Api\Command\UpdateListCommandInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Callback\CallbackSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Customer\CustomerSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Header\HeaderSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Identification\IdentificationSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListFactory;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\ListSession\ListSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\InvoiceIdProviderInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\PaymentSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Payment\UniqidInvoiceIdProvider;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelFactory;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\ProcessingModel\ProcessingModelSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Product\ProductSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Redirect\RedirectSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Status\StatusSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleFactory;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\Style\StyleSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemDeserializer;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemFactory;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemFactoryInterface;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemSerializer;
use Inpsyde\PayoneerSdk\Api\Entities\System\SystemSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Payoneer;
use Inpsyde\PayoneerSdk\Api\PayoneerIntegrationTypes;
use Inpsyde\PayoneerSdk\Api\PayoneerInterface;
use Inpsyde\PayoneerSdk\Client\ApiClient;
use Inpsyde\PayoneerSdk\Client\ApiClientInterface;
use Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\InteractionCodeValidator;
use Inpsyde\PayoneerSdk\Api\Command\ResponseValidator\ResponseValidatorInterface;
use Inpsyde\PayoneerSdk\Client\TokenAwareInterface;
use Inpsyde\PayoneerSdk\Client\TokenProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

return static function (): array {
    return [
        'payoneer_sdk.remote_api_url.base_string' => static function (): string {
            /**
             * Replace with the real one in consuming code.
             */
            return '';
        },

        'payoneer_sdk.remote_api_url.base' => static function (
            ContainerInterface $container
        ): UriInterface {
            /** @var UriFactoryInterface $uriFactory */
            $uriFactory = $container->get('payoneer_sdk.uri_factory');
            /** @var string $baseUri */
            $baseUri = $container->get('payoneer_sdk.remote_api_url.base_string');

            return $uriFactory->createUri($baseUri);
        },

        'payoneer_sdk.api_client' => static function (
            ContainerInterface $container
        ): ApiClientInterface {
            /** @var ClientInterface $httpClient */
            $httpClient = $container->get('payoneer_sdk.http_client');
            /** @var RequestFactoryInterface $requestFactory */
            $requestFactory = $container->get('payoneer_sdk.request_factory');
            /** @var StreamFactoryInterface $streamFactory */
            $streamFactory = $container->get('payoneer_sdk.stream_factory');
            /** @var UriInterface $baseUrl */
            $baseUrl = $container->get('payoneer_sdk.remote_api_url.base');
            /** @var TokenAwareInterface $tokenProvider */
            $tokenProvider = $container->get('payoneer_sdk.token_provider');

            return new ApiClient($httpClient, $requestFactory, $baseUrl, $streamFactory, $tokenProvider);
        },

        'payoneer_sdk.token_provider' => static function (
            ContainerInterface $container
        ): TokenAwareInterface {
            /** @var callable(): string $tokenProviderCallback */
            $tokenProviderCallback = $container->get('core.token_provider_callback');

            return new TokenProvider($tokenProviderCallback);
        },

        'payoneer_sdk.headers.content_type' => static function (): string {
            return 'application/vnd.optile.payment.enterprise-v1-extensible+json';
        },

        'payoneer_sdk.headers.accept' => static function (
            ContainerInterface $container
        ): string {
            return (string) $container->get('payoneer_sdk.headers.content_type');
        },

        'payoneer_sdk.api_username' => static function (): string {
            //to be set by consuming code
            return '';
        },

        'payoneer_sdk.api_password' => static function (): string {
            //to be set by consuming code
            return '';
        },

        'payoneer_sdk.default_request_headers' => static function (
            ContainerInterface $container
        ): array {
            return [
                'Accept' => $container->get('payoneer_sdk.headers.accept'),
                'Content-Type' => $container->get('payoneer_sdk.headers.content_type'),
            ];
        },

        'payoneer_sdk.list_factory' => static function (): ListFactoryInterface {
            return new ListFactory();
        },

        'payoneer_sdk.callback_factory' => static function (): CallbackFactoryInterface {
            return new CallbackFactory();
        },

        'payoneer_sdk.customer_factory' => static function (): CustomerFactoryInterface {
            return new CustomerFactory();
        },

        'payoneer_sdk.payment_factory' => static function (
            ContainerInterface $container
        ): PaymentFactoryInterface {
            $invoiceIdProvider = $container->get('payoneer.sdk.invoice_id_provider');

            return new PaymentFactory($invoiceIdProvider);
        },

        'payoneer_sdk.product_factory' => static function (): ProductFactoryInterface {
            return new ProductFactory();
        },

        'payoneer_sdk.header_factory' => static function (): HeaderFactoryInterface {
            return new HeaderFactory();
        },

        'payoneer_sdk.identification_factory' =>
            static function (): IdentificationFactoryInterface {
                return new IdentificationFactory();
            },

        'payoneer_sdk.name_factory' => static function (): NameFactoryInterface {
            return new NameFactory();
        },

        'payoneer_sdk.phone_factory' => static function (): PhoneFactoryInterface {
            return new PhoneFactory();
        },

        'payoneer_sdk.address_factory' => static function (): AddressFactoryInterface {
            return new AddressFactory();
        },

        'payoneer_sdk.style_factory' => static function (): StyleFactoryInterface {
            return new StyleFactory();
        },

        'payoneer_sdk.status_factory' => static function (): StatusFactoryInterface {
            return new StatusFactory();
        },

        'payoneer_sdk.system_factory' => static function (): SystemFactoryInterface {
            return new SystemFactory();
        },

        'payoneer_sdk.redirect_factory' => static function (): RedirectFactoryInterface {
            return new RedirectFactory();
        },

        'payoneer_sdk.registration_factory' => static function (): RegistrationFactoryInterface {
            return new RegistrationFactory();
        },

        'payoneer_sdk.processing_model_factory' => static function (): ProcessingModelFactoryInterface {
            return new ProcessingModelFactory();
        },

        'payoneer_sdk.header_deserializer' => static function (
            ContainerInterface $container
        ): HeaderDeserializerInterface {
            /** @var HeaderFactoryInterface $headerFactory */
            $headerFactory = $container->get('payoneer_sdk.header_factory');

            return new HeaderDeserializer($headerFactory);
        },

        'payoneer_sdk.callback_deserializer' => static function (
            ContainerInterface $container
        ): CallbackDeserializerInterface {
            /** @var CallbackFactoryInterface $callbackFactory */
            $callbackFactory = $container->get('payoneer_sdk.callback_factory');
            /** @var HeaderDeserializerInterface $headerDeserializer */
            $headerDeserializer = $container->get('payoneer_sdk.header_deserializer');

            return new CallbackDeserializer($callbackFactory, $headerDeserializer);
        },

        'payoneer_sdk.name_deserializer' => static function (
            ContainerInterface $container
        ): NameDeserializerInterface {
            $nameFactory = $container->get('payoneer_sdk.name_factory');

            return new NameDeserializer($nameFactory);
        },

        'payoneer_sdk.address_deserializer' => static function (
            ContainerInterface $container
        ): AddressDeserializerInterface {
            /** @var NameFactoryInterface $nameFactory */
            $nameDeserializer = $container->get('payoneer_sdk.name_deserializer');

            /** @var AddressFactoryInterface $addressFactory */
            $addressFactory = $container->get('payoneer_sdk.address_factory');

            return new AddressDeserializer($addressFactory, $nameDeserializer);
        },

        'payoneer_sdk.customer_deserializer' => static function (
            ContainerInterface $container
        ): CustomerDeserializerInterface {
            /** @var CustomerFactoryInterface $customerFactory */
            $customerFactory = $container->get('payoneer_sdk.customer_factory');
            /** @var PhoneDeserializerInterface $phoneDeserializer */
            $phoneDeserializer = $container->get('payoneer_sdk.phone_deserializer');
            /** @var AddressDeserializerInterface $addressDeserializer */
            $addressDeserializer = $container->get('payoneer_sdk.address_deserializer');
            /** @var RegistrationDeserializerInterface $registrationDeserializer */
            $registrationDeserializer = $container->get('payoneer_sdk.registration_deserializer');
            /** @var NameDeserializerInterface $nameDeserializer */
            $nameDeserializer = $container->get('payoneer_sdk.name_deserializer');

            return new CustomerDeserializer(
                $customerFactory,
                $phoneDeserializer,
                $addressDeserializer,
                $registrationDeserializer,
                $nameDeserializer
            );
        },

        'payoneer_sdk.payment_deserializer' => static function (
            ContainerInterface $container
        ): PaymentDeserializerInterface {
            /** @var PaymentFactoryInterface $callbackFactory */
            $paymentFactory = $container->get('payoneer_sdk.payment_factory');

            return new PaymentDeserializer($paymentFactory);
        },

        'payoneer_sdk.product_deserializer' => static function (
            ContainerInterface $container
        ): ProductDeserializerInterface {
            /** @var ProductFactoryInterface $productFactory */
            $productFactory = $container->get('payoneer_sdk.product_factory');

            return new ProductDeserializer($productFactory);
        },

        'payoneer_sdk.phone_deserializer' => static function (
            ContainerInterface $container
        ): PhoneDeserializerInterface {
            $phoneFactory = $container->get('payoneer_sdk.phone_factory');

            return new PhoneDeserializer($phoneFactory);
        },

        'payoneer_sdk.style_deserializer' => static function (
            ContainerInterface $container
        ): StyleDeserializerInterface {
            /** @var StyleFactoryInterface $styleFactory */
            $styleFactory = $container->get('payoneer_sdk.style_factory');

            return new StyleDeserializer($styleFactory);
        },

        'payoneer_sdk.status_deserializer' => static function (
            ContainerInterface $container
        ): StatusDeserializerInterface {
            /** @var StatusFactoryInterface $statusFactory */
            $statusFactory = $container->get('payoneer_sdk.status_factory');

            return new StatusDeserializer($statusFactory);
        },

        'payoneer_sdk.identification_deserializer' => static function (
            ContainerInterface $container
        ): IdentificationDeserializerInterface {
            /** @var IdentificationFactoryInterface $identificationFactory */
            $identificationFactory = $container->get('payoneer_sdk.identification_factory');

            return new IdentificationDeserializer($identificationFactory);
        },

        'payoneer_sdk.system_deserializer' => static function (
            ContainerInterface $container
        ): SystemDeserializerInterface {
            /** @var SystemFactoryInterface $systemFactory */
            $systemFactory = $container->get('payoneer_sdk.system_factory');

            return new SystemDeserializer($systemFactory);
        },

        'payoneer_sdk.registration_deserializer' => static function (
            ContainerInterface $container
        ): RegistrationDeserializerInterface {
            $registrationFactory = $container->get('payoneer_sdk.registration_factory');

            return new RegistrationDeserializer($registrationFactory);
        },

        'payoneer_sdk.list_deserializer' => static function (
            ContainerInterface $container
        ): ListDeserializerInterface {
            /** @var ListFactoryInterface $listFactory */
            $listFactory = $container->get('payoneer_sdk.list_factory');

            /** @var CallbackDeserializerInterface $callbackDeserializer */
            $callbackDeserializer = $container->get('payoneer_sdk.callback_deserializer');

            /** @var CustomerDeserializerInterface $customerDeserializer */
            $customerDeserializer = $container->get('payoneer_sdk.customer_deserializer');

            /** @var PaymentDeserializerInterface $paymentDeserializer */
            $paymentDeserializer = $container->get('payoneer_sdk.payment_deserializer');

            /** @var IdentificationDeserializerInterface $identificationDeserializer */
            $identificationDeserializer = $container->get('payoneer_sdk.identification_deserializer');

            /** @var StyleDeserializerInterface $styleDeserializer */
            $styleDeserializer = $container->get('payoneer_sdk.style_deserializer');

            /** @var StatusDeserializerInterface $statusDeserializer */
            $statusDeserializer = $container->get('payoneer_sdk.status_deserializer');
            /** @var RedirectDeserializerInterface $redirectDeserializer */
            $redirectDeserializer = $container->get('payoneer_sdk.redirect_deserializer');
            /** @var ProductDeserializerInterface $productDeserializer */
            $productDeserializer = $container->get('payoneer_sdk.product_deserializer');
            /** @var ProcessingModelDeserializerInterface $processingModelDeserializer */
            $processingModelDeserializer = $container->get('payoneer_sdk.processing_model_deserializer');

            return new ListDeserializer(
                $listFactory,
                $callbackDeserializer,
                $customerDeserializer,
                $paymentDeserializer,
                $statusDeserializer,
                $redirectDeserializer,
                $identificationDeserializer,
                $styleDeserializer,
                $productDeserializer,
                $processingModelDeserializer
            );
        },

        'payoneer_sdk.redirect_deserializer' => static function (
            ContainerInterface $container
        ): RedirectDeserializerInterface {
            $redirectFactory = $container->get('payoneer_sdk.redirect_factory');
            return new RedirectDeserializer($redirectFactory);
        },

        'payoneer_sdk.payment_serializer' => static function (): PaymentSerializerInterface {
            return new PaymentSerializer();
        },

        'payoneer_sdk.product_serializer' => static function (): ProductSerializerInterface {
            return new ProductSerializer();
        },
        'payoneer_sdk.customer_serializer' => static function (
            ContainerInterface $container
        ): CustomerSerializerInterface {
            /** @var PhoneSerializerInterface $phoneSerializer */
            $phoneSerializer = $container->get('payoneer_sdk.phone_serializer');
            /** @var AddressSerializerInterface $addressSerializer */
            $addressSerializer = $container->get('payoneer_sdk.address_serializer');
            /** @var RegistrationSerializerInterface $registrationSerializer */
            $registrationSerializer = $container->get('payoneer_sdk.registration_serializer');
            /** @var NameSerializerInterface $nameSerializer */
            $nameSerializer = $container->get('payoneer_sdk.name_serializer');

            /** @var PhoneSerializerInterface */
            return new CustomerSerializer(
                $phoneSerializer,
                $addressSerializer,
                $registrationSerializer,
                $nameSerializer
            );
        },
        'payoneer_sdk.processing_model_deserializer' => static function (
            ContainerInterface $container
        ): ProcessingModelDeserializerInterface {
            /** @var ProcessingModelFactoryInterface $processingModelFactory */
            $processingModelFactory = $container->get('payoneer_sdk.processing_model_factory');
            return new ProcessingModelDeserializer($processingModelFactory);
        },
        'payoneer_sdk.identification_serializer' =>
            static function (): IdentificationSerializerInterface {
                return new IdentificationSerializer();
            },
        'payoneer_sdk.phone_serializer' => static function (): PhoneSerializerInterface {
            return new PhoneSerializer();
        },

        'payoneer_sdk.name_serializer' => static function (): NameSerializerInterface {
            return new NameSerializer();
        },

        'payoneer_sdk.address_serializer' => static function (
            ContainerInterface $container
        ): AddressSerializerInterface {
            /** @var NameSerializerInterface $nameSerializer */
            $nameSerializer = $container->get('payoneer_sdk.name_serializer');

            return new AddressSerializer($nameSerializer);
        },

        'payoneer_sdk.header_serializer' => static function (): HeaderSerializerInterface {
            return new HeaderSerializer();
        },

        'payoneer_sdk.status_serializer' => static function (): StatusSerializerInterface {
            return new StatusSerializer();
        },

        'payoneer_sdk.callback_serializer' => static function (
            ContainerInterface $container
        ): CallbackSerializerInterface {
            /** @var HeaderSerializerInterface $headerSerializer */
            $headerSerializer = $container->get('payoneer_sdk.header_serializer');

            return new CallbackSerializer($headerSerializer);
        },

        'payoneer_sdk.redirect_serializer' => static function (): RedirectSerializerInterface {
            return new RedirectSerializer();
        },

        'payoneer_sdk.style_serializer' => static function (): StyleSerializerInterface {
            return new StyleSerializer();
        },

        'payoneer_sdk.system_serializer' => static function (): SystemSerializerInterface {
            return new SystemSerializer();
        },

        'payoneer_sdk.registration_serializer' =>
                static function (): RegistrationSerializerInterface {
                    return new RegistrationSerializer();
                },

        'payoneer_sdk.list_serializer' => static function (
            ContainerInterface $container
        ): ListSerializerInterface {
            /** @var IdentificationSerializerInterface $identificationSerializer */
            $identificationSerializer = $container->get('payoneer_sdk.identification_serializer');
            /** @var PaymentSerializerInterface $paymentSerializer */
            $paymentSerializer = $container->get('payoneer_sdk.payment_serializer');
            /** @var CustomerSerializerInterface $customerSerializer */
            $customerSerializer = $container->get('payoneer_sdk.customer_serializer');
            /** @var StyleSerializerInterface $styleSerializer */
            $styleSerializer = $container->get('payoneer_sdk.style_serializer');
            /** @var StatusSerializerInterface $statusSerializer */
            $statusSerializer = $container->get('payoneer_sdk.status_serializer');
            /** @var RedirectSerializerInterface $redirectSerializer */
            $redirectSerializer = $container->get('payoneer_sdk.redirect_serializer');
            /** @var  ProductSerializerInterface $productSerializer */
            $productSerializer = $container->get('payoneer_sdk.product_serializer');
            /** @var ProcessingModelSerializerInterface $processingModelSerializer */
            $processingModelSerializer = $container->get('payoneer_sdk.processing_model_serializer');

            return new ListSerializer(
                $identificationSerializer,
                $paymentSerializer,
                $statusSerializer,
                $customerSerializer,
                $styleSerializer,
                $redirectSerializer,
                $productSerializer,
                $processingModelSerializer
            );
        },

        'payoneer_sdk.commands.update_request_path_template' => static function (): string {
            return 'lists/%1$s';
        },

        'payoneer_sdk.command.error_factory' =>
            static function (): InteractionErrorFactoryInterface {
                return new InteractionErrorFactory(InteractionException::class);
            },

        'payoneer_sdk.command.error_messages' =>
            static function (ContainerInterface $c): array {
                return [
                    'ABORT' => 'The transaction has been aborted',
                    'TRY_OTHER_NETWORK' => 'Please try another network',
                    'TRY_OTHER_ACCOUNT' => 'Please try another account',
                    'RETRY' => 'Please attempt the transaction again',
                    'VERIFY' => 'Transaction requires verification',
                ];
            },

        'payoneer_sdk.command_response_validator.errors' =>
            static function (ContainerInterface $c): array {
                /** @var array<string, string> $messages */
                $messages = $c->get('payoneer_sdk.command.error_messages');
                /** @var InteractionErrorFactoryInterface $f */
                $f = $c->get('payoneer_sdk.command.error_factory');
                $product = [];

                foreach ($messages as $code => $message) {
                    $product[$code] = $f->createInteractionError($code, $message);
                }

                return $product;
            },

        'payoneer_sdk.command_response_validator' =>
            static function (ContainerInterface $container): ResponseValidatorInterface {
                /** @var array<string, InteractionErrorInterface> */
                $errors = $container->get('payoneer_sdk.command_response_validator.errors');
                $errorCodes = array_keys($errors);
                $validator = new InteractionCodeValidator($errorCodes);

                return $validator;
            },

        'payoneer_sdk.processing_model_serializer' => static function (): ProcessingModelSerializerInterface {
            return new ProcessingModelSerializer();
        },

        'payoneer_sdk.default_country' => static function (): string {
            return 'US';
        },

        'payoneer_sdk.commands.create' => static function (
            ContainerInterface $container
        ): CreateListCommandInterface {
            /** @var ApiClientInterface $apiClient */
            $apiClient = $container->get('payoneer_sdk.api_client');
            /** @var string $requestPathTemplate */
            $requestPathTemplate = $container->get('payoneer_sdk.commands.update_request_path_template');
            /** @var ListDeserializerInterface $listDeserializer */
            $listDeserializer = $container->get('payoneer_sdk.list_deserializer');
            /** @var CustomerSerializerInterface $customerSerializer */
            $customerSerializer = $container->get('payoneer_sdk.customer_serializer');
            /** @var PaymentSerializerInterface $paymentSerializer */
            $paymentSerializer = $container->get('payoneer_sdk.payment_serializer');
            /** @var CallbackSerializerInterface $callbackSerializer */
            $callbackSerializer = $container->get('payoneer_sdk.callback_serializer');
            /** @var SystemSerializerInterface $systemSerializer */
            $systemSerializer = $container->get('payoneer_sdk.system_serializer');
            $productSerializer = $container->get('payoneer_sdk.product_serializer');
            $styleSerializer = $container->get('payoneer_sdk.style_serializer');
            $responseValidator = $container->get('payoneer_sdk.command_response_validator');
            assert($responseValidator instanceof ResponseValidatorInterface);
            /** @var array<string, InteractionErrorInterface> */
            $errors = $container->get('payoneer_sdk.command_response_validator.errors');
            $country = $container->get('payoneer_sdk.default_country');

            return new CreateListCommand(
                $errors,
                $apiClient,
                $requestPathTemplate,
                $listDeserializer,
                $customerSerializer,
                $paymentSerializer,
                $callbackSerializer,
                $productSerializer,
                $styleSerializer,
                $responseValidator,
                $systemSerializer,
                $country
            );
        },

        'payoneer_sdk.commands.update' => static function (
            ContainerInterface $container
        ): UpdateListCommandInterface {
            /** @var ApiClientInterface $apiClient */
            $apiClient = $container->get('payoneer_sdk.api_client');
            /** @var string $requestPathTemplate */
            $requestPathTemplate = $container->get('payoneer_sdk.commands.update_request_path_template');
            /** @var ListDeserializerInterface $listDeserializer */
            $listDeserializer = $container->get('payoneer_sdk.list_deserializer');
            /** @var CustomerSerializerInterface $customerSerializer */
            $customerSerializer = $container->get('payoneer_sdk.customer_serializer');
            /** @var PaymentSerializerInterface $paymentSerializer */
            $paymentSerializer = $container->get('payoneer_sdk.payment_serializer');
            /** @var CallbackSerializerInterface $callbackSerializer */
            $callbackSerializer = $container->get('payoneer_sdk.callback_serializer');
            /** @var SystemSerializerInterface $systemSerializer */
            $systemSerializer = $container->get('payoneer_sdk.system_serializer');
            $productSerializer = $container->get('payoneer_sdk.product_serializer');
            $responseValidator = $container->get('payoneer_sdk.command_response_validator');
            assert($responseValidator instanceof ResponseValidatorInterface);
            /** @var array<string, InteractionErrorInterface> */
            $errors = $container->get('payoneer_sdk.command_response_validator.errors');
            $country = $container->get('payoneer_sdk.default_country');

            return new UpdateListCommand(
                $errors,
                $apiClient,
                $requestPathTemplate,
                $listDeserializer,
                $customerSerializer,
                $paymentSerializer,
                $callbackSerializer,
                $productSerializer,
                $responseValidator,
                $systemSerializer,
                $country
            );
        },

        'payoneer_sdk.commands.charge_request_path_template' => static function (): string {
            return 'lists/%1$s/charge';
        },

        'payoneer_sdk.commands.charge' => static function (
            ContainerInterface $container
        ): ChargeCommandInterface {
            /** @var ApiClientInterface $apiClient */
            $apiClient = $container->get('payoneer_sdk.api_client');

            /** @var string $pathTemplate */
            $pathTemplate = $container->get('payoneer_sdk.commands.charge_request_path_template');

            /** @var ListDeserializerInterface $listDeserializer */
            $listDeserializer = $container->get('payoneer_sdk.list_deserializer');

            /** @var PaymentSerializerInterface $paymentSerializer */
            $paymentSerializer = $container->get('payoneer_sdk.payment_serializer');
            /** @var ProductSerializerInterface $productSerializer */
            $productSerializer = $container->get('payoneer_sdk.product_serializer');
            assert($productSerializer instanceof ProductSerializerInterface);

            $responseValidator = $container->get('payoneer_sdk.command_response_validator');
            assert($responseValidator instanceof ResponseValidatorInterface);

            /** @var array<string, InteractionErrorInterface> */
            $errors = $container->get('payoneer_sdk.command_response_validator.errors');

            return new ChargeCommand(
                $errors,
                $apiClient,
                $pathTemplate,
                $listDeserializer,
                $paymentSerializer,
                $productSerializer,
                $responseValidator
            );
        },

        'payoneer_sdk.commands.payout_request_path_template' => static function (): string {
            return '/charges/%1$s/payout';
        },

        'payoneer_sdk.commands.payout' => static function (
            ContainerInterface $container
        ): CommandInterface {
            /** @var ApiClientInterface $apiClient */
            $apiClient = $container->get('payoneer_sdk.api_client');

            /** @var string $pathTemplate */
            $pathTemplate = $container->get('payoneer_sdk.commands.payout_request_path_template');

            /** @var ListDeserializerInterface $listDeserializer */
            $listDeserializer = $container->get('payoneer_sdk.list_deserializer');

            /** @var PaymentSerializerInterface $paymentSerializer */
            $paymentSerializer = $container->get('payoneer_sdk.payment_serializer');

            /** @var ProductSerializerInterface $productSerializer */
            $productSerializer = $container->get('payoneer_sdk.product_serializer');

            $responseValidator = $container->get('payoneer_sdk.command_response_validator');
            assert($responseValidator instanceof ResponseValidatorInterface);

            /** @var array<string, InteractionErrorInterface> */
            $errors = $container->get('payoneer_sdk.command_response_validator.errors');

            return new PayoutCommand(
                $errors,
                $apiClient,
                $pathTemplate,
                $listDeserializer,
                $paymentSerializer,
                $responseValidator,
                $productSerializer
            );
        },

        'payoneer_sdk.integration' => static function (): string {
            return PayoneerIntegrationTypes::SELECTIVE_NATIVE;
        },

        'payoneer.sdk.payoneer.default_headers' => static function (): array {
            return [];
        },

        'payoneer.sdk.invoice_id_provider' => static function (): InvoiceIdProviderInterface {
            return new UniqidInvoiceIdProvider();
        },

        'payoneer_sdk.payoneer' => static function (
            ContainerInterface $container
        ): PayoneerInterface {
            /** @var ApiClientInterface  $apiClient */
            $apiClient = $container->get('payoneer_sdk.api_client');

            /** @var ListDeserializerInterface $listDeserializer */
            $listDeserializer = $container->get('payoneer_sdk.list_deserializer');

            /** @var CreateListCommandInterface $createCommand */
            $createCommand = $container->get('payoneer_sdk.commands.create');

            /** @var UpdateListCommandInterface $updateCommand */
            $updateCommand = $container->get('payoneer_sdk.commands.update');

            /** @var ChargeCommandInterface $chargeCommand */
            $chargeCommand = $container->get('payoneer_sdk.commands.charge');

            /** @var PayoutCommandInterface $payoutCommand */
            $payoutCommand = $container->get('payoneer_sdk.commands.payout');

            /** @var ProductSerializerInterface $productSerializer */
            $productSerializer = $container->get('payoneer_sdk.product_serializer');

            /** @var array<string, string> $defaultHeaders */
            $defaultHeaders = $container->get('payoneer.sdk.payoneer.default_headers');

            /** @var string $integration */
            $integration = $container->get('payoneer_sdk.integration');

            /** @var CustomerSerializerInterface $customerSerializer */
            $customerSerializer = $container->get('payoneer_sdk.customer_serializer');

            /** @var PaymentSerializerInterface $paymentSerializer */
            $paymentSerializer = $container->get('payoneer_sdk.payment_serializer');

            /** @var CallbackSerializerInterface $callbackSerializer */
            $callbackSerializer = $container->get('payoneer_sdk.callback_serializer');

            /** @var StyleSerializerInterface $styleSerializer */
            $styleSerializer = $container->get('payoneer_sdk.style_serializer');

            /** @var SystemSerializerInterface $systemSerializer */
            $systemSerializer = $container->get('payoneer_sdk.system_serializer');

            return new Payoneer(
                $apiClient,
                $listDeserializer,
                $styleSerializer,
                $defaultHeaders,
                $createCommand,
                $updateCommand,
                $chargeCommand,
                $payoutCommand,
                $customerSerializer,
                $paymentSerializer,
                $callbackSerializer,
                $productSerializer,
                $systemSerializer,
                $integration
            );
        },
    ];
};
