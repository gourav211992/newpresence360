<div class="card">
    <div class="card-body emplodetainfocard pb-0">
        <div class="employee-box border-0 text-center">
            <div
                style="background-color: #ddb6ff;color: #6b12b7;line-height: 63px;width: 70px;height: 70px;border-radius: 5px;position: relative;font-size: xx-large;text-align: center;margin-left: 190px;margin-bottom: 15px;font-weight: bold;">
                {{ strtoupper(substr($candidate->name, 0, 1)) }}
            </div>
            <h2>{{ $candidate->name }}</h2>
            <span>{{ @$candidate->jobDetail->job_title_name }}</span>

            {{-- Shar feedback --}}
            @if($page == 'job-view' && $status == App\Helpers\CommonHelper::SCHEDULED && !$hasGivenFeedback)
                <br />
                <button class="btn btn-outline-primary btn-sm my-1" onclick="setFeedbackModal('{{ $interviewDetail->id }}')"><i
                        data-feather="star"></i> Share Feedback</button>
            @endif
            @if($page == 'job-view-hr' && $status == App\Helpers\CommonHelper::SCHEDULED)
                <br />
                <button class="btn btn-outline-primary btn-sm my-1" onclick="setHrFeedbackModal('{{ $interviewDetail->id }}')"><i
                        data-feather="star"></i> Share Feedback</button>
            @endif

            <div class="row justify-content-center">
                <div class="col-md-5 col-6 pe-0">
                    <div class="empl-info">
                        <div class="iconcode">
                            <i data-feather="calendar" class="text-primary-new"></i>
                        </div>
                        <div class="emp-infpdet">
                            <h3>{{ isset($interviewDetail->date_time) ?
                            App\Helpers\CommonHelper::dateFormat($interviewDetail->date_time) : '' }}</h3>
                            <h4>Interview Date</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-6">
                    <div class="empl-info">
                        <div class="iconcode">
                            <i data-feather="clock" class="text-primary-new"></i>
                        </div>
                        <div class="emp-infpdet">
                            <h3>{{ isset($interviewDetail->date_time) ?
                            App\Helpers\CommonHelper::timeFormat($interviewDetail->date_time) : '' }}</h3>
                            <h4>Interview Time</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="step-custhomapp bg-light mt-3 mb-0">
                <ul class="nav nav-tabs mb-0 mt-25 custapploannav customrapplicationstatus candstatubar" role="tablist">
                    @forelse($interviewRounds as $round)
                        @php
                            $jobInterviewStatus = isset($round->jobInterview->status) ? $round->jobInterview->status : null;
                            $className = '';
                            if($jobInterviewStatus == 'selected'){
                                $className = 'statusactive';
                            }
                        @endphp

                        <li class="nav-item">
                            <p class="{{ $className }}"><i data-feather="check"></i></p>
                            <a class="nav-link active">
                                {{ $round->name }}
                            </a>
                        </li>
                    @empty
                    @endforelse
                </ul>
            </div>

        </div>

        <div class="empl-detail-info">
            <h3>DETAILS</h3>
            <div class="download-resume">
                <a href="{{ url('/').'/'.$candidate->resume_path }}" target="_blank"><img
                    src="{{ asset('img/resume.png') }}" /></a><br />
                <a href="{{ $interviewDetail->meeting_link }}" target="_blank" class="mt-1 d-block"><img src="{{ asset('img/meet.png') }}" /></a>
            </div>
            <div class="row">
                <div class="col-6">
                    <p>Email</p>
                    <h5>{{ $candidate->email }}</h5>
                </div>
                <div class="col-6">
                    <p>Meeting Link</p>
                    <h5><a href="#">{{ $interviewDetail->meeting_link }}</a></h5>
                </div>
                <div class="col-6">
                    <p>Phone No</p>
                    <h5>{{ $candidate->mobile_no }}</h5>
                </div>
                <div class="col-6">
                    <p>Location</p>
                    <h5>{{ $candidate->location_name }}</h5>
                </div>
                <div class="col-6">
                    <p>Applied Date</p>
                    <h5>{{ isset($candidate->assignedJob->created_at) ?
                                App\Helpers\CommonHelper::dateFormat($candidate->assignedJob->created_at) : '' }}</h5>
                </div>
                <div class="col-6">
                    <p>Work Exp.</p>
                    <h5>{{ $candidate->work_exp }}</h5>
                </div>
                <div class="col-6">
                    <p>Skill Set</p>
                    <h5> 
                        @forelse($candidate->candidateSkills as $skill)
                        <span class="badge rounded-pill badge-light-secondary badgeborder-radius font-small-2">{{
                            $skill->name }}</span>
                        @empty
                        @endforelse
                    </h5>
                </div>
                <div class="col-md-12 mt-2 text-center">
                    @if($page == 'job-view-hr' && $status == App\Helpers\CommonHelper::SELECTED && $pendingRoundsCount > 0)
                        <button class="btn btn-primary btn-primary-new  me-1  mb-50 mb-sm-0" onclick="setScheduleInterviewModal('interview-scheduled','{{ $candidate->id }}')">
                            <i data-feather="calendar"></i>  Schedule Next Interview 
                        </button>
                    @endif
                </div>


            </div>
        </div>
        @if(count($feedbackLog) > 0)
        <div class="  mt-2">
            <h4 class="text-dark">Feedback</h4>
            <div
                class="mt-2 bg-light step-custhomapp p-1 customerapptimelines customerapptimelinesapprovalpo expviewdetailsher">
                <ul class="timeline ms-50 newdashtimline ">
                    @forelse($feedbackLog as $log)
                        <li class="timeline-item">
                            @php
                                if($log->rating == '1'){
                                    $className = 'badge-light-danger';
                                    $timeLineClassName = 'timeline-point-danger';
                                }
                                elseif($log->rating == '2'){
                                    $className = 'badge-light-warning';
                                    $timeLineClassName = 'timeline-point-warning';
                                }elseif($log->rating == '3' || $log->rating == '4'){
                                    $className = 'badge-light-primary';
                                    $timeLineClassName = 'timeline-point-primary';
                                }else{
                                    $className = 'badge-light-success';
                                    $timeLineClassName = 'timeline-point-success';
                                }
                            @endphp
                            <span class="timeline-point {{ $timeLineClassName }} timeline-point-indicator"></span>
                            <div class="timeline-event">
                                <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                    <h6>{{ $log->panel_name }}</h6>
                                    <span class="badge rounded-pill {{ $className }}">{{ $log->round_name }}</span>
                                </div>
                                <h5>({{ $log->created_at ? $log->created_at->diffForHumans() : '' }})</h5>
                                <p class="mb-50">
                                    @if($log->attachment_path)
                                        <a href="{{ url('/').'/'.$log->attachment_path }}"><i data-feather="download"></i></a>
                                    @endif
                                    {{ $log->remarks }}</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="text-dark mb-50"><strong>Rating:</strong><br />
                                            @php
                                                $rating = $log->rating ?? 0; // Ensure rating is a number, default to 0 if null
                                            @endphp

                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $rating)
                                                    <i data-feather="star" class="text-primary"></i> <!-- Full star -->
                                                @else
                                                    <i data-feather="star" class="text-muted"></i> <!-- Empty star -->
                                                @endif
                                            @endfor
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-dark mb-50"><strong>Behaviour:</strong><br />
                                            @php
                                                $behaviorRating = $log->behavior ?? 0; // Ensure behaviorRating is a number, default to 0 if null
                                            @endphp

                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $behaviorRating)
                                                    <i data-feather="star" class="text-primary"></i> <!-- Full star -->
                                                @else
                                                    <i data-feather="star" class="text-muted"></i> <!-- Empty star -->
                                                @endif
                                            @endfor
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="text-dark mb-50"><strong>Skills:</strong><br />
                                            @php
                                                $skillsRating = $log->behavior ?? 0; // Ensure skillsRating is a number, default to 0 if null
                                            @endphp

                                            @for ($i = 1; $i <= 5; $i++)
                                                @if ($i <= $skillsRating)
                                                    <i data-feather="star" class="text-primary"></i> <!-- Full star -->
                                                @else
                                                    <i data-feather="star" class="text-muted"></i> <!-- Empty star -->
                                                @endif
                                            @endfor
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                    @endforelse
                    
                </ul>
            </div>
        </div>
        @endif

    </div>
</div>