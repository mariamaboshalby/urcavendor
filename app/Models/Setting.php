<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    public const HOME_SLIDER_DEFAULTS = [
        ['desktop' => 'images/slider-1.webp', 'mobile' => null],
        ['desktop' => 'images/slider-2.webp', 'mobile' => null],
        ['desktop' => 'images/slider-3.webp', 'mobile' => null],
        ['desktop' => 'images/slider-4.webp', 'mobile' => null],
    ];

    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function set($key, $value)
    {
        return self::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
