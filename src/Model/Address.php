<?php

namespace RiseTechApps\Address\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use RiseTechApps\HasUuid\Traits\HasUuid\HasUuid;
use RiseTechApps\Monitoring\Traits\HasLoggly\HasLoggly;
use RiseTechApps\ToUpper\Traits\HasToUpper;

class Address extends Model
{
    use HasFactory, Notifiable, HasUuid, SoftDeletes, HasToUpper, HasLoggly;

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
        'type'
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
    ];

    protected $appends = ['full_address'];

    public function getFullAddressAttribute()
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
}
