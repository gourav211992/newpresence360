@forelse($questions as $key => $question)
    <div class="question-box" data-question-index="{{ $key }}">
        <h4>Question {{ $key + 1 }}
            <a href="javascript:;" data-url="{{ url('recruitment/assessments/remove-question') . '/' . $question->id }}"
                data-request="remove">
                <span data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="Delete"
                    class="float-end text-danger ms-1">
                    <i data-feather="trash-2"></i>
                </span>
            </a>
            @if ($question->image)
                <a href="{{ url($question->image) }}" target="_blank">
                    <span data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" title="Add Image"
                        class="upload-btn-wrapper float-end">
                        <i data-feather='image'></i>
                    </span>
                </a>
            @endif
        </h4>

        <div class="row">
            <div class="col-md-8 mb-sm-0 mb-1">
                <input type="text" class="form-control" placeholder="Title" value="{{ $question->question }}"
                    disabled>
            </div>
            <div class="col-md-4 question-select mb-1">
                <select data-placeholder="Select Question Type" class="select2-icons form-select" id="select2-icons1"
                    disabled>
                    <option value="single choice" data-icon="circle"
                        {{ $question->type == 'single choice' ? 'selected' : '' }}>Single
                        Choice</option>
                    <option value="multiple choice" data-icon="stop-circle"
                        {{ $question->type == 'multiple choice' ? 'selected' : '' }}>
                        Multiple Choice</option>
                    <option value="dropdown" data-icon="chevron-down"
                        {{ $question->type == 'dropdown' ? 'selected' : '' }}>Dropdown
                    </option>
                    <option value="file upload" data-icon="upload"
                        {{ $question->type == 'file upload' ? 'selected' : '' }}>File Upload
                    </option>
                    <option value="image" data-icon="upload" {{ $question->type == 'image' ? 'selected' : '' }}>Image
                        Upload
                    </option>
                    <option value="short answer" data-icon="align-left"
                        {{ $question->type == 'short answer' ? 'selected' : '' }}>Short
                        Answer</option>
                    <option value="rating" data-icon="star" {{ $question->type == 'rating' ? 'selected' : '' }}>Rating
                    </option>
                </select>
            </div>
        </div>
        @if ($question->type == 'single choice')
            @forelse($question->options as $option)
                <div class="mb-1 innergroupanser option-preview-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check ansercheckbox">
                                <input class="form-check-input" type="radio" id="answer4"
                                    {{ $option->is_correct == '1' ? 'checked' : '' }} disabled>
                                <label class="form-check-label" for="answer4">{{ $option->option }}</label>
                            </div>
                        </div>
                        <div class="col-md-2 mt-1 mt-sm-0">
                            <a href="javascript:;"
                                data-url="{{ url('recruitment/assessments/remove-option') . '/' . $option->id }}"
                                data-request="remove">
                                <span class="text-danger cursor-pointer add-removetxt">
                                    <i data-feather="x-circle"></i> Remove
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        @elseif ($question->type == 'multiple choice')
            @forelse($question->options as $option)
                <div class="mb-1 innergroupanser option-preview-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check ansercheckbox">
                                <input class="form-check-input" type="checkbox" id="answer4"
                                    {{ $option->is_correct == '1' ? 'checked' : '' }} disabled>
                                <label class="form-check-label">{{ $option->option }}</label>
                            </div>
                        </div>
                        <div class="col-md-2 mt-1 mt-sm-0">
                            <a href="javascript:;"
                                data-url="{{ url('recruitment/assessments/remove-option') . '/' . $option->id }}"
                                data-request="remove">
                                <span class="text-danger cursor-pointer add-removetxt">
                                    <i data-feather="x-circle"></i> Remove
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        @elseif ($question->type == 'dropdown')
            @forelse($question->options as $option)
                <div class="mb-1 innergroupanser option-preview-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check ansercheckbox">
                                <input class="form-check-input" type="radio" id="answer4"
                                    {{ $option->is_correct == '1' ? 'checked' : '' }} disabled>
                                <label class="form-check-label">{{ $option->option }}</label>
                            </div>
                        </div>
                        <div class="col-md-2 mt-1 mt-sm-0">
                            <a href="javascript:;"
                                data-url="{{ url('recruitment/assessments/remove-option') . '/' . $option->id }}"
                                data-request="remove">
                                <span class="text-danger cursor-pointer add-removetxt">
                                    <i data-feather="x-circle"></i> Remove
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        @elseif ($question->type == 'rating')
            <div class="row">
                <div class="col-md-9">
                    <div class="row mb-1 align-items-center">
                        <div class="col-md-2">
                            <label class="form-label">Score from</label>
                        </div>
                        <div class="col-md-3 pe-sm-0">
                            <input type="number" class="form-control" placeholder="0"
                                value="{{ $question->score_from }}" disabled>
                        </div>
                        <div class="col-md-1 text-center">
                            <p class="mb-0">to</p>
                        </div>
                        <div class="col-md-3 ps-sm-0">
                            <input type="number" class="form-control" placeholder="10"
                                value="{{ $question->score_to }}" disabled>
                        </div>
                    </div>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6">
                    <div class="mb-1">
                        <label class="form-label">Low score label</label>
                        <input type="text" class="form-control" placeholder="Enter"
                            value="{{ $question->low_score }}" disabled>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-1">
                        <label class="form-label">High score lable</label>
                        <input type="text" class="form-control" placeholder="Enter"
                            value="{{ $question->high_score }}" disabled>
                    </div>
                </div>

            </div>
        @elseif ($question->type == 'image')
            @forelse($question->options as $option)
                <div class="mb-1 innergroupanser option-preview-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check ansercheckbox">
                                <input class="form-check-input" type="radio" id="answer4"
                                    {{ $option->is_correct == '1' ? 'checked' : '' }} disabled>
                                <img src="{{ url($option->image) }}" style="height:100px; width:100px;" />
                            </div>
                        </div>
                        <div class="col-md-2 mt-1 mt-sm-0">
                            <a href="javascript:;"
                                data-url="{{ url('recruitment/assessments/remove-option') . '/' . $option->id }}"
                                data-request="remove">
                                <span class="text-danger cursor-pointer add-removetxt">
                                    <i data-feather="x-circle"></i> Remove
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        @endif


        <div>
            <div class="d-flex align-items-center">
                <div class="form-check form-check-primary form-switch">
                    <input type="checkbox" class="form-check-input" id="customSwitch7"
                        {{ $question->is_required == 1 ? 'checked' : '' }} disabled />
                </div>
                <label class="form-check-label" for="customSwitch7">Required</label>
            </div>
            <div class="d-flex align-items-center mt-1 show-dropdown-toggle">
                <div class="form-check form-check-primary form-switch">
                    <input type="checkbox" class="form-check-input" id="customSwitch45"
                        {{ $question->is_dropdown == 1 ? 'checked' : '' }} disabled />
                </div>
                <label class="form-check-label" for="customSwitch45">Show as
                    dropdown</label>
            </div>
        </div>

    </div>
@empty
@endforelse
