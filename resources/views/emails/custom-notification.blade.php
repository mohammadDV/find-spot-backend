@extends('emails.layouts.base-peyda')

@section('title', $title)

@section('content')
    <!-- Logo Section -->
    <div class="logo">
        <img src="https://finybo.com/images/finybo-logo.svg" alt="Finybo" class="logo-img">
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
