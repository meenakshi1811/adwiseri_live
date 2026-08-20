<?php

namespace App\Http\Middleware;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Http\Request;

class NormalizePhoneInput
{
    public function handle(Request $request, Closure $next)
    {
        $updates = [];

        foreach (PhoneNumber::FIELDS as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $value = $request->input($field);

            if ($value === null || trim((string) $value) === '') {
                continue;
            }

            $normalized = PhoneNumber::normalize((string) $value);

            if ($normalized !== null) {
                $updates[$field] = $normalized;
            }
        }

        if (!empty($updates)) {
            $request->merge($updates);
        }

        return $next($request);
    }
}
