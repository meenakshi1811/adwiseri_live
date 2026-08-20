@php
    use App\Support\IntlPhoneCountryMap;

    $phoneForPrefill = $phoneForPrefill ?? null;
    $savedCountry = $savedCountry ?? null;
    $savedIsCountryName = $savedIsCountryName ?? false;
    $selectedCountryId = IntlPhoneCountryMap::selectedCountryId($phoneForPrefill, $savedCountry, $savedIsCountryName);
@endphp
@foreach($countries as $country)
    <option value="{{ $country->id }}" {{ (int) ($selectedCountryId ?? 0) === (int) $country->id ? 'selected' : '' }}>{{ $country->country_name }}</option>
@endforeach
