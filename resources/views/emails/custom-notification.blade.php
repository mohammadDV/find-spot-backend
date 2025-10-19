@extends('emails.layouts.base-peyda')

@section('title', $title)

@section('content')
    <!-- Logo Section -->
    <div class="logo">
        <h1 class="logo-text">
            FINYBO
            <span class="logo-dot"></span>
        </h1>
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
