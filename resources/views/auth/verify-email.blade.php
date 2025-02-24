@extends('layouts.auth')
@section('title')
    {{ __('Verify Email') }}
@endsection
@php
    $logo = asset(Storage::url('uploads/logo/'));
    if (empty($lang)) {
        $lang = Utility::getValByName('default_language');
    }
@endphp

@section('language-bar')
    @foreach (\App\Models\Utility::languages() as $code => $language)
        <a href="{{ url('/verify-email', $code) }}" tabindex="0"
            class="dropdown-item @if ($lang == $code) text-primary @endif">
            <span>{{ ucfirst($language) }}</span>
        </a>
    @endforeach
@endsection

@section('content')
    <div class="card-body">
        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600 text-primary">
                {{ __('A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif
        <div class="mb-4 text-sm text-gray-600">
            {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div class="d-grid">
                <button type="submit" class="btn btn-primary mt-2">
                    {{ __('Resend Verification Email') }}
                </button>
            </div>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="btn btn-danger btn-sm mt-2">
                {{ __('Logout') }}
            </button>
        </form>
    </div>
@endsection
