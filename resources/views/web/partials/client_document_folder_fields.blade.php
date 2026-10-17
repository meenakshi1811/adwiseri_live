@php
    $documentFolders = $documentFolders ?? app(\App\Services\CountryCategorySettingsService::class)->getDocumentFolders();
    $selectedFolder = old('doc_folder', $selectedDocFolder ?? '');
    $selectedType = old('doc_type', $selectedDocType ?? '');
    $folderFieldId = $folderFieldId ?? 'doc_folder';
    $typeFieldId = $typeFieldId ?? 'doc_type';
@endphp

<div class="col-md-4 p-1">
    <label>Folder / Section</label>
</div>
<div class="col-md-8 p-1">
    <select name="doc_folder" id="{{ $folderFieldId }}" class="form-control form-select @error('doc_folder') is-invalid @enderror">
        <option value="">Select section (optional)</option>
        @foreach($documentFolders as $folder => $types)
            <option value="{{ $folder }}" {{ $selectedFolder === $folder ? 'selected' : '' }}>{{ $folder }}</option>
        @endforeach
    </select>
    @error('doc_folder')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
    <small class="text-muted">Choose a section to filter document types, or leave blank to auto-assign from type.</small>
</div>

<div class="col-md-4 p-1">
    <label>Document Type<span class="text-danger" style="font-size: 18px;">*</span></label>
</div>
<div class="col-md-8 p-1">
    <select name="doc_type" id="{{ $typeFieldId }}" class="form-control form-select @error('doc_type') is-invalid @enderror" required
        data-doc-folders='@json($documentFolders)' data-selected-type="{{ $selectedType }}">
        <option value="">Select section or document type</option>
    </select>
    @error('doc_type')
        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
    @enderror
</div>

@once
@push('scripts')
<script>
    (function ($) {
        function populateClientDocTypes($folderSelect, $typeSelect, selectedType) {
            if (!$typeSelect.length) {
                return;
            }

            var folderMap = {};
            try {
                folderMap = JSON.parse($typeSelect.attr('data-doc-folders') || '{}');
            } catch (e) {
                folderMap = {};
            }

            var folder = ($folderSelect.val() || '').trim();
            var types = folder !== '' ? (folderMap[folder] || []) : [];
            var allTypes = [];

            if (types.length === 0) {
                Object.keys(folderMap).forEach(function (key) {
                    (folderMap[key] || []).forEach(function (type) {
                        if (allTypes.indexOf(type) === -1) {
                            allTypes.push(type);
                        }
                    });
                });
                types = allTypes;
            }

            $typeSelect.empty();
            $typeSelect.append($('<option>', { value: '', text: types.length ? 'Select Document Type' : 'Select section first' }));

            types.forEach(function (type) {
                var $option = $('<option>', { value: type, text: type });
                if (selectedType && selectedType === type) {
                    $option.prop('selected', true);
                }
                $typeSelect.append($option);
            });
        }

        $(document).ready(function () {
            $('[data-doc-folders]').each(function () {
                var $typeSelect = $(this);
                var $form = $typeSelect.closest('form');
                var $folderSelect = $form.find('[name="doc_folder"]');

                var selectedType = ($typeSelect.attr('data-selected-type') || '').trim();
                populateClientDocTypes($folderSelect, $typeSelect, selectedType);

                $folderSelect.on('change', function () {
                    populateClientDocTypes($folderSelect, $typeSelect, '');
                });
            });
        });
    })(jQuery);
</script>
@endpush
@endonce
