<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\HasMany;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDeliveryEvent;
use RiseTechApps\Address\Model\Address;

trait HasAddressDelivery
{
    public static function bootHasAddressDelivery(): void
    {
        static::saved(function ($model) {
            event(new AddressCreateOrUpdateDeliveryEvent($model));
        });

    }

    public function addressDelivery(): HasMany
    {
        return $this->hasMany(Address::class, 'address_id')->where('type', 'delivery');
    }
}
