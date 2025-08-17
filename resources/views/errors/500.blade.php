@extends('layouts.app')

@section('title', 'Błąd serwera')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-9xl font-bold text-gray-300">500</h1>
        <h2 class="text-3xl font-semibold text-gray-800 mb-4">Błąd serwera</h2>
        <p class="text-gray-600 mb-8">Przepraszamy, wystąpił nieoczekiwany błąd. Spróbuj ponownie później.</p>
        <a href="{{ route('dashboard') }}" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
            <i class="fas fa-home mr-2"></i>Wróć do strony głównej
        </a>
    </div>
</div>
@endsection