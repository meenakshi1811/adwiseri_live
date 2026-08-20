@php
    $lineItems = $invoice->lineItems();
    $lineItemPrefix = $lineItemPrefix ?? 'Professional Fees';
@endphp

@foreach ($lineItems as $item)
    <tr>
        <td class="p-1 desc-col">
            @if(!empty($lineItemPrefix))
                {{ $lineItemPrefix }} ({{ $item->detail }})
            @else
                {{ $item->detail }}
            @endif
        </td>
        <td class="p-1 amount-col">{{ number_format((float) $item->amount, 2) }}</td>
    </tr>
@endforeach
