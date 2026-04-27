<?php

namespace RiseTechApps\Address\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RiseTechApps\HasUuid\Traits\HasUuid;

class AddressHistory extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'address_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'user_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the address that owns this history entry.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Get the user who made this change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    /**
     * Get a human-readable description of the change.
     */
    public function getDescriptionAttribute(): string
    {
        return match($this->action) {
            'created' => 'Endereço criado',
            'updated' => 'Endereço atualizado',
            'deleted' => 'Endereço removido',
            'restored' => 'Endereço restaurado',
            default => 'Ação desconhecida',
        };
    }

    /**
     * Get the changes between old and new values.
     */
    public function getChangesAttribute(): array
    {
        $changes = [];
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        $fields = array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($fields as $field) {
            $oldValue = $old[$field] ?? null;
            $newValue = $new[$field] ?? null;

            if ($oldValue !== $newValue) {
                $changes[$field] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    /**
     * Scope to get only creation entries.
     */
    public function scopeCreated($query)
    {
        return $query->where('action', 'created');
    }

    /**
     * Scope to get only update entries.
     */
    public function scopeUpdated($query)
    {
        return $query->where('action', 'updated');
    }

    /**
     * Scope to get only deletion entries.
     */
    public function scopeDeleted($query)
    {
        return $query->where('action', 'deleted');
    }
}