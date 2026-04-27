<?php

namespace RiseTechApps\Address\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use RiseTechApps\Address\Models\AddressHistory;
use RiseTechApps\Address\Models\AddressUsageLog;
use RiseTechApps\HasUuid\Traits\HasUuid;
use RiseTechApps\Monitoring\Traits\HasLoggly\HasLoggly;
use RiseTechApps\ToUpper\Traits\HasToUpper;

class Address extends Model
{
    use HasFactory, Notifiable, HasUuid, SoftDeletes, HasToUpper, HasLoggly;
    use Prunable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'address_type',
        'address_id',
        'zip_code',
        'country',
        'state',
        'city',
        'district',
        'address',
        'number',
        'complement',
        'type',
        'is_default',
        'usage_count',
        'last_used_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'address_type',
        'address_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    protected $appends = ['full_address'];

    /**
     * Get the owning addressable model.
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo('address', 'address_type', 'address_id');
    }

    /**
     * Get the history entries for this address.
     */
    public function history(): HasMany
    {
        return $this->hasMany(AddressHistory::class, 'address_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get the latest history entry.
     */
    public function latestHistory(): ?AddressHistory
    {
        return $this->history()->first();
    }

    /**
     * Get the usage logs for this address.
     */
    public function usageLogs(): HasMany
    {
        return $this->hasMany(AddressUsageLog::class, 'address_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Increment the usage count and create a log entry.
     */
    public function incrementUsage(string $action = 'general', ?array $metadata = null, ?int $userId = null): AddressUsageLog
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);

        return $this->usageLogs()->create([
            'action' => $action,
            'metadata' => $metadata,
            'user_id' => $userId ?? Auth::id(),
        ]);
    }

    /**
     * Scope to get most used addresses.
     */
    public function scopeMostUsed(Builder $query, int $limit = null): Builder
    {
        $query->orderBy('usage_count', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query;
    }

    /**
     * Scope to get recently used addresses.
     */
    public function scopeRecentlyUsed(Builder $query, int $days = 30): Builder
    {
        return $query->whereNotNull('last_used_at')
            ->where('last_used_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to get addresses never used.
     */
    public function scopeNeverUsed(Builder $query): Builder
    {
        return $query->where('usage_count', 0);
    }

    /**
     * Scope to get addresses used more than X times.
     */
    public function scopeUsedMoreThan(Builder $query, int $times): Builder
    {
        return $query->where('usage_count', '>', $times);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = [
            $this->address,
            $this->number ? ', ' . $this->number : '',
            $this->complement ? ' - ' . $this->complement : '',
            $this->district ? ', ' . $this->district : '',
            $this->city ? ', ' . $this->city : '',
            $this->state ? ' - ' . $this->state : '',
            $this->zip_code ? ' - CEP: ' . $this->zip_code : '',
        ];
        return implode('', array_filter($parts));
    }

    public function prunable(): Builder|Address
    {
        return static::onlyTrashed()->where('deleted_at', '<=', now()->subDays(1));
    }

    /**
     * Scope to get only default addresses.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get addresses by state.
     */
    public function scopeByState(Builder $query, string $state): Builder
    {
        return $query->where('state', $state);
    }

    /**
     * Scope to get addresses by city.
     */
    public function scopeByCity(Builder $query, string $city): Builder
    {
        return $query->where('city', $city);
    }

    /**
     * Scope to get addresses by zip code.
     */
    public function scopeByZipCode(Builder $query, string $zipCode): Builder
    {
        return $query->where('zip_code', $zipCode);
    }

    /**
     * Scope to get addresses by country.
     */
    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }

    /**
     * Scope to get addresses by district/neighborhood.
     */
    public function scopeByDistrict(Builder $query, string $district): Builder
    {
        return $query->where('district', $district);
    }

    /**
     * Set this address as the default for its owner and type.
     * This will unset any other default address of the same type.
     */
    public function setAsDefault(): void
    {
        if (!$this->exists) {
            $this->save();
        }

        static::where('address_type', $this->address_type)
            ->where('address_id', $this->address_id)
            ->where('type', $this->type)
            ->where('is_default', true)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        $this->update(['is_default' => true]);
    }
}
