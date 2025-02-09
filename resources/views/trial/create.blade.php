@extends('layouts.app')
@section('title', __('messages.create_trial'))
@section('content')

<link rel="stylesheet" href="{{ asset('css/pose-detection.css') }}">

<div class="container">
    @if(session('error'))
    <div class="alert alert-warning">
        {{ session('error') }}
    </div>
    <script>
        setTimeout(() => {
            document.querySelector('.alert').style.display = 'none';
        }, 2000);
    </script>
    @endif
    <h1>{{ __('messages.body_measurement_form') }}</h1>

    <form id="trialForm" enctype="multipart/form-data">
        @csrf

        <div id="step1">
            <div class="form-group">
                <label for="trial_name">{{ __('messages.trial_name') }}</label>
                <input type="text" id="trial_name" name="trial_name" required
                    placeholder="Enter trial name...">
            </div>
            <label for="gender">{{ __('messages.gender') }}</label>
            <div class="gender-select">
                <div class="gender-option">
                    <input type="radio" id="male" name="gender" value="male" required>
                    <label class="gender-card" for="male">
                        <div class="gender-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 2a2 2 0 0 1 2 2 2 2 0 0 1-2 2 2 2 0 0 1-2-2c0-1.1.9-2 2-2zm8 7h-5v13h-2v-6h-2v6H9V9H4V7h16v2z" />
                            </svg>
                        </div>
                        <div class="gender-label">Male</div>
                    </label>
                </div>

                <div class="gender-option">
                    <input type="radio" id="female" name="gender" value="female" required>
                    <label class="gender-card" for="female">
                        <div class="gender-icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 2a2 2 0 0 1 2 2 2 2 0 0 1-2 2 2 2 0 0 1-2-2c0-1.1.9-2 2-2zm-1 19v-6H8V8h8v7h-3v6h-2z" />
                            </svg>
                        </div>
                        <div class="gender-label">Female</div>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label for="height">{{ __('messages.height') }} (cm)</label>
                <input type="number" id="height" name="height" required placeholder="Enter height...">
            </div>
            <div class="form-group">
                <label for="weight">{{ __('messages.weight') }} (kg)</label>
                <input type="number" id="weight" name="weight" required placeholder="Enter weight...">
            </div>
            <button type="button" id="nextStep" class="btn">
                {{ __('messages.next_step') }} <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- <div id="face_step" style="display: none;">
            <div class="data-preview" id="face_data_preview">
                <video id="video_face" autoplay playsinline></video>
                <canvas id="output_face"></canvas>
            </div>

        </div> -->

        <div id="step2" style="display: none;">
            <div id="loading" style="display: none;">
                <div class="spinner-text"> Loading PoseNet model... </div>
                <div class="sk-spinner sk-spinner-pulse"></div>
            </div>
            <div id="main" style="display: block;"> <video id="video" playsinline="" style="-moz-transform:scaleX(-1);-o-transform:scaleX(-1);-webkit-transform:scaleX(-1);transform:scaleX(-1);display:none;" width="600" height="500"> </video> <canvas id="output" width="600" height="500"> </canvas>
                <div style="position: fixed; top: 0px; left: 0px; cursor: pointer; opacity: 0.9; z-index: 10000;"><canvas width="80" height="48" style="width: 80px; height: 48px; display: block;"></canvas><canvas width="80" height="48" style="width: 80px; height: 48px; display: none;"></canvas><canvas width="80" height="48" style="width: 80px; height: 48px; display: none;"></canvas></div>
            </div>
        </div>
    </form>

    <div class="success-message">
        {{ __('messages.trial_saved') }} <i class="fas fa-check-circle"></i>
    </div>
</div>

<script>
    const messages = @json(__('messages.pose_detection'));
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dat-gui/0.7.6/dat.gui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/stats.js/r16/Stats.min.js"></script>
<script type="module" src="{{ asset('dist/bundle.js') }}"></script>

@endsection