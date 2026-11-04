<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegajoApiCredential extends Model
{
    public const SINGLETON_ID = 1;

    protected $fillable = [
        'token',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'token' => 'encrypted',
        ];
    }

    public static function currentToken(): ?string
    {
        $token = static::query()->find(self::SINGLETON_ID)?->token;

        return filled($token) ? $token : (filled(config('services.legajos.token'))
            ? (string) config('services.legajos.token')
            : null);
    }
}
