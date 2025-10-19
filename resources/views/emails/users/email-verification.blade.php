@extends('emails.layouts.base-peyda')

@section('title', __('site.Verify Email Address'))

@section('content')
    <!-- Logo Section -->
    <div class="logo">
        <h1 class="logo-text">
            FINYBO
            <span class="logo-dot"></span>
        </h1>
    </div>

    <!-- Title Section -->
    <h2 class="title">{{ __('site.Verify Email Address') }}</h2>

    <!-- Content Section -->
    <p class="content">
        {{ __('site.Hello') }} {{ $user->first_name }},<br><br>
        {{ __('site.Please click the button below to verify your email address.') }}
    </p>

    <!-- CTA Button -->
    <a href="{{ $verificationUrl }}" class="button">
        {{ __('site.Verify Email Address') }}
    </a>

    <!-- Warning Section -->
    <div class="warning-text">
        {{ __('site.This verification link will expire in 60 minutes.') }}<br>
        {{ __('site.If you did not create an account, no further action is required.') }}
    </div>

    <!-- URL Section -->
    <div class="url-text">
        <strong>{{ __('site.If you are having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:') }}</strong><br><br>
        {{ $verificationUrl }}
    </div>

@endsection
