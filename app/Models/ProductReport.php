<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReport extends Model
{
    public const REASONS = [
        'barang_berbahaya' => 'Barang berbahaya',
        'barang_ilegal' => 'Barang ilegal',
        'barang_terlarang' => 'Barang terlarang',
        'penipuan' => 'Dugaan penipuan',
        'informasi_menyesatkan' => 'Informasi menyesatkan',
        'lainnya' => 'Lainnya',
    ];

    protected $fillable = ['user_id', 'product_id', 'reason', 'details', 'status'];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
