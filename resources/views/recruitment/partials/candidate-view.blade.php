<div class="card">
    <div class="card-body emplodetainfocard pb-0">
        <div class="employee-box text-center">
            {{-- <img src="{{ asset('app-assets/images/avatars/10.png') }}" class="empyee-profimage mt-0" /> --}}
            <div style="background-color: #ddb6ff;color: #6b12b7;line-height: 63px;width: 70px;height: 70px;border-radius: 5px;position: relative;font-size: xx-large;text-align: center;margin-left: 190px;margin-bottom: 15px;font-weight: bold;">                                                                    
                {{ strtoupper(substr($candidate->name, 0, 1)) }}
            </div>
            <h2>{{ $candidate->name }}</h2>
            <span>{{ @$candidate->jobDetail->job_title_name }}</span>

            <div class="row justify-content-center">
                <div class="col-md-5 col-6 pe-0">
                    <div class="empl-info">
                        <div class="iconcode">
                            <i data-feather="calendar" class="text-primary-new"></i>
                        </div>
                        <div class="emp-infpdet">
                            <h3>{{ isset($candidate->assignedJob->created_at) ?
                                App\Helpers\CommonHelper::dateFormat($candidate->assignedJob->created_at) : '' }}</h3>
                            <h4>Applied Date</h4>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 col-6">
                    <div class="empl-info">
                        <div class="iconcode">
                            <i data-feather="briefcase" class="text-primary-new"></i>
                        </div>
                        <div class="emp-infpdet">
                            <h3>{{ $candidate->work_exp }}</h3>
                            <h4>Exp.</h4>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="empl-detail-info">
            <h3>DETAILS</h3>
            <a href="{{ url('/').'/'.$candidate->resume_path }}" class="text-danger download-resume"><img
                    src="{{ asset('img/resume.png') }}" /></a>
            <div class="row">
                <div class="col-12">
                    <p>Email</p>
                    <h5>{{ $candidate->email }}</h5>
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
                    <p>Skill Set</p>
                    <h5>
                        @forelse($candidate->candidateSkills as $skill)
                        <span class="badge rounded-pill badge-light-secondary badgeborder-radius font-small-2">{{
                            $skill->name }}</span>
                        @empty
                        @endforelse
                </div>

                <div class="col-md-12 mt-2 text-center">
                    @if($page == 'job-view-hr')
                        @if(in_array($candidate->assignedJob->status, ['assigned','onhold']))
                            <button class="btn btn-primary btn-primary-new  me-1  mb-50 mb-sm-0" onclick="setStatusModal('qualified','{{ $candidate->id }}')"><i
                                    data-feather="check-circle"></i> Qualified </button>

                            <button class="btn btn-outline-primary me-1  mb-50 mb-sm-0" onclick="setStatusModal('not-qualified','{{ $candidate->id }}')">
                                <i data-feather="x-circle"></i> Not Qualified</button>
                        
                            @if($candidate->assignedJob->status !== 'onhold')
                                <div class="mt-1">
                                    <button class="btn btn-warning btn-sm  mb-50 mb-sm-0" onclick="setStatusModal('onhold','{{ $candidate->id }}')">
                                        <i data-feather="alert-circle"></i> On
                                        Hold</button>
                                </div>
                            @endif
                        @elseif($candidate->assignedJob->status == 'qualified')
                            <button class="btn btn-primary btn-primary-new  me-1  mb-50 mb-sm-0" onclick="setScheduleInterviewModal('interview-scheduled','{{ $candidate->id }}')">
                                <i data-feather="calendar"></i>  Schedule Interview 
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>