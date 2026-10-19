@extends('layouts.app')

@section('title', 'Material Requisition')
@section('page-title', 'Material Requisition')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    
    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-xl border border-green-200 shadow-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold">Material Requisition</h1>
            <p class="text-gray-500">Daftar permintaan barang dari gudang ke purchasing.</p>
        </div>
        <button onclick="toggleModal('modal-req-add', true)" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700">
            Buat Permintaan
        </button>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-sm">No</th>
                        <th class="px-6 py-4 font-semibold text-sm">Peminta</th>
                        <th class="px-6 py-4 font-semibold text-sm">Status</th>
                        <th class="px-6 py-4 font-semibold text-sm">Tanggal</th>
                        <th class="px-6 py-4 font-semibold text-sm">Disetujui Oleh</th>
                        <th class="px-6 py-4 font-semibold text-sm text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($requisitions as $req)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4 font-medium">{{ $req->creator->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-medium 
                                {{ $req->status == 'Pending' ? 'bg-yellow-100 text-yellow-700' : 
                                   ($req->status == 'Approved' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700') }}">
                                {{ $req->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $req->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $req->approver->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col items-center gap-2 text-sm">

                                {{-- Detail --}}
                                <button
                                    type="button"
                                    onclick="openDetailModal(
                                        {{ $req->id }},
                                        '{{ $req->creator->name }}',
                                        '{{ $req->created_at->format('d M Y') }}',
                                        '{{ addslashes($req->catatan) }}',
                                        {{ $req->items }}
                                    )"
                                    class="font-medium text-blue-600 hover:text-blue-800 transition-colors">
                                    Detail
                                </button>

                                @if ($req->status == 'Pending')

                                    {{-- Approve & Reject (Role Pembelian) --}}
                                    @if (Auth::user()->role == 'pembelian')
                                        <div class="flex gap-2">
                                            <button 
                                                type="button" 
                                                onclick="openApproveModal({{ $req->id }}, {{ $req->items->toJson() }})" 
                                                class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
                                                Approve
                                            </button>

                                            {{-- <form action="{{ route('requisition.reject', $req->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <button
                                                    type="submit"
                                                    class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                                                    Reject
                                                </button>
                                            </form> --}}
                                        </div>
                                    @endif

                                    @if(Auth::user()->role == 'gudang')

                                    {{-- Edit & Hapus --}}
                                    <div class="flex gap-3">
                                        <button
                                            type="button"
                                            onclick="openEditModal({{ $req->id }}, '{{ addslashes($req->catatan) }}')"
                                            class="text-xs font-medium text-yellow-600 hover:text-yellow-800">
                                            Edit
                                        </button>

                                        <button
                                            type="button"
                                            onclick="openDeleteModal('{{ route('requisition.destroy', $req->id) }}')"
                                            class="text-xs font-medium text-red-600 hover:text-red-800">
                                            Hapus
                                        </button>
                                    </div>
                                    @endif

                                @endif

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="modal-req-add" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-6">Permintaan Barang Baru</h2>
        
        <form action="{{ route('requisition.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <div id="requisition-items-container" class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">Daftar Barang</label>
                
                <div class="row-barang flex gap-2">
                    <!-- Container untuk input -->
                    <div class="input-container w-full">
                        <select name="items[]" class="w-full px-4 py-2.5 rounded-xl border border-gray-200">
                            <option value="">Pilih barang...</option>
                            @foreach($all_warehouse as $item)
                                <option value="{{ $item->id }}">{{ $item->barang }} (Stok: {{ $item->jumlah }})</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="number" name="jumlah[]" placeholder="Qty" class="w-20 px-4 py-2.5 rounded-xl border border-gray-200">
                    <button type="button" onclick="toggleInput(this)" class="text-xs bg-gray-100 px-2 rounded-xl">Baru?</button>
                </div>
            </div>

            <button type="button" onclick="addRow()" class="text-sm text-blue-600 font-medium hover:underline">+ Tambah Barang Lain</button>

            <div class="mt-4">
                <textarea name="catatan" rows="3" placeholder="Catatan untuk Purchasing..." class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
            </div>
            
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="toggleModal('modal-req-add', false)" class="px-6 py-2 text-gray-600">Batal</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl shadow-lg hover:bg-blue-700">Kirim Permintaan</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-req-edit" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-sm p-8 shadow-2xl">
        <h2 class="text-xl font-bold mb-4">Edit Catatan</h2>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <textarea name="catatan" id="edit-catatan" rows="3" class="w-full p-3 border rounded-xl mb-4"></textarea>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="toggleModal('modal-req-edit', false)" class="text-gray-600">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div id="modal-req-delete" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-sm p-8 text-center shadow-2xl">
        <h2 class="text-xl font-bold mb-4">Hapus Permintaan?</h2>
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <button type="button" onclick="toggleModal('modal-req-delete', false)" class="px-4 py-2 bg-gray-100 rounded-xl">Batal</button>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-xl">Hapus Permanen</button>
        </form>
    </div>
</div>

<div id="modal-req-detail" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4">Detail Permintaan</h2>
        <div class="space-y-4">
            <div><p class="text-xs text-gray-400">Peminta</p><p id="det-peminta" class="font-medium"></p></div>
            <div><p class="text-xs text-gray-400">Tanggal</p><p id="det-tanggal" class="font-medium"></p></div>
            <div><p class="text-xs text-gray-400">Barang</p>
                <ul id="det-items" class="list-disc pl-5 mt-2 space-y-1"></ul>
            </div>
            <div><p class="text-xs text-gray-400">Catatan</p><p id="det-catatan" class="text-sm italic"></p></div>
        </div>
        <div class="mt-8 flex justify-end">
            <button onclick="toggleModal('modal-req-detail', false)" class="px-6 py-2 bg-gray-100 rounded-xl">Tutup</button>
        </div>
    </div>
</div>

<div id="modal-approve-harga" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-4">Setujui & Input Harga</h2>
        <form id="approveForm" method="POST">
            @csrf @method('PUT')
            
            <div id="list-items-approve" class="space-y-4 mb-6">
                <!-- Data barang akan diisi via JavaScript -->
            </div>

            <div class="border-t pt-4 text-right font-bold text-lg">
                Total Seluruh: <span id="grand-total-display">Rp 0</span>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="toggleModal('modal-approve-harga', false)" class="text-gray-600">Batal</button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-xl">Simpan Approval</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id, show) { document.getElementById(id).classList.toggle('hidden', !show); }
    function addRow() {
        const container = document.getElementById('requisition-items-container');
        container.insertAdjacentHTML('beforeend', `
            <div class="row-barang flex gap-2 mt-2">
                <div class="input-container w-full">
                    <select name="items[]" class="w-full px-4 py-2.5 rounded-xl border border-gray-200">
                        @foreach($all_warehouse as $item)
                            <option value="{{ $item->id }}">{{ $item->barang }}</option>
                        @endforeach
                    </select>
                </div>
                <input type="number" name="jumlah[]" placeholder="Qty" class="w-20 px-4 py-2.5 rounded-xl border border-gray-200">
                <button type="button" onclick="toggleInput(this)" class="text-xs bg-gray-100 px-2 rounded-xl">Baru?</button>
            </div>
        `);
    }
    function toggleInput(btn) {
        const container = btn.parentElement.querySelector('.input-container');
        const isSelect = container.querySelector('select');

        if (isSelect) {
            // Ganti Select ke Input Teks
            container.innerHTML = `<input type="text" name="items[]" placeholder="Nama Barang Baru" class="w-full px-4 py-2.5 rounded-xl border border-gray-200">`;
            btn.textContent = "Pilih";
        } else {
            // Ganti Input Teks ke Select
            container.innerHTML = `
                <select name="items[]" class="w-full px-4 py-2.5 rounded-xl border border-gray-200">
                    @foreach($all_warehouse as $item)
                        <option value="{{ $item->id }}">{{ $item->barang }}</option>
                    @endforeach
                </select>`;
            btn.textContent = "Baru?";
        }
    }

    function openEditModal(id, catatan) {
        document.getElementById('editForm').action = '/requisition/' + id;
        document.getElementById('edit-catatan').value = catatan;
        toggleModal('modal-req-edit', true);
    }

    // Fungsi Delete
    function openDeleteModal(url) {
        document.getElementById('deleteForm').action = url;
        toggleModal('modal-req-delete', true);
    }

    function openDetailModal(id, peminta, tanggal, catatan, items) {
        document.getElementById('det-peminta').textContent = peminta;
        document.getElementById('det-tanggal').textContent = tanggal;
        document.getElementById('det-catatan').textContent = catatan || '-';
        
        const list = document.getElementById('det-items');
        list.innerHTML = ''; // Reset list
        
        items.forEach(item => {
            // Akses properti barang dari model Warehouse yang di-load melalui relasi
            const namaBarang = item.warehouse ? item.warehouse.barang : 'Barang Baru';
            list.innerHTML += `<li>${namaBarang} - <b>Qty: ${item.jumlah_minta}</b></li>`;
        });
        
        toggleModal('modal-req-detail', true);
    }
    function openApproveModal(id, items) {
        document.getElementById('approveForm').action = '/requisition/approve/' + id;
        const container = document.getElementById('list-items-approve');
        container.innerHTML = ''; // Reset list
        
        items.forEach((item, index) => {
            const nama = item.warehouse ? item.warehouse.barang : 'Barang Baru';
            container.innerHTML += `
                <div class="flex items-center gap-2 border-b pb-2">
                    <div class="w-full">
                        <p class="font-medium text-sm">${nama}</p>
                        <p class="text-xs text-gray-500">Qty: ${item.jumlah_minta}</p>
                    </div>
                    <input type="hidden" name="item_ids[]" value="${item.id}">
                    <input type="number" name="harga[]" placeholder="Harga Satuan" 
                        class="w-32 p-2 border rounded-xl text-sm harga-input" 
                        data-qty="${item.jumlah_minta}" required oninput="calculateGrandTotal(this)">
                </div>
            `;
        });
        
        toggleModal('modal-approve-harga', true);
    }

    // Fungsi hitung total otomatis
    function calculateGrandTotal(el) {
        let grandTotal = 0;
        document.querySelectorAll('.harga-input').forEach(input => {
            const qty = parseFloat(input.getAttribute('data-qty'));
            const harga = parseFloat(input.value) || 0;
            grandTotal += (qty * harga);
        });
        
        document.getElementById('grand-total-display').textContent = new Intl.NumberFormat('id-ID', {
            style: 'currency', currency: 'IDR', minimumFractionDigits: 0
        }).format(grandTotal);
    }
</script>
@endsection