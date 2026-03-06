<table>
    @if(!empty($headers))
    <thead>
        <tr>
            @foreach($headers as $header)
                <td style="{{ $loop->last ? 'text-align: right;' : '' }}">
                    {{ $header }}
                </td>
            @endforeach
        </tr>
    </thead>
    @endif
    
    <tbody>
        @foreach($items as $row)
        <tr>
            @foreach($row as $cell)
                <td style="{{ $loop->last ? 'text-align: right; font-weight: 600; color: #333;' : 'color: #6d7178;' }}">
                    @if(is_array($cell))
                        {{ $cell['value'] ?? '' }}
                        
                        @if(!empty($cell['sub_value']))
                            <br><small style="color: #9ca3af;">{{ $cell['sub_value'] }}</small>
                        @endif
                    @else
                        {{ $cell }}
                    @endif
                </td>
            @endforeach
        </tr>
        @endforeach

        @if(!empty($totals))
            @php
                $colspan = count($headers) > 1 ? count($headers) - 1 : 1;
            @endphp
            
            @foreach($totals as $total)
            <tr>
                <td colspan="{{ $colspan }}" style="text-align: right; color: #6d7178; {{ $loop->first ? 'border-top: 2px dashed #eceeff;' : '' }}">
                    <strong>{{ $total['label'] }}</strong>
                </td>
                
                <td style="text-align: right; {{ $loop->first ? 'border-top: 2px dashed #eceeff;' : '' }}">
                    @if($total['is_discount'] ?? false)
                        <span style="color: #e74c3c;">- {{ $total['value'] }}</span>
                    @elseif($total['is_highlighted'] ?? false)
                        <strong><span style="font-size: 16px; color: #7267ef;">{{ $total['value'] }}</span></strong>
                    @else
                        <span style="font-weight: 600; color: #333;">{{ $total['value'] }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        @endif
    </tbody>
</table>