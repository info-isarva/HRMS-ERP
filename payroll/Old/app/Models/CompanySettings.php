<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySettings extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_name',
        'logo_image',
        'favicon', 
        'contact_person',
        'address',
        'country',
        'city',
        'state_province',
        'postal_code',
        'email',
        'phone_number',
        'mobile_number',
        'fax',
        'website_url',
        'company_pan',
        'company_tan',
    ];

    /**
     * Get the full URL for the logo image
     */
    public function getLogoUrlAttribute()
    {
        if (!$this->logo_image) {
            return null;
        }
        
        $baseUrl = config('app.url') ?: request()->getSchemeAndHttpHost();
        $logoPath = trim($this->logo_image, '/');
        
        return $baseUrl . '/' . $logoPath;
    }

    /**
     * Get the full URL for the favicon
     */
    public function getFaviconUrlAttribute()
    {
        if (!$this->favicon) {
            return null;
        }
        
        $baseUrl = config('app.url') ?: request()->getSchemeAndHttpHost();
        $faviconPath = trim($this->favicon, '/');
        
        return $baseUrl . '/' . $faviconPath;
    }

    /**
     * Get company settings with full URLs
     */
    public static function getWithFullUrls()
    {
        $settings = self::where('id', 1)->first();
        
        if (!$settings) {
            return null;
        }
        
        return [
            'id' => $settings->id,
            'company_name' => $settings->company_name,
            'contact_person' => $settings->contact_person,
            'address' => $settings->address,
            'country' => $settings->country,
            'city' => $settings->city,
            'state_province' => $settings->state_province,
            'postal_code' => $settings->postal_code,
            'email' => $settings->email,
            'phone_number' => $settings->phone_number,
            'mobile_number' => $settings->mobile_number,
            'fax' => $settings->fax,
            'website_url' => $settings->website_url,
            'company_pan' => $settings->company_pan,
            'company_tan' => $settings->company_tan,
            'logo_url' => $settings->logo_url,
            'favicon_url' => $settings->favicon_url,
            'logo_path' => $settings->logo_image,
            'favicon_path' => $settings->favicon,
            'created_at' => $settings->created_at,
            'updated_at' => $settings->updated_at,
        ];
    }
}
