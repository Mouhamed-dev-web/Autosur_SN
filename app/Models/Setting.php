<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['name', 'label', 'value'];

    /** Règles de validation par paramètre (modifiables dans le back-office). */
    public const RULES = [
        'technical_visit_fee' => 'required|integer|between:1500,3000',
        'technical_visit_validity_months' => 'required|integer|between:1,36',
        'channel_mail_enabled' => 'required|in:0,1',
        'channel_sms_enabled' => 'required|in:0,1',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings'));
        static::deleted(fn () => Cache::forget('settings'));
    }

    public static function get(string $name, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings', fn () => static::pluck('value', 'name')->all());

        return array_key_exists($name, $all) ? $all[$name] : $default;
    }

    public static function int(string $name, int $default): int
    {
        return (int) static::get($name, $default);
    }

    public static function bool(string $name, bool $default): bool
    {
        $value = static::get($name);

        return $value === null ? $default : in_array((string) $value, ['1', 'true', 'on'], true);
    }
}