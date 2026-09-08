@php
    $initialSubCategory = $initialSubCategory ?? null;
    $hasSubcategorySelect = $hasSubcategorySelect ?? false;
    $lockedCountryId = $lockedCountryId ?? \App\Support\SubscriberLicensedCountry::resolveCountryId($initialSubCategory);
    $lockedCountryName = $lockedCountryName ?? \App\Support\SubscriberLicensedCountry::resolveCountryName($initialSubCategory);
@endphp
<script>
(function () {
    var presetLockedCountry = {
        id: @json($lockedCountryId),
        name: @json($lockedCountryName),
    };

    function getLockedCountryFromSubcategory() {
        var $subcategory = $('#subcategory');
        if ($subcategory.length) {
            var $selected = $subcategory.find('option:selected');

            return {
                id: $selected.data('locked-country-id') || '',
                name: $selected.data('locked-country-name') || '',
            };
        }

        return presetLockedCountry;
    }

    function applySubscriberCountryLock() {
        var $country = $('#country');
        if (!$country.length) {
            return;
        }

        var locked = getLockedCountryFromSubcategory();
        var $hidden = $('#country_locked_hidden');
        var $hint = $('#subscriber-country-lock-hint');

        if (locked.id) {
            if (!$hidden.length) {
                $hidden = $('<input>', {
                    type: 'hidden',
                    id: 'country_locked_hidden',
                    name: 'country',
                });
                $country.after($hidden);
            }

            $hidden.val(String(locked.id));
            $country.val(String(locked.id)).prop('disabled', true);

            if ($hint.length) {
                $hint
                    .text('Country is locked to ' + locked.name + ' for this licensed immigration advisor sub-category.')
                    .show();
            }

            $country.trigger('change');
            return;
        }

        $hidden.remove();
        $country.prop('disabled', false);

        if ($hint.length) {
            $hint.hide().text('');
        }
    }

    $(document).ready(function () {
        @if ($hasSubcategorySelect)
        $('#subcategory').on('change', applySubscriberCountryLock);
        @endif

        applySubscriberCountryLock();
    });

    window.applySubscriberCountryLock = applySubscriberCountryLock;
})();
</script>
