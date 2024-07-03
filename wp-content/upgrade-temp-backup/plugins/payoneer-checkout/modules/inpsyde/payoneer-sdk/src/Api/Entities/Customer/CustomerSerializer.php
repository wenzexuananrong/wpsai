<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Customer;

use Inpsyde\PayoneerSdk\Api\ApiExceptionInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneSerializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationSerializerInterface;

class CustomerSerializer implements CustomerSerializerInterface
{
    /**
     * @var PhoneSerializerInterface
     */
    protected $phoneSerializer;
    /**
     * @var AddressSerializerInterface
     */
    protected $addressSerializer;
    /**
     * @var RegistrationSerializerInterface
     */
    protected $registrationSerializer;
    /**
     * @var NameSerializerInterface
     */
    protected $nameSerializer;

    /**
     * @param PhoneSerializerInterface $phoneSerializer To serialize customer's phones.
     * @param AddressSerializerInterface $addressSerializer To serialize customer's addresses.
     * @param RegistrationSerializerInterface $registrationSerializer To serialize customer's registration.
     * @param NameSerializerInterface $nameSerializer To serialize customer's name.
     */
    public function __construct(
        PhoneSerializerInterface $phoneSerializer,
        AddressSerializerInterface $addressSerializer,
        RegistrationSerializerInterface $registrationSerializer,
        NameSerializerInterface $nameSerializer
    ) {

        $this->phoneSerializer = $phoneSerializer;
        $this->addressSerializer = $addressSerializer;
        $this->registrationSerializer = $registrationSerializer;
        $this->nameSerializer = $nameSerializer;
    }

    /**
     * @inheritDoc
     */
    public function serializeCustomer(CustomerInterface $customer): array
    {
        $serializedCustomer = [
            'number' => $customer->getNumber(),
        ];

        try {
            $customerPhones = $customer->getPhones();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }

        if (isset($customerPhones)) {
            $serializedCustomer['phones'] = array_map(function (PhoneInterface $phone): array {
                return $this->phoneSerializer->serializePhone($phone);
            }, $customerPhones);
        }

        try {
            $customerAddresses = $customer->getAddresses();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }

        if (isset($customerAddresses)) {
            $serializedAddresses = [
                'billing' => $this->addressSerializer
                    ->serializeAddress($customerAddresses['billing']),
            ];

            if (isset($customerAddresses['shipping'])) {
                $serializedAddresses['shipping'] = $this->addressSerializer
                    ->serializeAddress($customerAddresses['shipping']);
            }
            $serializedCustomer['addresses'] = $serializedAddresses;
        }

        try {
            $serializedCustomer['email'] = $customer->getEmail();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }

        try {
            $serializedCustomer['deliveryEmail'] = $customer->getDeliveryEmail();
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }

        try {
            $registration = $customer->getRegistration();
            $serializedCustomer['registration'] = $this->registrationSerializer
                ->serializeRegistration($registration);
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }

        try {
            $name = $customer->getName();
            $serializedCustomer['name'] = $this->nameSerializer
                ->serializeName($name);
        } catch (ApiExceptionInterface $apiException) {
            //this is an optional field, so it's ok to not have it
        }

        return $serializedCustomer;
    }
}
