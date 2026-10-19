@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    
    <!-- TAMPILAN KHUSUS ADMIN -->
    @if(auth()->user()->role == 'admin')
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
            <h1 class="text-2xl font-bold">Selamat Datang 👋</h1>
            <p class="text-gray-500 mt-2">Berikut adalah ringkasan operasional sistem gudang Anda hari ini.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Purchase Order</p>
                    <h3 class="text-3xl font-extrabold mt-1 text-gray-800">{{ $data['po_count'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Barang Masuk (Hari Ini)</p>
                    <h3 class="text-3xl font-extrabold mt-1 text-gray-800">{{ $data['masuk_count'] ?? 0 }}</h3>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Stok Barang</p>
                    <h3 class="text-3xl font-extrabold mt-1 text-gray-800">{{ $data['stok_total'] ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="font-bold">Purchase Order Terbaru</h3>
            </div>
            <table class="w-full text-left">
                <tbody class="divide-y divide-gray-100">
                    @forelse($data['recent_po'] ?? [] as $po)
                        <tr>
                            <td class="px-6 py-4 text-sm">{{ $po->nama_pemesan }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $po->tanggal_pesan }}</td>
                            <td class="px-6 py-4 text-sm text-right font-medium">Rp {{ number_format($po->total_order, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td class="p-6 text-center text-gray-400" colspan="3">Belum ada aktivitas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    <!-- TAMPILAN UNTUK BUKAN ADMIN (DASHBOARD ROLE MASING-MASING) -->
    @else
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
            <h1 class="text-2xl font-bold">Selamat Datang, {{ auth()->user()->name ?? 'User' }} 👋</h1>
            <p class="text-gray-500 mt-2">
                Anda masuk sebagai role: <span class="font-semibold uppercase text-blue-600">{{ auth()->user()->role }}</span>
            </p>
        </div>

        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <h3 class="font-bold text-lg mb-2">Area Kerja {{ ucfirst(auth()->user()->role) }}</h3>
            <p class="text-gray-600 text-sm">
                Silakan gunakan menu navigasi di sebelah kiri untuk mengelola tugas dan data yang sesuai dengan hak akses Anda.
            </p>
        </div>
    @endif

</div>
@endsection