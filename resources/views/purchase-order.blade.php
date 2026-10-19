@extends('layouts.app')

@section('title', 'Purchase Order')
@section('page-title', 'Purchase Order')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm flex flex-wrap gap-3">
        <form method="GET" class="flex flex-wrap gap-3 w-full">
            <input type="text" name="search" placeholder="Cari nama pemesan..." class="px-4 py-2 rounded-xl border border-gray-200 outline-none flex-1">
            <select name="cabang" class="px-4 py-2 rounded-xl border border-gray-200 outline-none">
                <option value="">Semua Cabang</option>
            </select>
            <input type="date" name="tanggal" class="px-4 py-2 rounded-xl border border-gray-200 outline-none">
            <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-xl">Filter</button>
            <button type="button" onclick="openModal('modal-po')" class="bg-blue-600 text-white px-6 py-2 rounded-xl ml-auto">+ PO Baru</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-4 text-sm">No</th>
                    <th class="px-6 py-4 text-sm">Pemesan</th>
                    <th class="px-6 py-4 text-sm">Tempat</th>
                    <th class="px-6 py-4 text-sm">Sales</th>
                    <th class="px-6 py-4 text-sm">Tanggal</th>
                    <th class="px-6 py-4 text-sm">Tipe Pemesanan</th>
                    <th class="px-6 py-4 text-sm text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($purchaseOrders as $po)
                    <tr>
                        <td class="px-6 py-4 text-sm">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 text-sm">{{ $po->nama_pemesan }}</td>
                        <td class="px-6 py-4 text-sm">{{ $po->alamat_tempat }}</td>
                        <td class="px-6 py-4 text-sm">{{ $po->salesman }}</td>
                        <td class="px-6 py-4 text-sm">{{ $po->tanggal_pesan }}</td>
                        <td class="px-6 py-4 text-sm">{{ $po->tipe_pemesanan }}</td>
                        <td class="px-6 py-4 text-sm text-center flex justify-center gap-2">
                            <button onclick="showDetail({{ $po->id }})" class="text-blue-600 hover:text-blue-800">Detail</button>
                            <button onclick="openEditModal({{ $po->id }})" class="text-amber-600 hover:text-amber-800">Edit</button>
                            <button onclick="openDeleteModal({{ $po->id }})" class="text-red-600 hover:text-red-800">Hapus</button>
                            <button
                                onclick="openPrintModal({{ $po->id }})"
                                class="text-green-600 hover:text-green-800 font-medium">

                                Dokumen

                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div id="modal-po" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-7xl max-h-[90vh] overflow-y-auto p-8 shadow-2xl">
        <h2 id="modal-title" class="text-xl font-bold mb-6">Tambah Purchase Order</h2>
        <form id="po-form" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemesan</label>
                        <input type="text" name="nama_pemesan" placeholder="Contoh: Budi Santoso" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Pemesan</label>
                        <input type="text" name="alamat_pemesan" placeholder="Contoh: Toko Maju Jaya" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Penerima</label>
                        <input type="text" name="nama_tempat" placeholder="Contoh: Toko Maju Jaya" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Kirim</label>
                        <input type="text" name="alamat_tempat" placeholder="Contoh: Toko Maju Jaya" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pesan</label>
                        <input type="date" name="tanggal_pesan" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                    </div>
                    @if(Auth::user()->cabang != 'Pusat')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Salesman</label>
                        <select name="salesman" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-white" required>
                            <option value="">-- Pilih Sales --</option>
                            @foreach ($sales as $sa)
                                <option value="{{ $sa->name }}">{{ $sa->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if(Auth::user()->cabang == 'Pusat')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cabang</label>
                        <select name="cabang" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition">
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
                        <select name="tipe_pemesanan" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none transition" required>
                            <option value="">Pilih Tipe...</option>
                            <option value="termin">Termin</option>
                            <option value="dp">DP</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-gray-700">Detail Barang</h3>
                    <button type="button" onclick="addRow()" 
                        class="text-sm bg-green-600 text-white px-4 py-2 rounded-xl hover:bg-green-700 transition flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Barang
                    </button>
                </div>
                
                <div class="overflow-hidden border border-gray-200 rounded-2xl">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold">
                            <tr>
                                <th class="px-6 py-3 w-2/3">Barang</th>
                                <th class="px-6 py-3 w-1/4">Jumlah</th>
                                <th class="px-6 py-3 w-auto text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detail-table-body" class="divide-y divide-gray-100">
                            </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Cetak</label>
                        <input type="text" name="judul_cetak" placeholder="Contoh: Label Pengiriman" 
                            class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran</label>
                        <input type="text" name="ukuran" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Uang Muka</label>
                        <input type="text" name="uang_muka" oninput="formatRupiah(this)" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Ply</label>
                        <input type="number" name="jumlah_ply" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No Film</label>
                        <input type="text" name="no_film" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Isi Per Box</label>
                        <input type="number" name="isi_per_box" id="perbox" oninput="cekJumlah(this)" placeholder="Contoh: 100 pcs" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Perporasi</label>
                        <input type="text" name="perporasi" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sisa Pembayaran</label>
                        <input type="text" value="0" name="sisa_pembayaran" id="sisa_pembayaran" oninput="formatRupiah(this)" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Terbilang</label>
                        <input type="text" value="kosong" name="terbilang" id="terbilang" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Order</label>
                        <input type="text" name="total_order" id="total_order" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500" readonly>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <textarea name="keterangan" rows="2" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button type="button" onclick="closeModals()" class="px-6 py-2 text-gray-500">Batal</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl">Simpan PO</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-delete" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl">

        <div class="text-center">

            <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                </svg>
            </div>

            <h2 class="text-xl font-bold text-gray-800">
                Hapus Purchase Order
            </h2>

            <p class="mt-3 text-gray-500">
                Apakah Anda yakin ingin menghapus Purchase Order ini?
                <br>
                <span class="font-semibold text-red-600">
                    Data yang dihapus tidak dapat dikembalikan.
                </span>
            </p>

        </div>

        <form id="deleteForm" method="POST" class="mt-6">
            @csrf
            @method('DELETE')

            <div class="flex justify-end gap-3">

                <button
                    type="button"
                    onclick="closeModal('modal-delete')"
                    class="px-5 py-2 rounded-xl border border-gray-300 hover:bg-gray-100">

                    Batal

                </button>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl bg-red-600 text-white hover:bg-red-700">

                    Ya, Hapus

                </button>

            </div>

        </form>

    </div>
</div>

<div id="modal-detail" class="fixed inset-0 z-50 hidden bg-black/50">

    <div class="flex items-center justify-center min-h-screen p-4">

        <div class="bg-white rounded-3xl w-full max-w-6xl max-h-[90vh] overflow-y-auto shadow-2xl">

            <!-- Header -->
            <div class="flex items-center justify-between border-b px-8 py-5">

                <div>
                    <h2 class="text-2xl font-bold">
                        Detail Purchase Order
                    </h2>

                    <p class="text-sm text-gray-500">
                        Informasi lengkap Purchase Order
                    </p>
                </div>

                <button onclick="closeModal('modal-detail')"
                    class="text-gray-500 hover:text-red-600 text-2xl">
                    &times;
                </button>

            </div>

            <div class="p-8">

                <!-- Data Pemesan -->
                <div class="grid md:grid-cols-2 gap-6">

                    <div class="space-y-4">

                        <div>
                            <label class="text-gray-500 text-sm">Nama Pemesan</label>
                            <p id="detail_nama_pemesan" class="font-semibold"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Alamat Pemesan</label>
                            <p id="detail_alamat_pemesan"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Penerima</label>
                            <p id="detail_nama_tempat"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Alamat Kirim</label>
                            <p id="detail_alamat_tempat"></p>
                        </div>

                    </div>

                    <div class="space-y-4">

                        <div>
                            <label class="text-gray-500 text-sm">Tanggal Pesan</label>
                            <p id="detail_tanggal_pesan"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Salesman</label>
                            <p id="detail_salesman"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Cabang</label>
                            <p id="detail_cabang"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Tipe Pemesanan</label>
                            <p id="detail_tipe_pemesanan"></p>
                        </div>

                    </div>

                </div>

                <!-- Detail Barang -->

                <div class="mt-8">

                    <h3 class="font-bold mb-3">
                        Detail Barang
                    </h3>

                    <div class="overflow-hidden rounded-xl border">

                        <table class="w-full text-sm">

                            <thead class="bg-gray-100">

                                <tr>

                                    <th class="px-4 py-3 text-left">Barang</th>

                                    <th class="px-4 py-3 text-center">Jumlah</th>

                                </tr>

                            </thead>

                            <tbody id="detail_barang_table">

                            </tbody>

                        </table>

                    </div>

                </div>

                <!-- Detail Produksi -->

                <div class="grid md:grid-cols-2 gap-6 mt-8">

                    <div class="space-y-4">

                        <div>
                            <label class="text-gray-500 text-sm">Judul Cetak</label>
                            <p id="detail_judul_cetak"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Ukuran</label>
                            <p id="detail_ukuran"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Jumlah Box</label>
                            <p id="detail_jumlah_box"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Isi / Box</label>
                            <p id="detail_isi_per_box"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Jumlah Ply</label>
                            <p id="detail_jumlah_ply"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">No Film</label>
                            <p id="detail_no_film"></p>
                        </div>

                    </div>

                    <div class="space-y-4">

                        <div>
                            <label class="text-gray-500 text-sm">Perporasi</label>
                            <p id="detail_perporasi"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Harga / Box</label>
                            <p id="detail_harga_per_box"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Uang Muka</label>
                            <p id="detail_uang_muka"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Sisa Pembayaran</label>
                            <p id="detail_sisa_pembayaran"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Total Order</label>
                            <p class="font-bold text-green-600"
                               id="detail_total_order"></p>
                        </div>

                        <div>
                            <label class="text-gray-500 text-sm">Terbilang</label>
                            <p id="detail_terbilang"></p>
                        </div>

                    </div>

                </div>

                <div class="mt-6">

                    <label class="text-gray-500 text-sm">
                        Keterangan
                    </label>

                    <p id="detail_keterangan"
                       class="border rounded-xl p-4 bg-gray-50"></p>

                </div>

                <div class="flex justify-end mt-8">

                    <button
                        onclick="closeModal('modal-detail')"
                        class="bg-blue-600 text-white px-6 py-2 rounded-xl">

                        Tutup

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<div id="modal-print"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">

    <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl">

        <!-- Header -->
        <div class="border-b px-6 py-4 flex justify-between items-center">

            <h2 class="text-xl font-bold">
                Cetak Dokumen
            </h2>

            <button onclick="closeModal('modal-print')"
                class="text-gray-500 hover:text-black text-xl">

                ✕

            </button>

        </div>

        <!-- Body -->
        <div class="grid grid-cols-2 gap-4 p-6">

            <!-- Penawaran -->
            <a id="btn-quotation"
                target="_blank"
                class="border rounded-xl p-5 hover:bg-blue-50 transition">

                <div class="text-4xl mb-2">📄</div>

                <h3 class="font-bold">
                    Quotation
                </h3>

                <p class="text-sm text-gray-500">
                    Surat Penawaran Harga
                </p>

            </a>

            <!-- Invoice -->
            <a id="btn-invoice"
                target="_blank"
                class="border rounded-xl p-5 hover:bg-green-50 transition">

                <div class="text-4xl mb-2">🧾</div>

                <h3 class="font-bold">
                    Invoice
                </h3>

                <p class="text-sm text-gray-500">
                    Tagihan Pembayaran
                </p>

            </a>

            <!-- Faktur -->
            <a id="btn-faktur"
                target="_blank"
                class="border rounded-xl p-5 hover:bg-yellow-50 transition">

                <div class="text-4xl mb-2">💰</div>

                <h3 class="font-bold">
                    Faktur
                </h3>

                <p class="text-sm text-gray-500">
                    Bukti Pembayaran
                </p>

            </a>

            <!-- SPK -->
            <a id="btn-spk"
                target="_blank"
                class="border rounded-xl p-5 hover:bg-purple-50 transition">

                <div class="text-4xl mb-2">🏭</div>

                <h3 class="font-bold">
                    SPK
                </h3>

                <p class="text-sm text-gray-500">
                    Surat Perintah Kerja
                </p>

            </a>

            <!-- Surat Jalan -->
            <a id="btn-suratjalan"
                target="_blank"
                class="border rounded-xl p-5 hover:bg-orange-50 transition col-span-2">

                <div class="text-4xl mb-2">🚚</div>

                <h3 class="font-bold">
                    Surat Jalan
                </h3>

                <p class="text-sm text-gray-500">
                    Dokumen Pengiriman Barang
                </p>

            </a>

        </div>

    </div>

</div>

<script>
    function openModal(id) { 
        
        resetForm();

        document.getElementById(id).classList.remove('hidden');

    }
    function closeModals() { 
        ['modal-po', 'modal-detail', 'modal-delete'].forEach(id => document.getElementById(id).classList.add('hidden'));
    }

    // FUNGSI BARIS BARANG DINAMIS
    function addRow(data=null){

        const tbody=document.getElementById('detail-table-body');

        const rowId=tbody.children.length+1;

        const row=document.createElement('tr');

        row.innerHTML=`
            <td class="p-3">
                <select name="barang_id[]"
                    onchange="updateMaxStock(this,${rowId})"
                    class="w-full border rounded-xl p-2">

                    <option value="">Pilih Barang</option>

                    @foreach($warehouse as $barang)

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

    function openPrintModal(id){

        document.getElementById('btn-quotation').href =
            `/purchase-order/${id}/quotation`;

        document.getElementById('btn-invoice').href =
            `/purchase-order/${id}/invoice`;

        document.getElementById('btn-faktur').href =
            `/purchase-order/${id}/faktur`;

        document.getElementById('btn-spk').href =
            `/purchase-order/${id}/print`;

        document.getElementById('btn-suratjalan').href =
            `/purchase-order/${id}/surat-jalan`;

        document.getElementById('modal-print')
            .classList.remove('hidden');

        document.getElementById('modal-print')
            .classList.add('flex');
    }

    function cekJumlah(el){
        const jumlahInput = document.querySelectorAll('input[name="jumlah_beli[]"]');
        if(jumlahInput.length == 0){
            alert('Jumlah Barang Kosong'); 
            el.value = 0;
            return;  
        } 
        for(let i = 1; i <= jumlahInput.length; i++){
            const inputJumlah = document.getElementById(`input-jumlah-${i}`);
            if(inputJumlah && parseInt(el.value) > parseInt(inputJumlah.getAttribute('max')) || parseInt(el.value) > inputJumlah.value){
                alert('jumlah melebihi stok yang tersedia');
                el.value = inputJumlah.value;
                return;
            }
        }
    }

    function cekIsi(el){
        const jumlahInput = document.querySelectorAll('input[name="jumlah_beli[]"]');
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

    function resetForm() {

        document.getElementById('po-form').reset();

        document.getElementById('detail-table-body').innerHTML='';

        addRow();

        document.getElementById('modal-title').innerHTML="Tambah Purchase Order";

        const form=document.getElementById('po-form');

        form.action="/purchase-order";

        form.method="POST";

        const method=document.getElementById('method-put');

        if(method){
            method.remove();
        }

    }

    async function openEditModal(id){

        const response=await fetch(`/purchase-order/${id}/edit`);

        const data=await response.json();

        const form=document.getElementById('po-form');

        form.action=`/purchase-order/${id}`;

        let method=document.getElementById('method-put');

        if(!method){

            method=document.createElement('input');

            method.type='hidden';

            method.name='_method';

            method.value='PUT';

            method.id='method-put';

            form.appendChild(method);

        }

        document.getElementById('modal-title').innerHTML="Edit Purchase Order";

        //======================
        // HEADER
        //======================

        const selectSales = form.querySelector('select[name="salesman"]');

        console.log(data);
        
        form.nama_pemesan.value=data.nama_pemesan;
        form.alamat_pemesan.value=data.alamat_pemesan;
        form.nama_tempat.value=data.nama_tempat;
        form.alamat_tempat.value=data.alamat_tempat;
        form.tanggal_pesan.value=data.tanggal_pesan;
        form.salesman.value=data.salesman;
        form.cabang.value=data.cabang;
        form.tipe_pemesanan.value=data.tipe_pemesanan;
        
        form.judul_cetak.value=data.judul_cetak;
        form.ukuran.value=data.ukuran;
        form.jumlah_box.value=data.jumlah_box;
        form.harga_per_box.value=data.harga_per_box;
        form.uang_muka.value=data.uang_muka;
        form.jumlah_ply.value=data.jumlah_ply;
        form.no_film.value=data.no_film;
        form.isi_per_box.value=data.isi_per_box;
        form.perporasi.value=data.perporasi;
        form.sisa_pembayaran.value=data.sisa_pembayaran;
        form.terbilang.value=data.terbilang;
        form.total_order.value=data.total_order;
        form.keterangan.value=data.keterangan;

        //======================
        // DETAIL BARANG
        //======================

        const tbody=document.getElementById('detail-table-body');

        tbody.innerHTML='';

        console.log(data);

        data.detail.forEach((item,index)=>{

            addRow(item);

            const row=tbody.children[index];

            row.querySelector('select[name="barang_id[]"]').value=item.barang_id;

            row.querySelector('input[name="jumlah_beli[]"]').value=item.jumlah_beli;

            updateMaxStock(
                row.querySelector('select[name="barang_id[]"]'),
                index+1
            );

        });

        document.getElementById('modal-po').classList.remove('hidden');

    }

    async function showDetail(id){

        const response = await fetch(`/purchase-order/${id}/edit`);

        const data = await response.json();

        document.getElementById('detail_nama_pemesan').innerText=data.nama_pemesan;
        document.getElementById('detail_alamat_pemesan').innerText=data.alamat_pemesan;
        document.getElementById('detail_nama_tempat').innerText=data.nama_tempat;
        document.getElementById('detail_alamat_tempat').innerText=data.alamat_tempat;
        document.getElementById('detail_tanggal_pesan').innerText=data.tanggal_pesan;
        document.getElementById('detail_salesman').innerText=data.salesman;
        document.getElementById('detail_cabang').innerText=data.cabang;
        document.getElementById('detail_tipe_pemesanan').innerText=data.tipe_pemesanan;

        document.getElementById('detail_judul_cetak').innerText=data.judul_cetak;
        document.getElementById('detail_ukuran').innerText=data.ukuran;
        document.getElementById('detail_jumlah_box').innerText=data.jumlah_box;
        document.getElementById('detail_isi_per_box').innerText=data.isi_per_box;
        document.getElementById('detail_jumlah_ply').innerText=data.jumlah_ply;
        document.getElementById('detail_no_film').innerText=data.no_film;
        document.getElementById('detail_perporasi').innerText=data.perporasi;
        document.getElementById('detail_harga_per_box').innerText=data.harga_per_box;
        document.getElementById('detail_uang_muka').innerText=data.uang_muka;
        document.getElementById('detail_sisa_pembayaran').innerText=data.sisa_pembayaran;
        document.getElementById('detail_total_order').innerText=data.total_order;
        document.getElementById('detail_terbilang').innerText=data.terbilang;
        document.getElementById('detail_keterangan').innerText=data.keterangan;

        const tbody=document.getElementById('detail_barang_table');

        tbody.innerHTML=''; 

        for (const item of data.detail) {

            const responseBarang = await fetch(`/getBarang/${item.barang_id}`);
            const resultBarang = await responseBarang.json();
            console.log(resultBarang);

            tbody.innerHTML += `
                <tr class="border-t">
                    <td class="px-4 py-3">${resultBarang.barang}</td>
                    <td class="px-4 py-3 text-center">${item.jumlah_beli}</td>
                </tr>
            `;
        }

        document.getElementById('modal-detail').classList.remove('hidden');

    }

    function openDeleteModal(id){

        const modal = document.getElementById('modal-delete');
        const form = document.getElementById('deleteForm');

        form.action = `/purchase-order/${id}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // ESC to Close
    document.addEventListener('keydown', (e) => { if(e.key === 'Escape') closeModals(); });
</script>
@endsection