<?php

namespace RiseTechApps\Address\Traits\HasAddress;

use Illuminate\Database\Eloquent\Relations\MorphOne;
use RiseTechApps\Address\Events\Address\AddressCreateOrUpdateDefaultEvent;
use RiseTechApps\Address\Models\Address;

trait HasAddress
{
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'address')->where('type', Address::TYPE_DEFAULT);
    }

    /**
     * Sincroniza o endereço padrão deste modelo.
     *
     * @param array $data Dados que podem conter 'address' ou 'person.address'
     * @param bool $setAsDefault Se deve definir como padrão
     * @return Address|null
     */
    public function syncAddress(array $data, bool $setAsDefault = true): ?Address
    {
        return Address::syncForModel($this, $data, Address::TYPE_DEFAULT, $setAsDefault);
    }

    /**
     * Define o endereço deste modelo.
     *
     * Método alternativo mais curto para syncAddress.
     *
     * @param array $addressData Dados do endereço
     * @param bool $setAsDefault
     * @return Address|null
     */
    public function setAddress(array $addressData, bool $setAsDefault = true): ?Address
    {
        return Address::syncForModel($this, $addressData, Address::TYPE_DEFAULT, $setAsDefault);
    }
}
