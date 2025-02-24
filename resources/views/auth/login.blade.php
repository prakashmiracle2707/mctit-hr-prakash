<style>
  .google-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: white;
      color: black;
      font-size: 16px;
      font-weight: bold;
      border: 1px solid #ccc; /* Adds a thin border */
      border-radius: 25px; /* Makes it fully rounded */
      padding: 10px 20px;
      text-decoration: none;
      transition: background 0.3s ease-in-out, box-shadow 0.2s;
      width: 100%;
      height: 50px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  }
  
  .google-btn:hover {
      background-color: #f8f9fa;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  }
  
  .google-btn img {
      height: 20px;
      margin-right: 10px;
      color: black !important;
  }
</style>
@extends('layouts.auth')
@section('page-title')
    {{ __('Login') }}
@endsection
@section('language-bar')
    @php
        $languages = App\Models\Utility::languages();

        $lang = \App::getLocale('lang');
        $LangName = \App\Models\Languages::where('code', $lang)->first();
        if (empty($LangName)) {
            $LangName = new App\Models\Utility();
            $LangName->fullName = 'English';
        }

        $settings = App\Models\Utility::settings();
        config([
            'captcha.sitekey' => $settings['google_recaptcha_key'],
            'captcha.secret' => $settings['google_recaptcha_secret'],
            'options' => [
                'timeout' => 30,
            ],
        ]);
    @endphp
    <div class="lang-dropdown-only-desk">
        <li class="dropdown dash-h-item drp-language">
            <a class="dash-head-link dropdown-toggle btn" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="drp-text"> {{ ucfirst($LangName->fullName) }}
                </span>
            </a>
            <div class="dropdown-menu dash-h-dropdown dropdown-menu-end">
                @foreach ($languages as $code => $language)
                    <a href="{{ route('login', $code) }}" tabindex="0"
                        class="dropdown-item {{ $code == $lang ? 'active' : '' }}">
                        <span>{{ ucFirst($language) }}</span>
                    </a>
                @endforeach
            </div>
        </li>
    </div>
@endsection
@section('content')
    <div class="card-body">
        <div>
            <h2 class="mb-3 f-w-600">{{ __('Login') }}</h2>
        </div>
        <div class="custom-login-form">
            <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate="">
                @csrf
                <div class="form-group mb-3">
                    <label class="form-label">{{ __('Email') }}</label>
                    <input id="email" type="email" class="form-control  @error('email') is-invalid @enderror"
                        name="email" placeholder="{{ __('Enter your email') }}" required autofocus>
                    @error('email')
                        <span class="error invalid-email text-danger" role="alert">
                            <small>{{ $message }}</small>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-3 pss-field">
                    <label class="form-label">{{ __('Password') }}</label>
                    <input id="password" type="password" class="form-control  @error('password') is-invalid @enderror"
                        name="password" placeholder="{{ __('Password') }}" required>
                    @error('password')
                        <span class="error invalid-password text-danger" role="alert">
                            <small>{{ $message }}</small>
                        </span>
                    @enderror
                </div>
                <div class="form-group mb-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between">
                        @if (Route::has('password.request'))
                            <span>
                                <a href="{{ route('password.request', $lang) }}"
                                    tabindex="0">{{ __('Forgot Your Password?') }}</a>
                            </span>
                        @endif
                    </div>
                </div>
                @if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes')
                    @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
                        <div class="form-group mb-4">
                            {!! NoCaptcha::display() !!}
                            @error('g-recaptcha-response')
                                <span class="error small text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @else
                        <div class="form-group mb-4">
                            <input type="hidden" id="g-recaptcha-response" name="g-recaptcha-response"
                                class="form-control">
                            @error('g-recaptcha-response')
                                <span class="error small text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    @endif
                @endif
                <div class="d-grid">
                    <button class="btn btn-primary mt-2" type="submit">
                        {{ __('Login') }}
                    </button>
                </div>
            </form>

            <a href="{{ route('login.google') }}" class="google-btn" style="color: black;">
                <img src="https://developers.google.com/identity/images/g-logo.png" alt="Google Logo">
                Login with Google
            </a>


        </div>
    </div>
@endsection
@push('custom-scripts')
    <script src="{{ asset('custom/libs/jquery/dist/jquery.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $(".form_data").submit(function(e) {
                $(".login_button").attr("disabled", true);
                return true;
            });
        });
    </script>

    @if (isset($settings['recaptcha_module']) && $settings['recaptcha_module'] == 'yes')
        @if (isset($settings['google_recaptcha_version']) && $settings['google_recaptcha_version'] == 'v2-checkbox')
            {!! NoCaptcha::renderJs() !!}
        @else
            <script src="https://www.google.com/recaptcha/api.js?render={{ $settings['google_recaptcha_key'] }}"></script>
            <script>
                $(document).ready(function() {
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ $settings['google_recaptcha_key'] }}', {
                            action: 'submit'
                        }).then(function(token) {
                            $('#g-recaptcha-response').val(token);
                        });
                    });
                });
            </script>
        @endif
    @endif
@endpush
