<?php

namespace RiseTechApps\Address\Observers;

use Illuminate\Support\Facades\Auth;
use RiseTechApps\Address\Models\Address;
use RiseTechApps\Address\Models\AddressHistory;

class AddressObserver
{
    /**
     * Handle the Address "creating" event.
     */
    public function creating(Address $address): void
    {
        // Define como default automaticamente se for o primeiro endereço deste tipo
        if (is_null($address->is_default)) {
            $exists = Address::where('address_type', $address->address_type)
                ->where('address_id', $address->address_id)
                ->where('type', $address->type)
                ->exists();

            if (! $exists) {
                $address->is_default = true;
            }
        }
    }

    /**
     * Handle the Address "created" event.
     */
    public function created(Address $address): void
    {
        $this->logHistory($address, 'created', null, $address->toArray());
    }

    /**
     * Handle the Address "updated" event.
     */
    public function updated(Address $address): void
    {
        $oldValues = $address->getOriginal();
        $newValues = $address->getChanges();

        // Remove timestamps from comparison
        unset($oldValues['updated_at'], $oldValues['created_at']);

        $this->logHistory($address, 'updated', $oldValues, $newValues);
    }

    /**
     * Handle the Address "deleted" event.
     */
    public function deleted(Address $address): void
    {
        $this->logHistory($address, 'deleted', $address->toArray(), null);
    }

    /**
     * Handle the Address "restored" event.
     */
    public function restored(Address $address): void
    {
        $this->logHistory($address, 'restored', null, $address->toArray());
    }

    /**
     * Log history entry.
     */
    private function logHistory(Address $address, string $action, ?array $oldValues, ?array $newValues): void
    {
        $request = request();

        AddressHistory::create([
            'address_id' => $address->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'user_id' => Auth::id(),
        ]);
    }
}