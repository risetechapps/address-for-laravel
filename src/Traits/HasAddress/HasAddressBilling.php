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

    public function billingAddressDefault(): ?Address
    {
        return $this->addressBilling()->default()->first();
    }

    /**
     * Get the most used billing addresses.
     */
    public function mostUsedBillingAddresses(int $limit = 5)
    {
        return $this->addressBilling()->mostUsed($limit)->get();
    }

    /**
     * Get the most used billing address.
     */
    public function mostUsedBillingAddress(): ?Address
    {
        return $this->addressBilling()->mostUsed(1)->first();
    }
}
