@extends('layouts.app')

@section('title', 'Quotation')
@section('page-title', 'Daftar Quotation')

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
        <button onclick="toggleModal('modal-add', true)" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 transition font-medium">
            + Buat Quotation
        </button>
        @endif
    </div>

    <input type="hidden" id="cabang" value="{{ $cabang }}">
    <input type="hidden" id="role" value="{{ Auth::user()->role }}">

    <!-- Table -->
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
                    <th class="px-6 py-4 text-sm">Total</th>
                    <th class="px-6 py-4 text-sm text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="quotation-table" class="divide-y">

            </tbody>
        </table>
    </div>
</div>

<div id="modal-approve" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">

    <div class="bg-white rounded-3xl w-full max-w-3xl shadow-2xl max-h-[90vh] overflow-y-auto">

        <div class="border-b px-8 py-5">
            <h2 class="text-2xl font-bold">
                Detail Quotation
            </h2>
            <p class="text-sm text-gray-500">
                Silakan periksa seluruh data sebelum melakukan approval.
            </p>
        </div>

        <div class="p-8 space-y-6">

            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="text-sm font-medium text-gray-500">
                        No Quotation
                    </label>

                    <input
                        id="approve_number"
                        type="text"
                        readonly
                        class="w-full mt-1 rounded-xl border bg-gray-100">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">
                        Customer
                    </label>

                    <input
                        id="approve_customer"
                        type="text"
                        readonly
                        class="w-full mt-1 rounded-xl border bg-gray-100">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">
                        Berlaku Sampai
                    </label>

                    <input
                        id="approve_valid"
                        type="date"
                        readonly
                        class="w-full mt-1 rounded-xl border bg-gray-100">
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500">
                        Total
                    </label>

                    <input
                        id="approve_total"
                        type="text"
                        readonly
                        class="w-full mt-1 rounded-xl border bg-gray-100">
                </div>

            </div>

            <div>

                <h3 class="font-semibold mb-3">
                    Daftar Produk
                </h3>

                <table class="w-full border rounded-xl overflow-hidden">

                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 text-left">Produk</th>
                            <th class="p-3">Qty</th>
                            <th class="p-3">Harga</th>
                            <th class="p-3">Subtotal</th>
                        </tr>
                    </thead>

                    <tbody id="approve-items">

                    </tbody>

                </table>

                <div id="stock-result" class="hidden rounded-xl border p-4 bg-gray-50">

                </div>

            </div>

        </div>

        <div class="border-t px-8 py-5 flex justify-between items-center">

            <!-- Kiri -->
            <button
                type="button"
                onclick="checkStock()"
                class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">

                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2a4 4 0 014-4h8M5 7h14M5 12h14M5 17h2"/>
                </svg>

                Cek Ketersediaan Barang
            </button>

            <!-- Kanan -->
            <div class="flex gap-3">

                <button
                    type="button"
                    onclick="toggleModal('modal-approve',false)"
                    class="px-5 py-2 rounded-xl border hover:bg-gray-100">
                    Tutup
                </button>

                <form id="rejectForm" method="POST">
                    @csrf
                    @method('PUT')

                    <button
                        id="rejectBtn"
                        class="px-6 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                        disabled>
                        Reject
                    </button>

                </form>
                <form id="approveForm" method="POST">
                    @csrf
                    @method('PUT')

                    <button
                        id="approveBtn"
                        class="px-6 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed"
                        disabled>
                        Approve
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<!-- Modal Add -->
<div id="modal-add" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-2xl p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold mb-6">Buat Quotation Baru</h2>
        <form action="{{ route('quotations.store') }}" method="POST" class="space-y-4">
            @csrf
            
            <!-- Customer -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Customer</label>
                <input type="text" name="nama_customer" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <!-- Valid Until -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai</label>
                <input type="date" name="valid_until" required class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            
            <!-- Items -->
            <div id="items-container" class="space-y-3 border-t pt-4">
                <h3 class="font-semibold text-sm text-gray-900">Item</h3>
                
                <div class="flex gap-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <div class="w-1/2 px-1">Produk</div>
                    <div class="w-1/4 px-1">Qty</div>
                    <div class="w-1/4 px-1">Harga Satuan</div>
                </div>

                <!-- Baris Produk (Template) -->
                <div class="item-row flex gap-2">
                    <select name="items[0][inventory_id]" class="w-1/2 px-3 py-2 rounded-lg border border-gray-200">
                        @foreach($inventories as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->barang }}</option>
                        @endforeach
                    </select>
                    {{-- <input type="text" name="items[0][nama_barang]" placeholder="Nama Produk" class="w-1/2 px-3 py-2 rounded-lg border border-gray-200" required> --}}
                    <input type="number" name="items[0][quantity]" placeholder="Qty" class="w-1/4 px-3 py-2 rounded-lg border border-gray-200" required>
                    <input type="number" name="items[0][unit_price]" placeholder="Harga" class="w-1/4 px-3 py-2 rounded-lg border border-gray-200" required>
                </div>
            </div>

            <!-- Tombol Tambah Item -->
            <button type="button" onclick="addItemQuotation()" class="text-sm text-blue-600 font-medium hover:underline mt-2">
                + Tambah Produk Lain
            </button>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="toggleModal('modal-add', false)" class="px-6 py-2 text-gray-600 hover:text-gray-800">Batal</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modal-edit" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-2xl p-8 shadow-2xl max-h-[90vh] overflow-y-auto">

        <h2 class="text-xl font-bold mb-6">
            Edit Quotation
        </h2>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Nama Customer
                    </label>

                    <input
                        type="text"
                        name="nama_customer"
                        id="edit_nama_customer"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200"
                        required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Berlaku Sampai
                    </label>

                    <input
                        type="date"
                        name="valid_until"
                        id="edit_valid_until"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200"
                        required>
                </div>

                <div id="edit-items-container" class="space-y-3 border-t pt-4">
                    <h3 class="font-semibold">
                        Item Produk
                    </h3>
                </div>

                <button
                    type="button"
                    onclick="addEditItem()"
                    class="text-blue-600 text-sm">
                    + Tambah Produk
                </button>

            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button
                    type="button"
                    onclick="toggleModal('modal-edit',false)"
                    class="px-6 py-2">
                    Batal
                </button>

                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-xl">
                    Update
                </button>
            </div>

        </form>

    </div>
</div>

<!-- Modal Delete -->
<div id="modal-delete" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-sm p-8 text-center shadow-2xl">
        <h2 class="text-xl font-bold mb-4">Hapus Quotation?</h2>
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="w-full py-2 bg-red-600 text-white rounded-xl">Ya, Hapus</button>
        </form>
    </div>
</div>

{{-- Detail Plat --}}
<div id="platDetailModal"
     class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">


    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">


        <h2 class="text-lg font-bold mb-5">
            Detail Request Plat
        </h2>


        <div class="space-y-4">


            <div>
                <label class="text-sm text-gray-500">
                    Lokasi Plat
                </label>

                <p id="detail-lokasi"
                   class="font-medium">
                </p>
            </div>


            <div>
                <label class="text-sm text-gray-500">
                    Catatan
                </label>

                <p id="detail-catatan"
                   class="font-medium">
                </p>
            </div>


            <div>
                <label class="text-sm text-gray-500">
                    Approved At
                </label>

                <p id="detail-approved"
                   class="font-medium">
                </p>
            </div>


        </div>


        <div class="mt-6 text-right">

            <button
                onclick="closePlatDetailModal()"
                class="px-4 py-2 bg-gray-800 text-white rounded-lg">
                Tutup
            </button>

        </div>


    </div>

</div>

{{-- SPK Warehouse --}}
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
                Buat SPK Production
            </h2>
        </div>


        <div class="p-6">

            <input type="hidden" id="productionWarehouseSpkId">


            <div class="space-y-4">


                <div>
                    <label class="text-sm text-gray-500">
                        No SPK Warehouse
                    </label>

                    <div 
                        id="productionSpkNumber"
                        class="font-semibold">
                    </div>
                </div>



                <div>
                    <label class="text-sm text-gray-500">
                        Customer
                    </label>

                    <div 
                        id="productionCustomer"
                        class="font-semibold">
                    </div>
                </div>



                <div>
                    <label class="text-sm text-gray-500">
                        PIC Production
                    </label>


                    <select 
                        id="productionPic"
                        class="w-full border rounded-lg p-2 mt-1">

                        <option value="">
                            Pilih PIC
                        </option>

                    </select>

                </div>



                <div>

                    <label class="text-sm text-gray-500 font-medium">
                        Material / Barang
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
                        placeholder="Catatan Production">
                    </textarea>

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
                class="px-4 py-2 rounded-lg bg-blue-600 text-white">

                Buat SPK Production

            </button>


        </div>


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
    let itemCount = 1; // Counter untuk indeks berikutnya

    function addItemQuotation() {
        const container = document.getElementById('items-container');
        const firstRow = document.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);

        // Update nama input agar unik (misal: items[1][inventory_id])
        newRow.querySelectorAll('select, input').forEach(input => {
            input.name = input.name.replace(/\[\d+\]/, `[${itemCount}]`);
            input.value = ''; // Reset nilai input di baris baru
        });

        container.appendChild(newRow);
        itemCount++;
    }

    let editItemIndex = 0;

    function openEditModal(data) {

        document.getElementById('editForm').action =
            `/quotations/${data.id}`;

        document.getElementById('edit_nama_customer').value =
            data.nama_customer;

        document.getElementById('edit_valid_until').value =
            data.valid_until;

        let container = document.getElementById('edit-items-container');

        container.innerHTML = `
            <h3 class="font-semibold text-sm text-gray-900">
                Item Produk
            </h3>
        `;

        editItemIndex = 0;

        data.items.forEach(item => {

            container.insertAdjacentHTML('beforeend', `
                <div class="item-row flex gap-2">

                    <select
                        name="items[${editItemIndex}][inventory_id]"
                        class="w-1/2 px-3 py-2 rounded-lg border">

                        ${inventoryOptions(item.inventory.barang)}

                    </select>

                    <input
                        type="number"
                        name="items[${editItemIndex}][quantity]"
                        value="${item.quantity}"
                        class="w-1/4 px-3 py-2 rounded-lg border">

                    <input
                        type="number"
                        name="items[${editItemIndex}][unit_price]"
                        value="${item.unit_price}"
                        class="w-1/4 px-3 py-2 rounded-lg border">

                </div>
            `);

            editItemIndex++;
        });

        toggleModal('modal-edit', true);
    }

    function addEditItem() {

        let container = document.getElementById('edit-items-container');

        container.insertAdjacentHTML('beforeend', `

            <div class="item-row flex gap-2">

                <select
                    name="items[${editItemIndex}][inventory_id]"
                    class="w-1/2 px-3 py-2 rounded-lg border">

                     ${inventoryOptions()}

                </select>

                <input
                    type="number"
                    name="items[${editItemIndex}][quantity]"
                    placeholder="Qty"
                    class="w-1/4 px-3 py-2 rounded-lg border">

                <input
                    type="number"
                    name="items[${editItemIndex}][unit_price]"
                    placeholder="Harga"
                    class="w-1/4 px-3 py-2 rounded-lg border">

            </div>

        `);

        editItemIndex++;
    }
    let currentQuotationId = null;

    function openApproveModal(data) {

        currentQuotationId = data.id;

        document.getElementById('approveBtn').disabled = true;

        document.getElementById('stock-result').classList.add('hidden');

        document.getElementById('approve_number').value = data.quotation_number;
        document.getElementById('approve_customer').value = data.nama_customer;
        document.getElementById('approve_valid').value = data.valid_until;
        document.getElementById('approve_total').value =
            'Rp ' + Number(data.total_amount).toLocaleString('id-ID');

        document.getElementById('approveForm').action =
            `/quotations/approve/${data.id}`;

        document.getElementById('rejectForm').action =
            `/quotations/reject/${data.id}`;

        let tbody = document.getElementById('approve-items');

        tbody.innerHTML = '';

        data.items.forEach(item => {

            tbody.innerHTML += `
                <tr class="border-t">
                    <td class="p-3">${item.inventory.barang}</td>
                    <td class="p-3 text-center">${item.quantity}</td>
                    <td class="p-3 text-right">
                        Rp ${Number(item.unit_price).toLocaleString('id-ID')}
                    </td>
                    <td class="p-3 text-right">
                        Rp ${Number(item.subtotal).toLocaleString('id-ID')}
                    </td>
                </tr>
            `;

        });

        toggleModal('modal-approve', true);
    }

    function checkStock() {

        fetch('/quotations/check-stock', {

            method: 'POST',

            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },

            body: JSON.stringify({
                quotation_id: currentQuotationId
            })

        })

        .then(response => response.json())

        .then(res => {

            let result = document.getElementById('stock-result');

            result.classList.remove('hidden');

            if(res.success){

                result.innerHTML = `
                    <div class="p-3 rounded-xl bg-green-50 border border-green-200 text-green-700">
                        ✅ Semua barang tersedia.
                    </div>
                `;

                document.getElementById('approveBtn').disabled = false;
                document.getElementById('rejectBtn').disabled = false;

            }else{

                let html = `
                    <div class="p-3 rounded-xl bg-red-50 border border-red-200">

                        <p class="font-semibold text-red-700 mb-2">
                            Barang berikut tidak mencukupi:
                        </p>

                        <ul class="list-disc ml-5 text-red-600">
                `;

                res.items.forEach(item => {

                    html += `
                        <li>
                            ${item.barang}
                            (Dibutuhkan ${item.qty}, Stok Gudang ${item.stock})
                        </li>
                    `;

                });

                html += `
                        </ul>
                    </div>
                `;

                result.innerHTML = html;

                document.getElementById('approveBtn').disabled = true;
            }

        })

        .catch(() => {

            alert('Terjadi kesalahan saat mengecek stok.');

        });

    }
</script>
@endsection