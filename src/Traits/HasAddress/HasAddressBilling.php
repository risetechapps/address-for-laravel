<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateBillingEvent;
use RiseTechApps\Address\Models\Address;

trait HasAddressBilling
{
    public static function bootHasAddressBilling(): void
    {
        static::saved(function ($model) {
            event(new AddressCreateOrUpdateBillingEvent($model));
        });

    }

    public function addressBilling(): MorphMany
    {
        return $this->morphMany(Address::class, 'address')
            ->where('type', 'BILLING');
    }
}
