<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class FormFieldRules
{
    public static function name(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:3',
            'max:255',
            'regex:/^[\pL\s\'\-\.]+$/u',
        ];
    }

    public static function email(bool $required = true, ?string $uniqueTable = null, $ignoreId = null): array
    {
        $rules = [
            $required ? 'required' : 'nullable',
            'email:rfc,dns',
            'max:255',
        ];

        if ($uniqueTable) {
            $unique = Rule::unique($uniqueTable);
            if ($ignoreId !== null) {
                $unique->ignore($ignoreId);
            }
            $rules[] = $unique;
        }

        return $rules;
    }

    public static function phone(bool $required = true, ?string $uniqueTable = null, $ignoreId = null): array
    {
        $rules = [
            $required ? 'required' : 'nullable',
            'phone_intl',
        ];

        if ($uniqueTable) {
            $unique = Rule::unique($uniqueTable);
            if ($ignoreId !== null) {
                $unique->ignore($ignoreId);
            }
            $rules[] = $unique;
        }

        return $rules;
    }

    public static function postcode(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:3',
            'max:10',
            'regex:/^[A-Za-z0-9\s\-]+$/',
        ];
    }

    public static function passport(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:6',
            'max:14',
            'regex:/^[A-Z0-9]+$/',
        ];
    }

    public static function address(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'string',
            'min:3',
            'max:1000',
        ];
    }
}
