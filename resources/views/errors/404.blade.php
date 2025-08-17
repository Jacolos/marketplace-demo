@extends('layouts.app')

@section('title', 'Strona nie znaleziona')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <h1 class="text-9xl font-bold text-gray-300">404</h1>
        <h2 class="text-3xl font-semibold text-gray-800 mb-4">Strona nie znaleziona</h2>
        <p class="text-gray-600 mb-8">Przepraszamy, strona której szukasz nie istnieje.</p>
        <a href="{{ route('dashboard') }}" class="bg-blue-600 text-white px-6 py-3 rounded hover:bg-blue-700">
            <i class="fas fa-home mr-2"></i>Wróć do strony głównej
        </a>
    </div>
</div>
@endsection
