<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'display_name',
        'value',
        'type',
        'description',
        'group',
        'display_order',
        'created_by',
        'updated_by',
    ];

    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }
        
        // Convert the value based on type
        switch ($setting->type) {
            case 'boolean':
                return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($setting->value) ? (float) $setting->value : $default;
            case 'json':
                return json_decode($setting->value, true) ?: $default;
            default:
                return $setting->value;
        }
    }

    /**
     * Set a setting value by key
     *
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public static function setValue(string $key, $value)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }
        
        // Convert the value based on type before saving
        switch ($setting->type) {
            case 'boolean':
                $setting->value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
                break;
            case 'json':
                $setting->value = is_array($value) || is_object($value) 
                    ? json_encode($value) 
                    : $value;
                break;
            default:
                $setting->value = (string) $value;
        }
        
        $setting->updated_by = auth()->id();
        return $setting->save();
    }
}
