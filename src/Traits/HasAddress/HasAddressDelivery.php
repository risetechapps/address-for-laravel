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

    public function deliveryAddressDefault(): ?Address
    {
        return $this->addressDelivery()->default()->first();
    }

    /**
     * Get the most used delivery addresses.
     */
    public function mostUsedDeliveryAddresses(int $limit = 5)
    {
        return $this->addressDelivery()->mostUsed($limit)->get();
    }

    /**
     * Get the most used delivery address.
     */
    public function mostUsedDeliveryAddress(): ?Address
    {
        return $this->addressDelivery()->mostUsed(1)->first();
    }
}
