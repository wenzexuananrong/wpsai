<?php

declare(strict_types=1);

namespace Inpsyde\PayoneerSdk\Api\Entities\Customer;

use Inpsyde\PayoneerSdk\Api\ApiException;
use Inpsyde\PayoneerSdk\Api\Entities\Address\AddressDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Name\NameDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Phone\PhoneDeserializerInterface;
use Inpsyde\PayoneerSdk\Api\Entities\Registration\RegistrationDeserializerInterface;

class CustomerDeserializer implements CustomerDeserializerInterface
{
    /**
     * @var CustomerFactoryInterface Service able to create Customer instance.
     */
    protected $customerFactory;
    /**
     * @var PhoneDeserializerInterface
     */
    protected $phoneDeserializer;
    /**
     * @var AddressDeserializerInterface
     */
    protected $addressDeserializer;

    /**
     * @var RegistrationDeserializerInterface
     */
    protected $registrationDeserializer;
    /**
     * @var NameDeserializerInterface
     */
    protected $nameDeserializer;

    /**
     * @param CustomerFactoryInterface $customerFactory
     * @param PhoneDeserializerInterface $phoneDeserializer
     * @param AddressDeserializerInterface $addressDeserializer
     * @param RegistrationDeserializerInterface $registrationDeserializer
     * @param NameDeserializerInterface $nameDeserializer
     */
    public function __construct(
        CustomerFactoryInterface $customerFactory,
        PhoneDeserializerInterface $phoneDeserializer,
        AddressDeserializerInterface $addressDeserializer,
        RegistrationDeserializerInterface $registrationDeserializer,
        NameDeserializerInterface $nameDeserializer
    ) {

        $this->customerFactory = $customerFactory;
        $this->phoneDeserializer = $phoneDeserializer;
        $this->addressDeserializer = $addressDeserializer;
        $this->registrationDeserializer = $registrationDeserializer;
        $this->nameDeserializer = $nameDeserializer;
    }

    /**
     * @inheritDoc
     */
    public function deserializeCustomer(array $customerData): CustomerInterface
    {
        if (! isset($customerData['number'])) {
            throw new ApiException('Data contains no expected number element.');
        }
        $number = $customerData['number'];

        if (isset($customerData['phones'])) {
            $phonesData = $customerData['phones'];
            $mobilePhone = $this->phoneDeserializer->deserializePhone($phonesData['mobile']);
            $phones = ['mobile' => $mobilePhone];
        }

        if (isset($customerData['addresses'])) {
            $addressesData = $customerData['addresses'];
            $billingAddress = $this->addressDeserializer->deserializeAddress($addressesData['billing']);
            $addresses = ['billing' => $billingAddress];

            if (isset($addressesData['shipping'])) {
                $shippingAddress = $this->addressDeserializer->deserializeAddress($addressesData['shipping']);
                $addresses['shipping'] = $shippingAddress;
            }
        }

        $email = $customerData['email'] ?? null;

        $deliveryEmail = $customerData['deliveryEmail'] ?? null;

        $registration = isset($customerData['registration']) ?
            $this->registrationDeserializer->deserializeRegistration($customerData['registration'])
            : null;

        $name = isset($customerData['name']) ?
            $this->nameDeserializer->deserializeName($customerData['name'])
            : null;

        return $this->customerFactory->createCustomer(
            $number,
            $phones ?? null,
            $addresses ?? null,
            $email,
            $deliveryEmail,
            $registration,
            $name
        );
    }
}
