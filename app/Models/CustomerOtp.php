<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class CustomerOtp extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'otp_hash',
        'expires_at',
    ];

    public $timestamps = true;

    /**
     * Generate a 6-digit numeric OTP.
     */
    public static function generateOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Hash the OTP for storage.
     */
    public static function hashOtp(string $otp): string
    {
        return Hash::make($otp);
    }

    /**
     * Check if OTP is expired.
     */
    public function isExpired(): bool
    {
        return now()->isAfter($this->expires_at);
    }

    /**
     * Check if OTP matches given value using hash comparison.
     */
    public function matches(string $otp): bool
    {
        return Hash::check($otp, $this->otp_hash);
    }

    /**
     * Find valid (non-expired) OTP for a phone number.
     */
    public static function findValidOtp(string $phone): ?self
    {
        return static::where('phone', $phone)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Clean up expired OTPs.
     */
    public static function cleanExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }
}