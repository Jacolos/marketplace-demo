@extends('layouts.app')

@section('title', 'Edycja klienta')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Edycja klienta: {{ $customer->company_name }}</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nazwa firmy *</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $customer->company_name) }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('company_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">NIP</label>
                    <input type="text" value="{{ $customer->nip }}" disabled
                        class="w-full px-3 py-2 border rounded bg-gray-100">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefon *</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Osoba kontaktowa *</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person', $customer->contact_person) }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('contact_person') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Typ klienta *</label>
                    <select name="customer_type" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        <option value="retail" {{ old('customer_type', $customer->customer_type) == 'retail' ? 'selected' : '' }}>Detaliczny</option>
                        <option value="wholesale" {{ old('customer_type', $customer->customer_type) == 'wholesale' ? 'selected' : '' }}>Hurtowy</option>
                        <option value="vip" {{ old('customer_type', $customer->customer_type) == 'vip' ? 'selected' : '' }}>VIP</option>
                    </select>
                    @error('customer_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adres *</label>
                    <input type="text" name="address" value="{{ old('address', $customer->address) }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Miasto *</label>
                    <input type="text" name="city" value="{{ old('city', $customer->city) }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kod pocztowy *</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('postal_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kraj *</label>
                    <input type="text" name="country" value="{{ old('country', $customer->country) }}" maxlength="2" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('country') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Limit kredytowy *</label>
                    <input type="number" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" step="0.01" min="0" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('credit_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rabat % *</label>
                    <input type="number" name="discount_percent" value="{{ old('discount_percent', $customer->discount_percent) }}" min="0" max="100" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('discount_percent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }} class="mr-2">
                        <span>Klient aktywny</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-4">
                <a href="{{ route('customers.show', $customer) }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    Anuluj
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Zapisz zmiany
                </button>
            </div>
        </form>
    </div>
</div>
@endsection