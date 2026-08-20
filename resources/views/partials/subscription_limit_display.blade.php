@if (!empty($display['boosted']))
    <span style="text-decoration:line-through;color:#9ca3af;">{{ $display['base'] }}</span>
    <span style="color:#4C3BB7;font-weight:600;margin-left:6px;">{{ $display['effective'] }}</span>
@else
    {{ $display['effective'] }}
@endif
