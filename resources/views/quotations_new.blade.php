@extends('layouts.app')
@section('title', 'Sales Order')
@section('content')

<div class="space-y-6 animate-in fade-in duration-500">
    <div id="notification-area"></div>
    
    @if (session('success'))
        <div id="flash-msg" class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-200">{{ session('success') }}</div>
    @endif

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">Quotation</h1>
            <p class="text-gray-500">Kelola dokumen penawaran harga.</p>
        </div>
        @if(Auth::user()->role == 'sales')
        <button onclick="toggleModal('modal-po', true)" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 transition font-medium">
            + Buat Quotation
        </button>
        @endif
    </div>

    <input type="hidden" id="cabang" value="{{ $cabang }}">
    <input type="hidden" id="role" value="{{ Auth::user()->role }}">

     <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4 text-sm">No</th>
                    <th class="px-6 py-4 text-sm">No. Quotation</th>
                    <th class="px-6 py-4 text-sm">Customer</th>
                    @if(Auth::user()->cabang == 'Pusat')
                    <th class="px-6 py-4 text-sm">Cabang</th>
                    @endif
                    <th class="px-6 py-4 text-sm">Keterangan Reject</th>
                    <th class="px-6 py-4 text-sm">Total</th>
                    <th class="px-6 py-4 text-sm text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="quotation-table" class="divide-y">

            </tbody>
        </table>
    </div>
</div>

<div id="modal-po" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-7xl max-h-[90vh] overflow-y-auto p-8 shadow-2xl">
        <!-- Judul Modal Dinamis -->
        <h2 id="modal-title" class="text-xl font-bold mb-6">Tambah Purchase Order</h2>
        
        <!-- Form dengan Method Spoofing untuk Laravel -->
        <form id="po-form" method="POST">
            @csrf
            <!-- Input hidden untuk method PUT saat mode edit -->
            <div id="method-container"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemesan</label>
                        <input type="text" name="nama_pemesan" id="nama_pemesan" placeholder="Contoh: Budi Santoso" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Pemesan</label>
                        <input type="text" name="alamat_pemesan" id="alamat_pemesan" placeholder="Contoh: Toko Maju Jaya" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penerima</label>
                        <input type="text" name="nama_tempat" id="nama_tempat" placeholder="Contoh: Toko Maju Jaya" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Kirim</label>
                        <input type="text" name="alamat_tempat" id="alamat_tempat" placeholder="Contoh: Toko Maju Jaya" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pesan</label>
                        <input type="date" name="tanggal_pesan" id="tanggal_pesan" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                    @if(Auth::user()->cabang != 'Pusat')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Salesman</label>
                        <select name="salesman" id="salesman" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-white" required>
                            @if(auth()->user()->role === 'sales')
                                <option value="{{ auth()->user()->id }}" selected>{{ auth()->user()->name }}</option>
                            @else
                                <option value="">-- Pilih Sales --</option>
                                @foreach ($sales as $sa)
                                    <option value="{{ $sa->name }}">{{ $sa->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    @endif

                    @if(Auth::user()->cabang == 'Pusat')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cabang</label>
                        <select name="cabang" id="cabang" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
                            @foreach(['jakarta', 'bekasi'] as $cabang)
                                <option value="{{ $cabang }}">{{ strtoupper($cabang) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <input type="hidden" name="cabang" value="{{ Auth::user()->cabang }}">
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Pemesanan</label>
                        <select name="tipe_pemesanan" id="tipe_pemesanan" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                            <option value="">Pilih Tipe...</option>
                            <option value="termin">Termin</option>
                            <option value="dp">DP</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-700">Detail</h3>
                </div>
                
                <div class="overflow-hidden border border-gray-200 rounded-2xl">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-3 w-2/4">Jenis Kertas</th>
                                <th class="px-6 py-3 w-1/4">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody id="detail-table-body" class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-6 py-4">
                                    <select name="jenis_kertas" id="jenis_kertas" class="w-full border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500">
                                        <option value="" disabled selected>Pilih jenis kertas</option>
                                        @foreach ($inventories as $inv)
                                            <option value="{{ $inv->id }}">{{ $inv->barang }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-6 py-4">
                                    <input type="number" name="jumlah_beli" id="jumlah_beli" class="w-full border-gray-300 rounded-xl px-3 py-2 text-sm focus:ring-green-500 focus:border-green-500" placeholder="0">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Cetak</label>
                        <input type="text" name="judul_cetak" id="judul_cetak" placeholder="Contoh: Label Pengiriman" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran</label>
                        <input type="text" name="ukuran" id="ukuran" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Box</label>
                        <input type="number" name="jumlah_box" id="jumlah_box" oninput="cekIsi(this)" placeholder="0" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Per Box</label>
                        <input type="text" name="harga_per_box" id="harga_per_box" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none" oninput="formatRupiah(this)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Ply</label>
                        <input type="number" name="jumlah_ply" id="jumlah_ply" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Isi Per Box</label>
                        <input type="number" name="isi_per_box" id="perbox" placeholder="Contoh: 100 pcs" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Perporasi</label>
                        <input type="text" name="perporasi" id="perporasi" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Penawaran</label>
                        <input type="text" name="total_order" id="total_order" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" readonly>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="closeModals()" class="px-6 py-2 text-gray-500">Batal</button>
                <button type="submit" id="submit-btn" class="px-6 py-2 bg-blue-600 text-white rounded-xl">Simpan PO</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-kirim" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-md p-6 shadow-2xl transform transition-all">
        <div class="text-center">
            <!-- Icon Kirim -->
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 text-blue-600 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
            </div>
            
            <h3 class="text-lg font-bold text-gray-800 mb-2">Kirim Quotation</h3>
            <p class="text-sm text-gray-500 mb-6">Apakah Anda yakin ingin mengirim quotation nomor <span id="quotation-number-text" class="font-semibold text-gray-700"></span> ini untuk diproses/approve?</p>
        </div>

        <form id="form-kirim" method="POST">
            @csrf
            @method('PUT') <!-- Atau sesuaikan dengan method route Anda (POST/PUT/PATCH) -->
            
            <div class="flex justify-center gap-3">
                <button type="button" onclick="closeKirimModal()" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-medium transition">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition shadow-sm shadow-blue-200">
                    Ya, Kirim Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-approve"
    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">

    <div class="bg-white rounded-3xl w-full max-w-3xl shadow-2xl max-h-[90vh] overflow-y-auto">

        <!-- Header -->
        <div class="border-b px-8 py-5">
            <h2 class="text-2xl font-bold">
                Detail Quotation
            </h2>

            <p class="text-sm text-gray-500">
                Silakan periksa seluruh data sebelum melakukan approval.
            </p>
        </div>

        <!-- Form Utama -->
        <form id="quotationActionForm" method="POST">

            @csrf
            @method('PUT')

            <div class="p-8 space-y-6">

                <!-- Informasi Quotation -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label
                            for="approve_number"
                            class="text-sm font-medium text-gray-500">
                            No Quotation
                        </label>

                        <input
                            id="approve_number"
                            type="text"
                            readonly
                            class="w-full mt-1 rounded-xl border bg-gray-100 px-3 py-2">
                    </div>

                    <div>
                        <label
                            for="approve_customer"
                            class="text-sm font-medium text-gray-500">
                            Customer
                        </label>

                        <input
                            id="approve_customer"
                            type="text"
                            readonly
                            class="w-full mt-1 rounded-xl border bg-gray-100 px-3 py-2">
                    </div>

                    <div>
                        <label
                            for="approve_valid"
                            class="text-sm font-medium text-gray-500">
                            Dibuat Pada
                        </label>

                        <input
                            id="approve_valid"
                            type="date"
                            readonly
                            class="w-full mt-1 rounded-xl border bg-gray-100 px-3 py-2">
                    </div>

                    <div>
                        <label
                            for="approve_total"
                            class="text-sm font-medium text-gray-500">
                            Total
                        </label>

                        <input
                            id="approve_total"
                            type="text"
                            readonly
                            class="w-full mt-1 rounded-xl border bg-gray-100 px-3 py-2">
                    </div>

                </div>

                <!-- Daftar Produk -->
                <div>

                    <h3 class="font-semibold mb-3">
                        Daftar Produk
                    </h3>

                    <div class="overflow-x-auto rounded-xl border">

                        <table class="w-full">

                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="p-3 text-left">
                                        Produk
                                    </th>

                                    <th class="p-3 text-center">
                                        Qty Box
                                    </th>

                                    <th class="p-3 text-right">
                                        Harga Per Box
                                    </th>

                                    <th class="p-3 text-right">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="approve-items">
                                <!-- Data diisi melalui JavaScript -->
                            </tbody>

                        </table>

                    </div>

                    <div
                        id="stock-result"
                        class="hidden mt-4 rounded-xl border p-4 bg-gray-50">
                    </div>

                </div>

                <!-- Keterangan Reject -->
                <div>

                    <label
                        for="keterangan_reject"
                        class="text-sm font-medium text-gray-500">

                        Keterangan Reject

                    </label>

                    <textarea
                        name="keterangan_reject"
                        id="keterangan_reject"
                        rows="4"
                        placeholder="Masukkan alasan reject..."
                        class="w-full mt-1 rounded-xl border border-gray-300 p-3 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    </textarea>

                </div>

            </div>

            <!-- Footer -->
            <div class="border-t px-8 py-5 flex justify-between items-center">

                <button
                    type="button"
                    onclick="toggleModal('modal-approve', false)"
                    class="px-5 py-2 rounded-xl border hover:bg-gray-100">

                    Tutup

                </button>

                <div class="flex gap-3">

                    <!-- Reject -->
                    <button
                        type="submit"
                        id="rejectBtn"
                        formaction=""
                        class="px-6 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed">

                        Reject

                    </button>

                    <!-- Approve -->
                    <button
                        type="submit"
                        id="approveBtn"
                        formaction=""
                        class="px-6 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed">

                        Approve

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>

<div
    id="warehouseModal"
    class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <div class="bg-white rounded-xl w-full max-w-lg shadow-xl">

        <div class="border-b px-6 py-4">
            <h2 class="text-lg font-bold">
                Kirim SPK ke Warehouse
            </h2>
        </div>

        <div class="p-6">

            <input type="hidden" id="warehouseQuotationId">

            <div class="space-y-3">

                <div>
                    <label class="text-sm text-gray-500">
                        No Quotation
                    </label>

                    <div id="warehouseQuotationNumber"
                        class="font-semibold">
                    </div>
                </div>

                <div>
                    <label class="text-sm text-gray-500">
                        Customer
                    </label>

                    <div id="warehouseCustomer"
                        class="font-semibold">
                    </div>
                </div>

                <div class="mt-4">

                    <label class="text-sm text-gray-500 font-medium">
                        Daftar Barang
                    </label>

                    <div
                        id="warehouseItems"
                        class="mt-2 border rounded-lg overflow-hidden">
                    </div>

                </div>

                <div>
                    <label class="text-sm text-gray-500">
                        Catatan
                    </label>

                    <textarea
                        id="warehouseNote"
                        class="w-full border rounded-lg p-3 mt-1"
                        rows="4"
                        placeholder="Tambahkan catatan untuk warehouse (opsional)"></textarea>
                </div>

            </div>

        </div>

        <div class="border-t px-6 py-4 flex justify-end gap-2">

            <button
                onclick="closeWarehouseModal()"
                class="px-4 py-2 rounded-lg border">
                Batal
            </button>

            <button
                onclick="sendSpkWarehouse()"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white">
                Kirim SPK
            </button>

        </div>

    </div>

</div>

<div
    id="productionModal"
    class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <div class="bg-white rounded-xl w-full max-w-lg shadow-xl">

        <div class="border-b px-6 py-4">
            <h2 class="text-lg font-bold">
                Kirim SPK ke Production
            </h2>
        </div>

        <div class="p-6">

            <input type="hidden" id="productionWarehouseSpkId">

            <div class="space-y-3">

                <div>
                    <label class="text-sm text-gray-500">
                        No SPK Warehouse
                    </label>

                    <div id="productionSpkNumber"
                        class="font-semibold">
                    </div>
                </div>

                <div>
                    <label class="text-sm text-gray-500">
                        Customer
                    </label>

                    <div id="productionCustomer"
                        class="font-semibold">
                    </div>
                </div>

                <!-- Inputan PIC Production -->
                <div>
                    <label class="text-sm text-gray-500 font-medium">
                        Pilih PIC Production
                    </label>

                    <select
                        id="productionPic"
                        class="w-full border rounded-lg p-3 mt-1 bg-white">
                        <option value="">Pilih PIC</option>
                    </select>
                </div>

                <div class="mt-4">

                    <label class="text-sm text-gray-500 font-medium">
                        Daftar Barang
                    </label>

                    <div
                        id="productionItems"
                        class="mt-2 border rounded-lg overflow-hidden">
                    </div>

                </div>

                <div>
                    <label class="text-sm text-gray-500">
                        Catatan
                    </label>

                    <textarea
                        id="productionNote"
                        class="w-full border rounded-lg p-3 mt-1"
                        rows="4"
                        placeholder="Tambahkan catatan untuk production (opsional)"></textarea>
                </div>

            </div>

        </div>

        <div class="border-t px-6 py-4 flex justify-end gap-2">

            <button
                onclick="closeProductionModal()"
                class="px-4 py-2 rounded-lg border">
                Batal
            </button>

            <button
                onclick="sendSpkProduction()"
                class="px-4 py-2 rounded-lg bg-purple-600 text-white">
                Kirim SPK
            </button>

        </div>

    </div>

</div>

<div
    id="financeModal"
    class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <div class="bg-white rounded-xl w-full max-w-lg shadow-xl">

        <div class="border-b px-6 py-4">
            <h2 class="text-lg font-bold">
                Kirim SPK ke Finance
            </h2>
        </div>

        <div class="p-6">

            <input type="hidden" id="financeQuotationId">

            <div class="space-y-3">

                <div>
                    <label class="text-sm text-gray-500">
                        No Quotation
                    </label>

                    <div id="financeQuotationNumber"
                        class="font-semibold">
                    </div>
                </div>

                <div>
                    <label class="text-sm text-gray-500">
                        Customer
                    </label>

                    <div id="financeCustomer"
                        class="font-semibold">
                    </div>
                </div>

                <div class="mt-4">

                    <label class="text-sm text-gray-500 font-medium">
                        Daftar Barang & Harga
                    </label>

                    <div
                        id="financeItems"
                        class="mt-2 border rounded-lg overflow-hidden">
                    </div>

                </div>

                <div>
                    <label class="text-sm text-gray-500">
                        Catatan
                    </label>

                    <textarea
                        id="financeNote"
                        class="w-full border rounded-lg p-3 mt-1"
                        rows="4"
                        placeholder="Tambahkan catatan untuk finance (opsional)"></textarea>
                </div>

            </div>

        </div>

        <div class="border-t px-6 py-4 flex justify-end gap-2">

            <button
                onclick="closeFinanceModal()"
                class="px-4 py-2 rounded-lg border">
                Batal
            </button>

            <button
                onclick="sendSpkFinance()"
                class="px-4 py-2 rounded-lg bg-purple-600 text-white">
                Kirim SPK
            </button>

        </div>

    </div>

</div>

<div id="modal-delete" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-sm p-8 text-center shadow-2xl">
        <h2 class="text-xl font-bold mb-4">Hapus Quotation?</h2>
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <div class="flex items-center justify-end gap-3 w-full mt-4">
                <button type="button" onclick="closeModals()" class="px-5 py-2 text-gray-600 hover:bg-gray-100 rounded-xl transition">
                    Batal
                </button>
                <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl transition">
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>



<script src="{{ asset('js/request-baru-lagi-4.js') }}"></script>

<script>
    function toggleModal(id, show) {
        document.getElementById(id).classList.toggle('hidden', !show);
    }

    function openDeleteModal(url) {
        document.getElementById('deleteForm').action = url;
        toggleModal('modal-delete', true);
    }

    function openModalAdd() {
        const form = document.getElementById('po-form');
        
        // Ubah judul & action URL untuk store
        document.getElementById('modal-title').innerText = 'Tambah Quotation';
        form.action = "{{ route('quotations.store') }}"; 
        
        // Hapus method PUT jika sebelumnya dipakai untuk edit
        document.getElementById('method-container').innerHTML = ''; 
        
        // Reset isi form
        form.reset(); 
        
        // Tampilkan modal menggunakan fungsi Anda
        toggleModal('modal-po', true);
    }

    function openModalEdit(data) {
        const form = document.getElementById('po-form');
        
        // Ubah judul & action URL untuk update berdasarkan ID data
        document.getElementById('modal-title').innerText = 'Edit Quotations';
        form.action = `/quotations/${data.id}`; 

        console.log(data); // Cek konsol browser untuk memastikan data lengkap
        
        // Tambahkan method PUT untuk Laravel
        document.getElementById('method-container').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        // Masukkan data lama ke dalam input form berdasarkan ID
        document.getElementById('nama_pemesan').value = data.nama_customer || '';
        document.getElementById('alamat_pemesan').value = data.alamat_customer || '';
        document.getElementById('nama_tempat').value = data.penerima || '';
        document.getElementById('alamat_tempat').value = data.alamat_penerima || '';
        document.getElementById('tanggal_pesan').value = data.tanggal_pesan || '';
        
        // Perbaikan Salesman (Pastikan relasi 'sales' dimuat dengan with('sales'))
        if(document.getElementById('salesman')) {
            if(data.sales) {
                document.getElementById('salesman').value = data.sales.id || data.sales;
            }
        }

        if(document.getElementById('cabang')) {
            document.getElementById('cabang').value = data.cabang || '';
        }

        // --- PERBAIKAN UTAMA UNTUK JENIS KERTAS (BARANG) ---
        // Pastikan di controller Anda menggunakan ->with('barang') atau menyertakan relasi barang
        if(document.getElementById('jenis_kertas')) {
            let barangId = data.inventory_id || (data.barang ? data.barang.id : '');
            document.getElementById('jenis_kertas').value = barangId;
        }

        document.getElementById('tipe_pemesanan').value = data.tipe_pemesanan || '';
        document.getElementById('jumlah_beli').value = data.quantity || '';
        document.getElementById('judul_cetak').value = data.judul_cetak || '';
        document.getElementById('ukuran').value = data.ukuran || '';
        
        // --- PERBAIKAN PEMETAAN PROPERTI YANG TERBALIK ---
        document.getElementById('jumlah_box').value = data.jumlah_box || ''; // Sebelumnya data.perbox (terbalik)
        document.getElementById('harga_per_box').value = data.harga || '';
        document.getElementById('jumlah_ply').value = data.jumlah_ply || '';
        document.getElementById('perbox').value = data.perbox || ''; // Sebelumnya data.jumlah_box (terbalik)
        
        document.getElementById('perporasi').value = data.perporasi || '';
        document.getElementById('total_order').value = data.total_amount || '';
        document.getElementById('keterangan').value = data.keterangan || '';

        // Tampilkan modal menggunakan fungsi Anda
        toggleModal('modal-po', true);
    }

    // Fungsi Tutup Modal (Batal / Tombol Close)
    function closeModals() {
        toggleModal('modal-po', false);
        toggleModal('modal-delete', false);
    }

    function openApproveModal(data) {

        console.log(data);

        currentQuotationId = data.id;

        // Reset stock result
        document
            .getElementById('stock-result')
            .classList
            .add('hidden');

        // Reset keterangan reject
        document
            .getElementById('keterangan_reject')
            .value = '';

        // Isi informasi quotation
        document
            .getElementById('approve_number')
            .value = data.quotation_number ?? '';

        document
            .getElementById('approve_customer')
            .value = data.nama_customer ?? '';

        document
            .getElementById('approve_valid')
            .value = data.valid_until ?? '';

        document
            .getElementById('approve_total')
            .value =
                'Rp ' +
                Number(data.total_amount || 0)
                    .toLocaleString('id-ID');

        // Set URL approve
        document
            .getElementById('approveBtn')
            .formAction =
                `/quotations/${data.id}/approve`;

        // Set URL reject
        document
            .getElementById('rejectBtn')
            .formAction =
                `/quotations/${data.id}/reject`;

        // Isi tabel item
        const tbody =
            document.getElementById('approve-items');

        tbody.innerHTML = '';

        tbody.innerHTML += `
            <tr class="border-t">
                <td class="p-3">
                    ${data.barang?.barang ?? '-'}
                </td>

                <td class="p-3 text-center">
                    ${data.jumlah_box ?? 0}
                </td>

                <td class="p-3 text-right">
                    Rp ${Number(data.harga || 0)
                        .toLocaleString('id-ID')}
                </td>

                <td class="p-3 text-right">
                    Rp ${Number(data.total_amount || 0)
                        .toLocaleString('id-ID')}
                </td>
            </tr>
        `;

        // Buka modal
        toggleModal('modal-approve', true);
    }

    function addRow(data=null){

        const tbody=document.getElementById('detail-table-body');

        const rowId=tbody.children.length+1;

        const row=document.createElement('tr');

        row.innerHTML=`
            <td class="p-3">
                <select name="barang_id[]"
                    onchange="updateMaxStock(this,${rowId})"
                    class="w-full border rounded-xl p-2">

                    <option value="">Pilih Jenis Kertas</option>

                    @foreach($inventories as $barang)

                    <option
                        value="{{ $barang->id }}"
                        data-stok="{{ $barang->jumlah }}"
                        ${data && data.barang_id=={{ $barang->id }} ? 'selected':''}>
                        {{ $barang->barang }}
                    </option>

                    @endforeach

                </select>
            </td>

            <td class="p-3">

                <input
                    id="input-jumlah-${rowId}"
                    type="number"
                    name="jumlah_beli[]"
                    class="w-full border rounded-xl p-2"
                    value="${data ? data.jumlah : ''}"
                    oninput="stockPesan('${rowId}')"
                    >

                <small id="info-stok-${rowId}"></small>

            </td>

            <td class="text-center">

                <button type="button"
                    onclick="this.closest('tr').remove()">

                    Hapus

                </button>

            </td>
        `;

        tbody.appendChild(row);

    }

    function updateMaxStock(selectElement, rowId) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const stok = selectedOption.getAttribute('data-stok');
        const inputJumlah = document.getElementById(`input-jumlah-${rowId}`);
        const infoStok = document.getElementById(`info-stok-${rowId}`);

        if (stok) {
            inputJumlah.setAttribute('max', stok);
            infoStok.textContent = `Stok tersedia: ${stok}`;
        } else {
            inputJumlah.removeAttribute('max');
            infoStok.textContent = '';
        }
    }

    function stockPesan(rowId){
        const inputJumlah = document.getElementById(`input-jumlah-${rowId}`);
        let max = parseInt(inputJumlah.max);
        if(inputJumlah.value > max){
            alert('Jumlah Melebihi Stock');
            inputJumlah.value = max
        }
    }

    function cekIsi(el){
        const jumlahInput = document.querySelectorAll('input[name="jumlah_beli"]');
        const jumlahIsi = document.getElementById('perbox').value;
        let total = 0;
        jumlahInput.forEach(input => {
            total += parseInt(input.value);
        })
        const bagi = total / jumlahIsi;
        const hasil = Math.floor(bagi);
        if(el.value > hasil){
            alert('Jumlah Box Melebihi Jumlah Barang yang dipesan');
            el.value = hasil;
            return;
        }
    }
</script>

@endsection