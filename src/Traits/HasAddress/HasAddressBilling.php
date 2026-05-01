<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateBillingEvent;
use RiseTechApps\Address\Models\Address;

trait HasAddressBilling
{
    public function addressBilling(): MorphMany
    {
        return $this->morphMany(Address::class, 'address')
            ->where('type', Address::TYPE_BILLING);
    }

    public function billingAddressDefault(): ?Address
    {
        return $this->addressBilling()->default()->first();
    }

    /**
     * Sincroniza endereços de cobrança.
     *
     * @param array $data Dados que podem conter 'address_billing' ou 'person.address_billing'
     * @return void
     */
    public function syncAddressBilling(array $data): void
    {
        // Resolve múltiplos endereços de cobrança
        $billingAddresses = AddressPayloadResolver::multiple($data, 'address_billing');

        if (empty($billingAddresses)) {
            return;
        }

        // Remove endereços antigos
        $this->addressBilling()->delete();

        // Cria novos
        foreach ($billingAddresses as $addressData) {
            if (empty(array_filter($addressData))) {
                continue;
            }

            Address::create([
                'address_type' => get_class($this),
                'address_id' => $this->getKey(),
                'type' => Address::TYPE_BILLING,
                ...$addressData,
            ]);
        }
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
