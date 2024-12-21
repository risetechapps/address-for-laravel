<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\HasMany;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateBillingEvent;
use RiseTechApps\Address\Model\Address;

trait HasAddressBilling
{
    public static function bootHasAddressBilling(): void
    {
        static::saved(function ($model) {
            event(new AddressCreateOrUpdateBillingEvent($model));
        });

    }

    public function addressBilling(): HasMany
    {
        return $this->hasMany(Address::class, 'address_id')->where('type', 'BILLING');
    }
}
