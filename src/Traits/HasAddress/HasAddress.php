<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\HasOne;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDefaultEvent;
use RiseTechApps\Address\Models\Address;

trait HasAddress
{
    public static function bootHasAddress(): void
    {
        static::saved(function ($model) {
            event(new AddressCreateOrUpdateDefaultEvent($model));
        });
    }

    public function address(): HasOne
    {
        return $this->hasOne(Address::class, 'address_id')->where('type', 'DEFAULT');
    }
}
