<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DictionaryItem extends Model
{
    protected $fillable = [
        'dictionary_id',
        'key',
        'value',
        'sort_order',
    ];

    public function dictionary(): BelongsTo
    {
        return $this->belongsTo(Dictionary::class);
    }
}
