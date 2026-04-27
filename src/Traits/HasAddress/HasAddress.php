<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\MorphOne;
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

    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'address')->where('type', 'DEFAULT');
    }
}
