<?php

namespace RiseTechApps\Address;

use Illuminate\Support\Arr;

class Address
{
    protected static array $address = [];

    public static function setAddress(array $address): void
    {
        static::$address=$address;
    }

    public static function getAddress(): array{
        return static::$address;
    }

    public static function fillWithDefault($address, $model)
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
