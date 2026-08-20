<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomepageSectionSetting extends Model
{
    use HasFactory;

    protected $table = 'homepage_section_settings';

    protected $fillable = [
        'sections',
    ];

    protected $casts = [
        'sections' => 'array',
    ];

    /**
     * @return array<string, string>
     */
    public static function definitions(): array
    {
        return [
            'banner' => 'Banner / Hero',
            'about_highlights' => 'About Highlights',
            'key_features' => 'Key Features',
            'about_us' => 'About Us',
            'price_plans' => 'Price Plans',
            'discounts_offers' => 'Discounts & Offers',
            'why_adwiseri' => 'Why adwiseri?',
            'affiliates' => 'Affiliate Program',
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function defaultVisibility(): array
    {
        $defaults = [];

        foreach (array_keys(self::definitions()) as $key) {
            $defaults[$key] = true;
        }

        return $defaults;
    }

    public static function current(): self
    {
        $setting = static::query()->first();

        if ($setting) {
            return $setting;
        }

        return static::create([
            'sections' => self::defaultVisibility(),
        ]);
    }

    /**
     * @return array<string, bool>
     */
    public function visibilityMap(): array
    {
        $saved = is_array($this->sections) ? $this->sections : [];
        $map = self::defaultVisibility();

        foreach ($map as $key => $defaultVisible) {
            if (array_key_exists($key, $saved)) {
                $map[$key] = self::castVisibility($saved[$key], $defaultVisible);
            }
        }

        return $map;
    }

    public function isVisible(string $key): bool
    {
        return self::castVisibility($this->visibilityMap()[$key] ?? true, true);
    }

    /**
     * @param  mixed  $value
     */
    public static function castVisibility($value, bool $default = true): bool
    {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return (bool) $value;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function syncVisibility(array $input): void
    {
        $visibility = [];

        foreach (array_keys(self::definitions()) as $key) {
            $visibility[$key] = self::castVisibility($input[$key] ?? '0', false);
        }

        $this->sections = $visibility;
        $this->save();
    }
}
