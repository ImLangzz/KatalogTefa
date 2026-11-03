<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'price',
        'location_address',
        'location_latitude',
        'location_longitude',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'location_latitude' => 'decimal:7',
            'location_longitude' => 'decimal:7',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ProductReport::class);
    }
}
