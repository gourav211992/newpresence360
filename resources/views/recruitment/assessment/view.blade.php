@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content" style="padding-top: 65px !important">
        <div class="content-overlay"></div>
        <!-- <div class="header-navbar-shadow"></div> -->
        <div class="content-wrapper container-xxl p-0">

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <form>
                        <div class="text-sm-end mb-50 mb-sm-1">
                            <a href="{{ route('recruitment.assessments') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                <i data-feather="arrow-left-circle"></i> Back</a>
                            <button class="btn btn-primary btn-sm mb-50 mb-sm-0"> <i data-feather="check-circle"></i>
                                Submit</button>
                        </div>

                        <div class="success-boxmain preview-boxmain mb-4">

                            <div class="main-box mb-2">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="">
                                            <div>
                                                <h4 class="main-title">{{ $assessment->task_title }}</h4>

                                                <h6>{{ $assessment->description }} </h6>

                                                @forelse($questions as $key => $question)
                                                    <h4 class="mt-3">{{ $key + 1 }}.
                                                        {{ $question->type == 'rating' ? 'Review the course' : $question->question }}
                                                        @if ($question->is_required)
                                                            <span class="text-danger">*</span>
                                                        @endif
                                                    </h4>
                                                    @if ($question->image)
                                                        <div>
                                                            <div class="image-uplodasection">
                                                                <img src="{{ url($question->image) }}"
                                                                    style="object-fit: contain; object-position: center" />
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if ($question->type == 'single choice')
                                                        @if ($question->is_dropdown)
                                                            <div class="row">
                                                                <div class="col-md-6 mt-1">
                                                                    <select class="form-select" name="answerselect"
                                                                        {{ $question->is_required ? 'required' : '' }}>
                                                                        <option>Select</option>
                                                                        @forelse($question->options as $option)
                                                                            <option>{{ $option->option }}</option>
                                                                        @empty
                                                                        @endforelse
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        @else
                                                            @forelse($question->options as $option)
                                                                <div class="form-check ansercheckbox pt-1 margin-box">
                                                                    <input class="form-check-input" name="answerselect"
                                                                        type="radio" id="answer4" value="checked"
                                                                        {{ $question->is_required ? 'required' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="answer4">{{ $option->option }}</label>
                                                                </div>
                                                            @empty
                                                            @endforelse
                                                        @endif
                                                    @elseif ($question->type == 'multiple choice')
                                                        @if ($question->is_dropdown)
                                                            <div class="row">
                                                                <div class="col-md-6 mt-1">
                                                                    <select class="form-select select2" name="answerselect1"
                                                                        multiple
                                                                        {{ $question->is_required ? 'required' : '' }}>
                                                                        <option>Select</option>
                                                                        @forelse($question->options as $option)
                                                                            <option>{{ $option->option }}</option>
                                                                        @empty
                                                                        @endforelse
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        @else
                                                            @forelse($question->options as $option)
                                                                <div class="form-check ansercheckbox pt-1 margin-box">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        id="answer1" name="answerselect1" value="checked"
                                                                        {{ $question->is_required ? 'required' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="answer1">{{ $option->option }}</label>
                                                                </div>
                                                            @empty
                                                            @endforelse
                                                        @endif
                                                    @elseif ($question->type == 'dropdown')
                                                        <div class="row">
                                                            <div class="col-md-6 mt-1">
                                                                <select class="form-select" name="answerselect2"
                                                                    {{ $question->is_required ? 'required' : '' }}>
                                                                    <option>Select</option>
                                                                    @forelse($question->options as $option)
                                                                        <option>{{ $option->option }}</option>
                                                                    @empty
                                                                    @endforelse
                                                                </select>
                                                            </div>
                                                        </div>
                                                    @elseif ($question->type == 'short answer')
                                                        <div class="row">
                                                            <div class="col-md-6 mt-1">
                                                                <input type="text" name="answerselect3"
                                                                    class="form-control"
                                                                    placeholder="Enter your Short answer"
                                                                    {{ $question->is_required ? 'required' : '' }} />
                                                            </div>
                                                        </div>
                                                    @elseif ($question->type == 'file upload')
                                                        <div class="row">
                                                            <div class="col-md-6 mt-1">
                                                                <input type="file" name="answerselect4"
                                                                    class="form-control" placeholder="Choose the file"
                                                                    {{ $question->is_required ? 'required' : '' }} />
                                                            </div>
                                                        </div>
                                                    @elseif ($question->type == 'rating')
                                                        <h6 class="mt-1">{{ $question->score_from }}: Not Likely,
                                                            {{ $question->score_to }}: Extremely Likely</h6>

                                                        <div class="custom-options-checkable g-1 d-flex mt-1 flex-wrap">
                                                            @for ($i = $question->score_from; $i <= $question->score_to; $i++)
                                                                <div class="me-1 mb-sm-0 mb-1">
                                                                    <input class="custom-option-item-check" type="radio"
                                                                        name="loantypeselect"
                                                                        id="homeloan{{ $i }}"
                                                                        {{ $question->is_required ? 'required' : '' }} />
                                                                    <label class="custom-option-item text-center p-1"
                                                                        for="homeloan{{ $i }}">
                                                                        {{ $i }}
                                                                    </label>
                                                                </div>
                                                            @endfor
                                                        </div>
                                                    @elseif ($question->type == 'image')
                                                        @forelse($question->options as $option)
                                                            <div class="mb-1 mt-2 innergroupanser option-preview-section">
                                                                <div class="row">
                                                                    <div class="col-md-2 mt-1 mt-sm-0">
                                                                        <input class="form-check-input" name="answerselect5"
                                                                            type="radio" id="answer4" value="checked"
                                                                            {{ $question->is_required ? 'required' : '' }}>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check ansercheckbox">
                                                                            <img src="{{ url($option->image) }}"
                                                                                style="height:100px; width:100px;" />
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        @empty
                                                        @endforelse
                                                    @endif

                                                @empty
                                                @endforelse

                                            </div>
                                        </div>

                                        <button class="btn btn-primary mt-3"> <i data-feather="check-circle"></i>
                                            Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


        </div>
    </div>
    </div>
    <!-- END: Content-->
@endsection
