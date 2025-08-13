<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PassNotification extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'wallet_pass_id',
        'name',
        'title',
        'message',
        'button_text',
        'button_url',
        'status',
        'scheduled_at',
        'sent_at',
        'meta',
        'error',
    ];

    protected $casts = [
        'meta' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function walletPass(): BelongsTo
    {
        return $this->belongsTo(WalletPass::class);
    }
}
