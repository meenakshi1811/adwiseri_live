@php
    $filterGroups = $filterGroups ?? [];
    $tableId = $tableId ?? 'clientTable';
    $toolbarLabel = $toolbarLabel ?? 'Filters';
    $longestFilterLabelChars = 0;
    foreach ($filterGroups as $group) {
        $longestFilterLabelChars = max($longestFilterLabelChars, mb_strlen((string) ($group['label'] ?? '')));
    }
    $filterSelectWidthCh = max($longestFilterLabelChars + 3, 12);
@endphp

@if(count($filterGroups) > 1)
<link rel="stylesheet" href="{{ asset('web_assets/css/table-filter-toolbar.css') }}">
<div class="table-filter-toolbar multi-table-filter-toolbar"
     data-multi-table-filter-toolbar
     data-table-id="{{ $tableId }}">
    <div class="multi-table-filter-toolbar__header">
        <span class="multi-table-filter-toolbar__label">{{ $toolbarLabel }}:</span>
        <select class="multi-table-filter-toolbar__select form-select form-select-sm"
                data-filter-group-select
                data-longest-label-ch="{{ $filterSelectWidthCh }}"
                style="width: {{ $filterSelectWidthCh }}ch;"
                aria-label="Select filter type">
            @foreach($filterGroups as $group)
                <option value="{{ $group['key'] }}">{{ $group['label'] }}</option>
            @endforeach
        </select>
    </div>

    @foreach($filterGroups as $index => $group)
        @php
            $groupKey = $group['key'];
            $groupItems = $group['items'] ?? [];
            $groupTotal = (int) ($group['totalCount'] ?? array_sum(array_column($groupItems, 'count')));
            $matchMode = $group['match'] ?? 'exact';
            $rowAttribute = $group['rowAttribute'] ?? ('data-' . $groupKey);
        @endphp
        <div class="multi-table-filter-toolbar__panel {{ $index === 0 ? 'is-active' : '' }}"
             data-filter-group-panel="{{ $groupKey }}"
             @if($index !== 0) hidden @endif>
            <div class="table-filter-toolbar__items">
                <button type="button"
                        class="table-filter-btn table-filter-btn--tone-0 is-active"
                        data-filter-group="{{ $groupKey }}"
                        data-filter-value=""
                        data-row-attribute="{{ $rowAttribute }}"
                        data-match-mode="{{ $matchMode }}"
                        aria-pressed="true">
                    All
                    @if($groupTotal > 0)
                        <span class="table-filter-btn__count">{{ $groupTotal }}</span>
                    @endif
                </button>
                @foreach($groupItems as $itemIndex => $item)
                    <button type="button"
                            class="table-filter-btn table-filter-btn--tone-{{ ($itemIndex % 8) + 1 }}"
                            data-filter-group="{{ $groupKey }}"
                            data-filter-value="{{ $item['key'] }}"
                            data-row-attribute="{{ $rowAttribute }}"
                            data-match-mode="{{ $matchMode }}"
                            aria-pressed="false">
                        {{ $item['label'] }}
                        <span class="table-filter-btn__count">{{ $item['count'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@push('scripts')
@include('partials.multi_table_filter_scripts', [
    'tableId' => $tableId,
    'filterGroups' => $filterGroups,
])
@endpush
@endif
