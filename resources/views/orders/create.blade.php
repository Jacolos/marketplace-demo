@extends('layouts.app')

@section('title', 'Nowe zamówienie')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Nowe zamówienie</h1>

    @livewire('order-manager')
</div>
@endsection
