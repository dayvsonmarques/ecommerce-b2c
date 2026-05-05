<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name',
        'store_description',
        'logo_url',
        'address_street',
        'address_number',
        'address_complement',
        'address_neighborhood',
        'address_city',
        'address_state',
        'address_postal_code',
        'shipping_coverage',
    ];

    /**
     * Retorna (ou cria) a única linha de configurações da loja.
     */
    public static function instance(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'store_name'        => 'Minha Loja',
            'shipping_coverage' => 'national',
        ]);
    }
}
