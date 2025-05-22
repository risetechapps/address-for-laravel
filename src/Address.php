<?php

namespace RiseTechApps\Address;

use Illuminate\Support\Arr;

class Address
{
    protected static array $address = [];
    protected static array $billing = [];
    protected static array $delivery = [];

    public static function setAddress(array $address): void
    {
        static::$address = $address;
    }

    public static function setAddressBilling(array $addressBilling): void
    {
        static::$billing = $addressBilling;
    }

    public static function setAddressDelivery(array $addressDelivery): void
    {
        static::$delivery = $addressDelivery;
    }

    public static function getAddress(): array
    {
        return static::$address;
    }

    public static function getAddressBilling(): array
    {
        return static::$billing;
    }

    public static function getAddressDelivery(): array
    {
        return static::$delivery;
    }

    public static function fillWithDefault($address, $model): array
    {
        $defaultAddress = $model->address()->first();

        $fields = ['zip_code', 'state', 'city', 'district', 'address', 'number', 'complement'];

        foreach ($fields as $field) {
            if (!Arr::exists($address, $field) || empty($address[$field])) {
                $address[$field] = $defaultAddress ? $defaultAddress->getOriginal($field) : null;
            }
        }

        return $address;
    }

}
