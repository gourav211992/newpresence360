<div class="card-body notesdiaryscroll bg-light">
    <div class="actual-databudgetinfo mt-1">
        @forelse($erpDiaries as $erpDiary)
            <div class="card">
                <div class="card-header border-bottom  p-1">
                    <div
                        class="user-details d-flex justify-content-between align-items-center flex-wrap">
                        @php
                            $randomColor = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                        @endphp
                        <div class="me-75" style="width: 38px; height: 38px; border-radius: 50%; background-color: {{ $randomColor }}; color: white; text-align: center; line-height: 38px;">
                            {{ strtoupper(substr($erpDiary->customer_name, 0, 1)) }}
                        </div>
                        <div class="mail-items">
                            <h5 class="mb-0 mt-50">{{ $erpDiary->customer_name }}</h5>
                            <p class="mb-0">{{ $erpDiary->contact_person }} | {{ $erpDiary->email }} | {{ isset($erpDiary->customer->phone) ? $erpDiary->customer->phone : '-' }}</p>
                        </div>
                    </div>
                    <div class="mail-meta-item d-flex align-items-center">
                        <span
                            class="badge font-small-2 fw-bold rounded-pill {{ $erpDiary->customer_type == 'New' ? 'badge-light-success' : 'badge-light-warning' }}  badgeborder-radius">{{ $erpDiary->customer_type }}</span>
                    </div>
                </div>
                <div class="card-body mail-message-wrapper p-1">

                    <div class="mail-message">
                        <h5 class="card-text fw-bold">{{ $erpDiary->subject }}</h5>
                        <p class="card-text">{!! $erpDiary->description !!}</p>
                        <div class="row">
                            <div class="col-md-8">
                                @if(count($erpDiary->attachments) > 0)
                                    @foreach($erpDiary->attachments as $attachment)
                                        @if($attachment->document_path)
                                            @php
                                                $extension = App\Helpers\GeneralHelper::checkFileExtension($attachment->document_path);
                                            @endphp
                                            <a href="{{ url('/').'/'.$attachment->document_path }}" target="_blank">
                                                @if($extension == 'image')
                                                    <img src="{{ url('/').'/'.$attachment->document_path }}" class="me-25" alt="image" height="18">
                                                @endif
                                                <small class="text-muted fw-bolder">{{ basename($attachment->document_path) }}</small>
                                            </a>
                                            <br>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-md-4">
                                <div class="row">
                                <div class="col-md-12">
                                    <div>  
                                        <label class="form-label">Created on: </label>
                                        <span>{{ $erpDiary->created_at ? App\Helpers\GeneralHelper::dateFormat($erpDiary->created_at) : ''}}</span>
                                    </div>
                                </div>
                                <div>  
                                    <label class="form-label">Created by: </label>
                                    @if($erpDiary->created_by_type == 'employee')
                                        <span>{{ isset($erpDiary->createdByEmployee->name) ? $erpDiary->createdByEmployee->name : '' }}</span>
                                    @elseif($erpDiary->created_by_type == 'user')
                                        <span>{{ isset($erpDiary->createdByUser->name) ? $erpDiary->createdByUser->name : '' }}</span>
                                    @endif
                                </div>
                            </div>
                            </div>
                        </div>
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
    </div>
</div>

<div class="d-flex justify-content-end mx-1 mt-50">
    {{-- Pagination --}}
    {{ $erpDiaries->appends(request()->input())->links('crm.partials.pagination') }}
    {{-- Pagination End --}}
    
</div>