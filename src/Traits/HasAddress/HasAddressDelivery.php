<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use RiseTechApps\Address\Models\Address;
use RiseTechApps\Address\Support\AddressPayloadResolver;

trait HasAddressDelivery
{
    public function addressDelivery(): MorphMany
    {
        return $this->morphMany(Address::class, 'address')
            ->where('type', Address::TYPE_DELIVERY);
    }

    public function deliveryAddressDefault(): ?Address
    {
        return $this->addressDelivery()->default()->first();
    }

    /**
     * Sincroniza endereços de entrega.
     *
     * @param array $data Dados que podem conter 'address_delivery' ou 'person.address_delivery'
     * @return void
     */
    public function syncAddressDelivery(array $data): void
    {
        // Resolve múltiplos endereços de entrega
        $deliveryAddresses = AddressPayloadResolver::multiple($data, 'address_delivery');

        if (empty($deliveryAddresses)) {
            return;
        }

        // Remove endereços antigos
        $this->addressDelivery()->delete();

        // Cria novos
        foreach ($deliveryAddresses as $addressData) {
            $addressData = array_filter($addressData, fn($value) => $value !== null && $value !== '');

            if (empty($addressData)) {
                continue;
            }

            Address::create([
                ...$addressData,
                'address_type' => get_class($this),
                'address_id' => $this->getKey(),
                'type' => Address::TYPE_DELIVERY,
            ]);
        }
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
