<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use RiseTechApps\Address\Models\Address;
use RiseTechApps\Address\Support\AddressPayloadResolver;

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
            $addressData = array_filter($addressData, fn($value) => $value !== null && $value !== '');

            if (empty($addressData)) {
                continue;
            }

            Address::create([
                ...$addressData,
                'address_type' => get_class($this),
                'address_id' => $this->getKey(),
                'type' => Address::TYPE_BILLING,
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
