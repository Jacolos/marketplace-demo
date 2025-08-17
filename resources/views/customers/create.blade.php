@extends('layouts.app')

@section('title', 'Nowy klient')

@section('content')
<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Nowy klient</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nazwa firmy *</label>
                    <input type="text" name="company_name" value="{{ old('company_name') }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('company_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">NIP *</label>
                    <input type="text" name="nip" value="{{ old('nip') }}" maxlength="10" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('nip') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Telefon *</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Osoba kontaktowa *</label>
                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('contact_person') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Typ klienta *</label>
                    <select name="customer_type" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                        <option value="retail" {{ old('customer_type') == 'retail' ? 'selected' : '' }}>Detaliczny</option>
                        <option value="wholesale" {{ old('customer_type') == 'wholesale' ? 'selected' : '' }}>Hurtowy</option>
                        <option value="vip" {{ old('customer_type') == 'vip' ? 'selected' : '' }}>VIP</option>
                    </select>
                    @error('customer_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Adres *</label>
                    <input type="text" name="address" value="{{ old('address') }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Miasto *</label>
                    <input type="text" name="city" value="{{ old('city') }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kod pocztowy *</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code') }}" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('postal_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kraj *</label>
                    <input type="text" name="country" value="{{ old('country', 'PL') }}" maxlength="2" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('country') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Limit kredytowy *</label>
                    <input type="number" name="credit_limit" value="{{ old('credit_limit', 10000) }}" step="0.01" min="0" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('credit_limit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rabat % *</label>
                    <input type="number" name="discount_percent" value="{{ old('discount_percent', 0) }}" min="0" max="100" required
                        class="w-full px-3 py-2 border rounded focus:outline-none focus:border-blue-500">
                    @error('discount_percent') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-4">
                <a href="{{ route('customers.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
                    Anuluj
                </a>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-save mr-2"></i>Zapisz klienta
                </button>
            </div>
        </form>
    </div>
</div>
@endsection