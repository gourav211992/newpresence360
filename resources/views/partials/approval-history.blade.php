@if((isset($approvalHistory) && count($approvalHistory) > 0) || $revision_number)
    @if($document_status != \App\Helpers\ConstantHelper::DRAFT || in_array('bid-reopened',$approvalHistory?->pluck('approval_type')->toArray()))
    <div class="col-md-{{isset($colspan)?$colspan:4}}">
       <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
          <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
             <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
             <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
            @if($revision_number > intval(request('revisionNumber')) && request()->has('revisionNumber'))
            <select onclick="return false" class="form-select" id="revisionNumber">
             <option value="{{request('revisionNumber')}}">{{request('revisionNumber')}}</option>
             </select>
            @else
             <select class="form-select" id="revisionNumber">
             @for($i=$revision_number; $i >= 0; $i--)
             <option value="{{$i}}" {{request('revisionNumber',$revision_number) == $i ? 'selected' : ''}}>{{$i}}</option>
             @endfor
             </select>
            @endif
             </strong>
          </h5>
          <ul class="timeline ms-50 newdashtimline ">
             @foreach($approvalHistory as $approvalHist)
             <li class="timeline-item">
                <span class="timeline-point timeline-point-indicator"></span>
                <div class="timeline-event">
                   <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                      <h6>{{ucfirst($approvalHist->name ?? $approvalHist?->user?->name ?? 'NA')}}</h6>
                      @if($approvalHist->approval_type == 'approve')
                      <span class="badge rounded-pill badge-light-success">{{ucfirst($approvalHist->approval_type)}}</span>
                      @elseif($approvalHist->approval_type == 'submit')
                      <span class="badge rounded-pill badge-light-primary">{{ucfirst($approvalHist->approval_type)}}</span>
                      @elseif($approvalHist->approval_type == 'reject')
                      <span class="badge rounded-pill badge-light-danger">{{ucfirst($approvalHist->approval_type)}}</span>
                      @elseif($approvalHist->approval_type == 'posted')
                      <span class="badge rounded-pill badge-light-info">{{ucfirst($approvalHist->approval_type)}}</span>
                      @else
                      <span class="badge rounded-pill badge-light-danger">{{ucfirst($approvalHist->approval_type)}}</span>
                      @endif
                   </div>
                    @if($approvalHist->created_at)
                    <h6>
                     {{ \Carbon\Carbon::parse($approvalHist->created_at)->timezone('Asia/Kolkata')->format('d/m/Y | h.iA') }}
                    </h6>
                    @endif
                   @if($approvalHist->remarks)
                   <p>{!! $approvalHist->remarks !!}</p>
                   @endif
                   @if($approvalHist->getDocuments()->isNotEmpty())
                      <p>
                      @foreach($approvalHist->getDocuments() as $getDocument)
                         <a href="{{$approvalHist->getDocumentUrl($getDocument)}}" download>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                               <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                               <polyline points="7 10 12 15 17 10"></polyline>
                               <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                         </a>
                      @endforeach
                      </p>
                   @endif
                </div>
             </li>
             @endforeach
          </ul>
       </div>
    </div>
    @endif
@endif
