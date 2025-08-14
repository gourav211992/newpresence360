{{-- @foreach ($femifishedItems as $key => $semiFinishedGoods)
    @include('pwo.partials.semi-finished-row', ['node' => $semiFinishedGoods['semi_finished_goods']['fg']])
@endforeach --}}
@foreach ($femifishedItems as $key => $semiFinishedGoods)
    @php
        $fgNode = $semiFinishedGoods['semi_finished_goods']['fg'];
        $fgSoId = $fgNode['so_id'] ?? null;
    @endphp
    @include('pwo.partials.semi-finished-row', ['node' => $fgNode, 'parentSoId' => $fgSoId])
@endforeach