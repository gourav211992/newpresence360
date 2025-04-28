<div class="tab-pane active" id="Requested">
    <div class="table-responsive candidates-tables">
        @include('recruitment.partials.card-header')
        <table
            class="datatables-basic table table-striped myrequesttablecbox loanapplicationlist">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Job Id</th>
                    <th>Job Type</th>
                    <th>Job Title</th>
                    <th>Education</th>
                    <th>Skills</th>
                    <th>Exp.</th>
                    <th>Request By</th>
                    <th>Candidates</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobs as $job)
                <tr>
                    <td>{{ $jobs->firstItem() + $loop->index }}</td>
                    <td class="text-nowrap">{{ $job->created_at ? App\Helpers\CommonHelper::dateFormat($job->created_at) : '' }}</td>
                    <td class="fw-bolder text-dark">{{ $job->job_id }}</td>
                    <td>
                        <a href="{{ route('recruitment.jobs.show',['id' => $job->id]) }}">
                            <span class="badge rounded-pill badge-light-primary badgeborder-radius">
                                {{ ucfirst($job->status) }}
                            </span>
                        </a>
                    </td>
                    <td>{{ $job->job_title_name }}</td>
                    <td>{{ $job->education_name }}</td>
                    <td>
                        @php
                            $skills = $job->jobSkills;
                        @endphp
                        @foreach ($skills->take(2) as $skill)
                            <span class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                {{ $skill->name }}
                            </span>
                        @endforeach

                        @if ($skills->count() > 2)
                            <a href="#" class="skilnum text-primary" data-bs-toggle="modal" data-bs-target="#skillModal" data-skills='@json($skills->pluck("name"))'>
                                <span class="skilnum">+{{ $skills->count() - 2 }}</span>
                            </a>
                        @endif
                    </td>
                    <td>{{ $job->work_exp_min }} - {{ $job->work_exp_max }} year</td>
                    <td>
                        <div class="d-flex flex-row">
                            <div style="background-color: #ddb6ff; color: #6b12b7; line-height: 40px; width: 40px; height: 40px; border-radius: 50%; position: relative; font-size: 1rem; text-align: center; margin-right: 5px; font-weight: 600;">                                                                    
                                {{ strtoupper(substr($job->creator_name, 0, 1)) }}
                            </div>
                            <div class="my-auto">
                                <h6 class="mb-0 fw-bolder text-dark hr-dashemplname">{{ $job->creator_name }}</h6>
                            </div>
                        </div>
                    </td>
                    <td>{{ $job->assigned_candidates_count }}</td>
                    <td class="tableactionnew">
                        <div class="dropdown">
                            <button type="button"
                                class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="{{ route('recruitment.jobs.show',['id' => $job->id]) }}">
                                    <i data-feather="eye" class="me-50"></i>
                                    <span>View Detail</span>
                                </a> 
                                @if($job->status == 'open' && $user->id == $job->created_by)
                                    <a class="dropdown-item" href="{{ route('recruitment.jobs.candidates',['id' => $job->id]) }}">
                                        <i data-feather="users" class="me-50"></i>
                                        <span>Assign Candidates</span>
                                    </a>

                                    <a class="dropdown-item" href="{{ route('recruitment.jobs.edit',['id' => $job->id]) }}">
                                        <i data-feather="edit-3" class="me-50"></i>
                                        <span>Edit</span>
                                    </a>
                                @endif
                                <a class="dropdown-item" href="#">
                                    <i data-feather="trash-2" class="me-50"></i>
                                    <span>Closed</span>
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td class="text-danger text-center" colspan="12">No record(s) found.
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>
    {{-- Pagination --}}
    {{ $jobs->appends(request()->input())->links('recruitment.partials.pagination') }}
    {{-- Pagination End --}}
</div>