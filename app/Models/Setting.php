<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * There is only ever ONE row in this table -- the whole app's settings
 * live on that single record, rather than one row per setting. See
 * setting() in app/helpers.php for how the rest of the app reads it.
 */
class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_name',
        'company_logo',
        'company_address',
        'company_phone',
        'company_email',
        'receipt_footer_text',
        'currency_symbol',
    ];

    /**
     * Get the one settings row, creating it with sensible defaults the
     * first time anything asks for it.
     */
    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'company_name' => 'White Pearl City Gwadar',
            'currency_symbol' => 'Rs.',
        ]);
    }
}
