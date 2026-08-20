@php
    $filterGroups = $filterGroups ?? null;
    $filterItems = $filterItems ?? [];
    $tableId = $tableId ?? 'clientTable';
    $filterAttribute = $filterAttribute ?? 'data-filter-value';
    $toolbarTitle = $toolbarTitle ?? 'Quick filters';
    $toolbarLabel = $toolbarLabel ?? 'Filters';
    $showAllButton = $showAllButton ?? true;
    $totalCount = (int) ($totalCount ?? array_sum(array_column($filterItems, 'count')));
    $useMultiFilter = is_array($filterGroups) && count($filterGroups) > 1;
    $visibleFilterCount = count($filterItems) + ($showAllButton ? 1 : 0);
    $inlineToolbar = !$useMultiFilter && $visibleFilterCount > 0 && $visibleFilterCount <= 4;
@endphp

@if($useMultiFilter)
    @include('partials.multi_table_filter_toolbar', [
        'filterGroups' => $filterGroups,
        'tableId' => $tableId,
        'toolbarLabel' => $toolbarLabel,
    ])
@elseif(count($filterItems) > 0)
<link rel="stylesheet" href="{{ asset('web_assets/css/table-filter-toolbar.css') }}">
<div class="table-filter-toolbar{{ $inlineToolbar ? ' table-filter-toolbar--inline' : '' }}"
     data-table-filter-toolbar
     data-table-id="{{ $tableId }}"
     data-filter-attr="{{ $filterAttribute }}">
    @if(!empty($toolbarTitle))
        <p class="table-filter-toolbar__title">{{ $toolbarTitle }}</p>
    @endif
    <div class="table-filter-toolbar__items">
        @if($showAllButton)
            <button type="button"
                    class="table-filter-btn table-filter-btn--tone-0 is-active"
                    data-filter-value=""
                    aria-pressed="true">
                All <span class="table-filter-btn__count">{{ $totalCount }}</span>
            </button>
        @endif
        @foreach($filterItems as $index => $item)
            <button type="button"
                    class="table-filter-btn table-filter-btn--tone-{{ ($index % 8) + 1 }}"
                    data-filter-value="{{ $item['key'] }}"
                    aria-pressed="false">
                {{ $item['label'] }} <span class="table-filter-btn__count">{{ $item['count'] }}</span>
            </button>
        @endforeach
    </div>
</div>
@push('scripts')
@include('partials.table_filter_scripts', ['tableId' => $tableId, 'filterAttribute' => $filterAttribute])
@endpush
@endif
