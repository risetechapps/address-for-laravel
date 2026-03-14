<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDeliveryEvent;
use RiseTechApps\Address\Models\Address;

trait HasAddressDelivery
{
    public static function bootHasAddressDelivery(): void
    {
        static::saved(function ($model) {
            event(new AddressCreateOrUpdateDeliveryEvent($model));
        });

    }

    public function addressDelivery(): MorphMany
    {
        return $this->morphMany(Address::class, 'address')
            ->where('type', 'DELIVERY');
    }
}
