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

        // Diff (não delete-all + recreate): atualiza existentes por id, cria os
        // novos e remove só os ausentes. Evita o churn de soft-delete — cada sync
        // apagava TODOS e recriava, acumulando linhas mortas na tabela.
        $existing = $this->addressBilling()->get()->keyBy('id');
        $processedIds = [];

        foreach ($billingAddresses as $addressData) {
            if (empty(array_filter($addressData))) {
                continue;
            }

            $id = $addressData['id'] ?? null;

            if ($id && isset($existing[$id])) {
                $existing[$id]->update($addressData);
                $processedIds[] = $id;
            } else {
                $created = Address::create([
                    'address_type' => $this::class,
                    'address_id' => $this->getKey(),
                    'type' => Address::TYPE_BILLING,
                    ...$addressData,
                ]);
                $processedIds[] = $created->getKey();
            }
        }

        // Remove os endereços que não vieram no payload.
        $this->addressBilling()->whereNotIn('id', $processedIds)->delete();
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
