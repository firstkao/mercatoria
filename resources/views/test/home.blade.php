@extends('layouts.app')

@section('title', 'Test Home')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-12">

    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-primary-500 mb-4">
            Selamat Datang di Mercatoria
        </h1>
        <p class="text-lg text-gray-600">
            Marketplace merchandise IP Cina untuk penggemar Indonesia
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-semibold text-lg mb-2">🎮 Katalog Game</h3>
            <p class="text-sm text-gray-600">Jelajahi merch dari game favoritmu</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-semibold text-lg mb-2">🪙 Koin & Referral</h3>
            <p class="text-sm text-gray-600">Dapat koin setiap transaksi</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h3 class="font-semibold text-lg mb-2">🚚 Pengiriman Aman</h3>
            <p class="text-sm text-gray-600">Dari Tiongkok sampai Indonesia</p>
        </div>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
        <p class="text-sm text-blue-700">
            ✅ <strong>Setup berhasil!</strong> Layout Tailwind + Blade berfungsi dengan baik.
        </p>
    </div>

    <div class="mt-8 text-center">
        <p class="text-sm text-gray-500">
            Total Settings: {{ \App\Models\Setting::count() }} · 
            Total Stores: {{ \App\Models\Store::count() }} · 
            Total Pages: {{ \App\Models\Page::count() }}
        </p>
    </div>

</div>
@endsection
