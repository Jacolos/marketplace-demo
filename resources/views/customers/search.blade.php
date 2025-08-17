@extends('layouts.app')

@section('title', 'Wyszukiwarka produktów')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Wyszukiwarka produktów</h1>
    
    @livewire('product-search')
</div>
@endsection