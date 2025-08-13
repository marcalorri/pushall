<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WalletPass extends Model
{
    use HasUuids;

    /**
     * The primary key type is UUID.
     */
    protected $keyType = 'string';

    /**
     * The primary key is non-incrementing.
     */
    public $incrementing = false;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'user_id',
        'name',
        'platform',
        'type',
        'serial_number',
        'class_id',
        'object_id',
        'device_library_identifier',
        'push_token',
        'status',
        'meta',
        'image_path',
        'logo_path',
        'strip_path',
        'background_path',
        'thumbnail_path',
        'icon_path',
    ];

    /**
     * Attribute type casts.
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Owner relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Related pass notifications.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(PassNotification::class);
    }
}
