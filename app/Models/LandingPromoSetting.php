<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandingPromoSetting extends Model
{
    use HasFactory;

    protected $table = 'landing_promo_settings';

    protected $fillable = [
        'heading',
        'image',
        'discount_note',
        'offer_note',
    ];

    public static function current(): self
    {
        $setting = static::query()->first();

        if ($setting) {
            return $setting;
        }

        return static::create([
            'heading' => 'Discounts & Offers',
            'discount_note' => 'Discounts cannot be combined with any existing or newly introduced offer(s).',
            'offer_note' => 'For New Subscribers only. Cashbacks are rewarded in the form of wallet credits.',
        ]);
    }
}
