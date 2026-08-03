{{--
    Shared by the on-screen page, the printable page, AND the PDF template --
    one markup, so all three always look identical. Styled entirely with
    inline styles (no Bootstrap classes) on purpose: dompdf (used for the
    PDF) can't render Bootstrap's grid/flexbox, so plain table + inline
    style is the one layout that renders the same everywhere.

    Laid out as two side-by-side label:value columns, matching the old
    paper form, instead of one long list -- each label sits directly next
    to its own value so nothing reads as cramped or run-together.
--}}
@php
    $left = [
        'Owner Name' => $summaryBlock['Owner Name'] ?? '',
        'Plot Description' => $summaryBlock['Plot Description'] ?? '',
        'Advance Amount' => $summaryBlock['Advance Amount'] ?? '',
        'Due Amount' => $summaryBlock['Due Amount'] ?? '',
        'Cancel Amount' => $summaryBlock['Cancel Amount'] ?? '',
    ];
    $right = [
        'Status' => $summaryBlock['Status'] ?? '',
        'Booking Status' => $summaryBlock['Booking Status'] ?? '',
        'Plot Price' => $summaryBlock['Plot Price'] ?? '',
        'Installment Amount' => $summaryBlock['Installment Amount'] ?? '',
        'Received Amount' => $summaryBlock['Received Amount'] ?? '',
        'Date & Time' => $summaryBlock['Date & Time'] ?? '',
    ];
    $rowCount = max(count($left), count($right));
    $leftKeys = array_pad(array_keys($left), $rowCount, null);
    $rightKeys = array_pad(array_keys($right), $rowCount, null);

    $labelStyle = 'border:1px solid #d1d5db; padding:8px 12px; background:#f3f4f6; font-weight:bold; width:20%; white-space:nowrap; vertical-align:top;';
    $valueStyle = 'border:1px solid #d1d5db; padding:8px 12px; width:30%; vertical-align:top;';
@endphp
<table style="width:100%; border-collapse:collapse; margin-bottom:24px;">
    <tbody>
        @for ($i = 0; $i < $rowCount; $i++)
            <tr>
                <td style="{{ $labelStyle }}">{{ $leftKeys[$i] }}</td>
                <td style="{{ $valueStyle }}">{{ $leftKeys[$i] ? $left[$leftKeys[$i]] : '' }}</td>
                <td style="{{ $labelStyle }}">{{ $rightKeys[$i] }}</td>
                <td style="{{ $valueStyle }}">{{ $rightKeys[$i] ? $right[$rightKeys[$i]] : '' }}</td>
            </tr>
        @endfor
    </tbody>
</table>
