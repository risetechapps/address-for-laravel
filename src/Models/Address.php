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

    public const TYPE_DEFAULT = 'DEFAULT';
    public const TYPE_DELIVERY = 'DELIVERY';
    public const TYPE_BILLING = 'BILLING';

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

    /**
     * Sincroniza (cria ou atualiza) um endereço para um modelo.
     *
     * Busca endereço em $data['address'] ou $data['person']['address'].
     * Funciona tanto para criação quanto atualização.
     *
     * @param Model $model O modelo que terá o endereço (Profile, User, etc)
     * @param array $data Array de dados que pode conter o endereço
     * @param string $type Tipo do endereço: DEFAULT, BILLING, DELIVERY
     * @param bool $setAsDefault Se deve definir como endereço padrão
     * @return static|null O endereço criado/atualizado ou null se não houver dados
     *
     * @example
     * // Via HTTP Request
     * Address::syncForModel($profile, $request->all());
     *
     * // Via array manual
     * Address::syncForModel($profile, ['person' => ['address' => [...]]]);
     *
     * // Diretamente com endereço
     * Address::syncForModel($profile, ['address' => [...]]);
     */
    public static function syncForModel(Model $model, array $data, string $type = self::TYPE_DEFAULT, bool $setAsDefault = true): ?static
    {
        $addressData = static::extractAddressFromData($data);

        if (empty($addressData)) {
            return null;
        }

        // Limpar campos vazios e normalizar
        $addressData = array_filter($addressData, fn($value) => $value !== null && $value !== '');

        // Verificar se já existe endereço deste tipo
        $existingAddress = static::where('address_type', get_class($model))
            ->where('address_id', $model->getKey())
            ->where('type', $type)
            ->first();

        if ($existingAddress) {
            $existingAddress->update($addressData);
            $address = $existingAddress;
        } else {
            $addressData['address_type'] = get_class($model);
            $addressData['address_id'] = $model->getKey();
            $addressData['type'] = $type;
            $address = static::create($addressData);
        }

        if ($setAsDefault) {
            $address->setAsDefault();
        }

        return $address;
    }

    /**
     * Extrai dados de endereço de um array.
     *
     * Busca em: 'address', 'person.address', ou retorna o próprio array se for endereço puro.
     *
     * @param array $data Array de dados
     * @return array Dados do endereço ou array vazio
     */
    protected static function extractAddressFromData(array $data): array
    {
        // Caso 1: Dados diretos em 'address'
        if (isset($data['address']) && is_array($data['address'])) {
            // Verifica se é um endereço válido (tem campos esperados)
            if (static::isValidAddressData($data['address'])) {
                return $data['address'];
            }
        }

        // Caso 2: Dados aninhados em 'person.address'
        if (isset($data['person']['address']) && is_array($data['person']['address'])) {
            if (static::isValidAddressData($data['person']['address'])) {
                return $data['person']['address'];
            }
        }

        // Caso 3: O próprio array é o endereço (chaves como zip_code, state, etc)
        if (static::isValidAddressData($data)) {
            // Verifica se tem pelo menos campos de endereço
            $addressFields = ['zip_code', 'state', 'city', 'address', 'district'];
            $hasAddressField = false;
            foreach ($addressFields as $field) {
                if (isset($data[$field])) {
                    $hasAddressField = true;
                    break;
                }
            }
            if ($hasAddressField) {
                return $data;
            }
        }

        return [];
    }

    /**
     * Verifica se um array contém dados válidos de endereço.
     *
     * @param array $data
     * @return bool
     */
    protected static function isValidAddressData(array $data): bool
    {
        // Deve ser um array associativo (não uma lista)
        if (empty($data) || array_is_list($data)) {
            return false;
        }

        // Deve ter pelo menos um campo típico de endereço
        $addressFields = ['zip_code', 'state', 'city', 'address', 'district', 'street', 'number'];
        foreach ($addressFields as $field) {
            if (array_key_exists($field, $data)) {
                return true;
            }
        }

        return false;
    }
}
