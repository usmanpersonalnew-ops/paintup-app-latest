<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'logo_path',
        'primary_color',
        'secondary_color',
        'support_whatsapp',
        'support_email',
        'footer_text',
        'gst_number',
        'address',
        'invoice_prefix',
    ];

    protected $casts = [
        'primary_color' => 'string',
        'secondary_color' => 'string',
    ];

    /**
     * Get the first settings record (singleton pattern)
     */
    public static function getSettings()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PaintUp',
                'primary_color' => '#2563eb',
                'secondary_color' => '#1e293b',
                'invoice_prefix' => 'INV',
            ]
        );
    }

    /**
     * Get company name
     */
    public static function companyName()
    {
        return static::getSettings()->company_name ?? 'PaintUp';
    }

    /**
     * Get logo path
     */
    public static function logoPath()
    {
        return static::getSettings()->logo_path;
    }

    /**
     * Get primary color
     */
    public static function primaryColor()
    {
        return static::getSettings()->primary_color ?? '#2563eb';
    }

    /**
     * Get secondary color
     */
    public static function secondaryColor()
    {
        return static::getSettings()->secondary_color ?? '#1e293b';
    }

    /**
     * Get support WhatsApp
     */
    public static function supportWhatsapp()
    {
        return static::getSettings()->support_whatsapp;
    }

    /**
     * Get support email
     */
    public static function supportEmail()
    {
        return static::getSettings()->support_email;
    }

    /**
     * Get footer text
     */
    public static function footerText()
    {
        return static::getSettings()->footer_text;
    }

    /**
     * Get GST settings as an array
     */
    public static function getGstSettings(): array
    {
        $settings = static::getSettings();
        return [
            'gst_number' => $settings->gst_number ?? null,
            'company_name' => $settings->company_name ?? 'PaintUp',
            'address' => $settings->address ?? null,
            'invoice_prefix' => $settings->invoice_prefix ?? 'INV',
        ];
    }

    /**
     * Get GST number
     */
    public static function gstNumber()
    {
        return static::getSettings()->gst_number;
    }

    /**
     * Get address
     */
    public static function address()
    {
        return static::getSettings()->address;
    }

    /**
     * Get invoice prefix
     */
    public static function invoicePrefix()
    {
        return static::getSettings()->invoice_prefix ?? 'INV';
    }

    /**
     * Get all settings as array for sharing
     */
    public static function toSettingsArray(): array
    {
        $settings = static::getSettings();
        return [
            'company_name' => $settings->company_name,
            'logo_path' => $settings->logo_path,
            'primary_color' => $settings->primary_color,
            'secondary_color' => $settings->secondary_color,
            'support_whatsapp' => $settings->support_whatsapp,
            'support_email' => $settings->support_email,
            'footer_text' => $settings->footer_text,
            'gst_number' => $settings->gst_number,
            'address' => $settings->address,
            'invoice_prefix' => $settings->invoice_prefix,
        ];
    }

    /**
     * Get GST settings for invoice generation
     */
    public static function gstSettings(): array
    {
        $settings = static::getSettings();

        return [
            'company_name' => $settings->company_name,
            'gst_number' => $settings->gst_number,
            'address' => $settings->address,
            'invoice_prefix' => $settings->invoice_prefix ?? 'INV',
            'support_email' => $settings->support_email,
            'support_whatsapp' => $settings->support_whatsapp,
        ];
    }
}
