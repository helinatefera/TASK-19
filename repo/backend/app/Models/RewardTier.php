<?php

namespace App\Models;

use App\Enums\FulfillmentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RewardTier extends Model
{
    protected $fillable = [
        'campaign_id',
        'title',
        'description',
        'price',
        'quantity_total',
        'quantity_claimed',
        'estimated_delivery_at',
        'fulfillment_type',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'fulfillment_type' => FulfillmentType::class,
            'estimated_delivery_at' => 'date',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
