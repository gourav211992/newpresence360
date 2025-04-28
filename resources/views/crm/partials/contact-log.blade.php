@forelse($diaries as $erpDiary)
    <div class="card">
        <div class="card-header border-bottom  p-1">
            <div class="user-details d-flex justify-content-between align-items-center flex-wrap">
                @php
                    $randomColor = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                @endphp
                <div class="me-75" style="width: 38px; height: 38px; border-radius: 50%; background-color: {{ $randomColor }}; color: white; text-align: center; line-height: 38px;">
                    {{ strtoupper(substr($erpDiary->customer_name, 0, 1)) }}
                </div>
                <div class="mail-items">
                    <h5 class="mb-0 mt-50">{{ $erpDiary->customer_name }}</h5>
                    <p class="mb-0">{{ $erpDiary->created_at ? App\Helpers\GeneralHelper::dateFormat($erpDiary->created_at) : ''}} | {{ $erpDiary->created_at ? App\Helpers\GeneralHelper::timeFormat($erpDiary->created_at) : ''}}</p> 
                </div>
            </div> 
        </div>
        <div class="card-body mail-message-wrapper p-1">
            <div class="mail-message">
                <h5 class="card-text fw-bolder">{{ $erpDiary->subject }}</h5>
                <p class="card-text">{!! $erpDiary->description !!}</p> 
            </div>
        </div>
    </div>
@empty
    <div class="card">
        <div class="card-body mail-message-wrapper p-1">
            <div class="mail-message">
                <p class="card-text text-danger text-center"> No record(s) found. </p>
            </div>
        </div>
    </div>
@endforelse