<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    /** Claves disponibles */
    public const PONENTE_REGISTRATION_OPEN = 'ponente_registration_open';
    public const SUBMISSIONS_OPEN          = 'submissions_open';

    public static function get(string $key): ?string
    {
        return Cache::rememberForever(
            "setting:{$key}",
            fn () => static::where('key', $key)->value('value')
        );
    }

    public static function getBool(string $key, bool $default): bool
    {
        $value = static::get($key);

        return $value === null ? $default : $value === '1';
    }

    public static function setBool(string $key, bool $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value ? '1' : '0']);
        Cache::forget("setting:{$key}");
    }
}
