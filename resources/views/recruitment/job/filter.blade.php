<div class="modal modal-slide-in fade filterpopuplabel" id="filter">
    <div class="modal-dialog sidebar-sm">
        <form class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1">
                <div class="mb-1">
                    <label class="form-label" for="fp-range">Select Date Range</label>
                    <input type="text" id="fp-range" class="form-control flatpickr-range"
                        placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range" value="{{ request('date_range') }}" />
                </div>

                <div class="mb-1">
                    <label class="form-label">Select Job Title</label>
                    <select class="form-select select2" name="job_title">
                        <option value="" {{ request('job_title') == '' ? 'selected' : '' }}>Select</option>
                        @forelse($jobTitles as $jobTitle)
                            <option value="{{ $jobTitle->id }}"
                                {{ request('job_title') == $jobTitle->id ? 'selected' : '' }}>{{ $jobTitle->title }}
                            </option>
                        @empty
                        @endforelse
                    </select>
                </div>


                <div class="mb-1">
                    <label class="form-label">Skills</label>
                    <select class="form-select select2" name="skill">
                        <option value="" {{ request('skill') == '' ? 'selected' : '' }}>Select</option>
                        @forelse($skillsData as $skill)
                            <option value="{{ $skill->id }}" {{ request('skill') == $skill->id ? 'selected' : '' }}>
                                {{ $skill->name }}</option>
                        @empty
                        @endforelse
                    </select>
                </div>

                <div class="mb-1">
                    <label class="form-label">Status</label>
                    <select class="form-select select2" name="status">
                        <option value="" {{ request('status') == '' ? 'selected' : '' }}>Select</option>
                        @forelse($status as $value)
                            <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                                {{ ucwords($value) }}
                            </option>
                        @empty
                        @endforelse
                    </select>
                </div>
            </div>
            <div class="modal-footer justify-content-start">
                <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                <a href="{{ route('recruitment.jobs') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
