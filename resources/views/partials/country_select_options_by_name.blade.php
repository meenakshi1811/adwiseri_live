@php
    use App\Support\IntlPhoneCountryMap;

    $phoneForPrefill = $phoneForPrefill ?? null;
    $savedCountry = $savedCountry ?? null;
    $selectedName = IntlPhoneCountryMap::selectedCountryName($phoneForPrefill, $savedCountry);
@endphp
@foreach($countries as $country)
    <option value="{{ $country->country_name }}" {{ ($selectedName ?? '') === $country->country_name ? 'selected' : '' }}>{{ $country->country_name }}</option>
@endforeach
