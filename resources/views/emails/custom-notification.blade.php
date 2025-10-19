@extends('emails.layouts.base-peyda')

@section('title', $title)

@section('content')
    <!-- Logo Section -->
    <div class="logo">
        <img src="https://varzeshpod-prod.s3.ir-thr-at1.arvanstorage.com/uploads/images/default/user-8/2025/10/19/EkeDwVgmts3OAOUPUY8eXQF7ZA9WVPAeoQjD2MT6.jpg" alt="Finybo" class="logo-img" width="160" height="40" style="height: 40px; width: auto;">
    </div>

    <!-- Title Section -->
    <h2 class="title">{{ $title }}</h2>

    <!-- Content Section -->
    <p class="content">{{ $content }}</p>

    <!-- CTA Button -->
    <a href="{{ $actionUrl ?? '#' }}" class="button">
        رفتن به سایت
    </a>

@endsection
