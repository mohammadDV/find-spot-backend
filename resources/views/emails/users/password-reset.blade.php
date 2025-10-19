@extends('emails.layouts.base-peyda')

@section('title', __('site.Password reset request'))

@section('content')
    <!-- Logo Section -->
    <div class="logo">
        <img src="https://varzeshpod-prod.s3.ir-thr-at1.arvanstorage.com/uploads/images/default/user-8/2025/10/19/EkeDwVgmts3OAOUPUY8eXQF7ZA9WVPAeoQjD2MT6.jpg" alt="Finybo" class="logo-img" width="160" height="40" style="height: 40px; width: auto;">
    </div>

    <!-- Title Section -->
    <h2 class="title">{{ __('site.Password reset request') }}</h2>

    <!-- Content Section -->
    <p class="content">
        {{ __('site.Hello') }} {{ $user->first_name }},<br><br>
        {{ __('site.You are receiving this email because we received a password reset request for your account.') }}
    </p>

    <!-- CTA Button -->
    <a href="{{ $resetUrl }}" class="button">
        {{ __('site.Reset Password') }}
    </a>

    <!-- Warning Section -->
    <div class="warning-text">
        {{ __('site.This password reset link will expire in 60 minutes.') }}<br>
        {{ __('site.If you did not request a password reset, no further action is required.') }}
    </div>

    <!-- URL Section -->
    <div class="url-text">
        <strong>{{ __('site.If you are having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:') }}</strong><br><br>
        {{ $resetUrl }}
    </div>

@endsection
