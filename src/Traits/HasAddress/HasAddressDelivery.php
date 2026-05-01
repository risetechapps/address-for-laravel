<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDeliveryEvent;
use RiseTechApps\Address\Models\Address;

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
            if (empty(array_filter($addressData))) {
                continue;
            }

            Address::create([
                'address_type' => get_class($this),
                'address_id' => $this->getKey(),
                'type' => Address::TYPE_DELIVERY,
                ...$addressData,
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
