<script>
    window.AdwiseriIntlPhoneConfig = {
        validateUrl: @json(route('validate.phone')),
        utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/utils.js',
        countryMap: @json(\App\Support\IntlPhoneCountryMap::isoToCountryId()),
        countryNameMap: @json(\App\Support\IntlPhoneCountryMap::nameToCountryId()),
        countryIdToIso: @json(\App\Support\IntlPhoneCountryMap::countryIdToIso()),
        countryNameToIso: @json(\App\Support\IntlPhoneCountryMap::countryNameToIso())
    };
</script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/intlTelInput.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.0.12/build/js/utils.js"></script>
<script src="{{ asset('web_assets/js/intl-phone.js') }}"></script>
<script src="{{ asset('web_assets/js/form-validation.js') }}"></script>
