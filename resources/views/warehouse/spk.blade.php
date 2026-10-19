@extends('layouts.app')

@section('title', 'Request Plat')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                SPK
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Daftar SPK.
            </p>
        </div>

        <button
            onclick="loadSpk()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">

            Refresh

        </button>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <div class="bg-white rounded-xl shadow border p-5">

            <p class="text-sm text-gray-500">
                Total SPK
            </p>

            <h2
                id="total-request"
                class="text-3xl font-bold mt-2">

                0

            </h2>

        </div>

        <div class="bg-white rounded-xl shadow border p-5">

            <p class="text-sm text-gray-500">
                Draft
            </p>

            <h2
                id="waiting-request"
                class="text-3xl font-bold text-yellow-500 mt-2">

                0

            </h2>

        </div>

        <div class="bg-white rounded-xl shadow border p-5">

            <p class="text-sm text-gray-500">
                Selesai
            </p>

            <h2
                id="approved-request"
                class="text-3xl font-bold text-green-600 mt-2">

                0

            </h2>

        </div>

    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow border overflow-hidden">

        <div class="px-6 py-4 border-b">

            <h3 class="font-semibold">

                Daftar SPK

            </h3>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-gray-50">

                    <tr class="text-left text-sm text-gray-600">

                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">SPK</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3 text-center">Action</th>

                    </tr>

                </thead>

                <tbody
                    id="request-table">

                    <tr>

                        <td colspan="7"
                            class="text-center py-10 text-gray-400">

                            Memuat data...

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

<div
    id="paymentModal"
    class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <div class="bg-white rounded-xl w-full max-w-lg shadow-xl">

        <div class="border-b px-6 py-4">
            <h2 class="text-lg font-bold">
                Terima Pembayaran dari Customer
            </h2>
        </div>

        <div class="p-6">

            <input type="hidden" id="paymentQuotationId">
            <input type="hidden" id="cabangUser">

            <div class="space-y-4">

                <div>
                    <label class="text-sm text-gray-500">
                        Invoice
                    </label>
                    <div id="paymentQuotationNumber" class="font-semibold"></div>
                </div>

                <div>
                    <label class="text-sm text-gray-500">
                        Customer
                    </label>
                    <div id="paymentCustomer" class="font-semibold"></div>
                </div>

                <div>
                    <label class="text-sm text-gray-500">
                        Total Tagihan
                    </label>
                    <div id="paymentTotalAmount" class="text-xl font-bold text-green-600"></div>
                </div>

                <div>
                    <label class="text-sm text-gray-500 font-medium">
                        Jumlah Dibayar
                    </label>
                    <input
                        type="text"
                        id="paymentAmount"
                        name="amount"
                        class="w-full border rounded-lg p-3 mt-1 font-semibold"
                        placeholder="Masukkan nominal pembayaran" oninput="formatRupiah(this)">
                </div>

                <div>
                    <label class="text-sm text-gray-500">
                        Catatan / Keterangan Pembayaran
                    </label>
                    <textarea
                        id="paymentNote"
                        class="w-full border rounded-lg p-3 mt-1"
                        name="note"
                        rows="3"
                        placeholder="Contoh: Transfer via BCA / Lunas (opsional)"></textarea>
                </div>

            </div>

        </div>

        <div class="border-t px-6 py-4 flex justify-end gap-2">

            <button
                onclick="closePaymentModal()"
                class="px-4 py-2 rounded-lg border hover:bg-gray-100 transition">
                Batal
            </button>

            <button
                onclick="submitPayment()"
                class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 transition">
                Simpan Pembayaran
            </button>

        </div>

    </div>

</div>

<script>
const role = "{{ Auth::user()->role }}";
async function loadSpk(){

    const tbody=document.getElementById('request-table');

    tbody.innerHTML=`
        <tr>
            <td colspan="7" class="text-center py-8">
                Memuat...
            </td>
        </tr>
    `;

    const response=await fetch('/api/gudang/spk-all');

    const data=await response.json();

    console.log(data);

    document.getElementById('total-request').innerText=data.length;

    document.getElementById('waiting-request').innerText=
        data.filter(x=>x.status==0).length;

    document.getElementById('approved-request').innerText=
        data.filter(x=>x.status==1).length;

    if(data.length==0){

        tbody.innerHTML=`
            <tr>
                <td colspan="7"
                    class="text-center py-10 text-gray-400">
                    Tidak ada request.
                </td>
            </tr>
        `;

        return;
    }

    tbody.innerHTML='';

    data.forEach((item,index)=>{
        const quotation = role === 'gudang' || role === 'akuntansi' || role === 'Akuntansi'
            ? item.quotation
            : item.warehouse.quotation;

        let status='';

        if(item.status==0){
            status=`
                <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs">
                    Draft
                </span>
            `;
        }else if(item.status==1){
            status=`
                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs">
                    Disiapkan
                </span>
            `;
        }

        // Variabel pengecekan role akuntansi
        const isAkuntansi = role === 'akuntansi' || role === 'Akuntansi';
        const isProduction = role === 'production' || role === 'Production';
        const isGudang = role === 'gudang' || role === 'Gudang';

        tbody.innerHTML+=`

        <tr class="border-b hover:bg-gray-50">

            <td class="px-5 py-4">

                ${index+1}

            </td>

            <td class="px-5 py-4 font-medium">

                ${item.spk_number}

            </td>

            <td class="px-5 py-4">

                ${quotation.nama_customer ?? '-'}

            </td>

            <td class="px-5 py-4">
                ${new Date(item.created_at).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'long',
                    year: 'numeric'
                })}
            </td>

            <td class="px-5 py-4">

                <div class="flex justify-center gap-2">

                    ${
                        item.status_spk == 0
                        ?
                        `
                            <button
                                onclick="openSpkDetail(${item.id})"
                                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">

                                Detail SPK

                            </button>

                            <button
                                onclick="acceptSpk(${item.id})"
                                class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">

                                Terima SPK

                            </button>
                        `
                        :
                        item.status_spk == 1
                        ?
                        `
                            <button
                                onclick="openSpkDetail(${item.id})"
                                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">

                                Detail SPK

                            </button>
                            
                            ${
                                isProduction || isGudang
                                ? `
                                    <button
                                        onclick="${isProduction ? `kirimSpk(${item.id})` : `kirimBarang(${item.id})`}"
                                        class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">
                                        ${isProduction ? 'Kirim SPK' : 'Kirim Barang'}
                                    </button>
                                `
                                : ''
                            }

                            ${
                                isAkuntansi 
                                ? `
                                    <button
                                        onclick="terimaBayaran(${item.id})"
                                        class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">

                                        Terima Pembayaran

                                    </button>
                                    <button
                                        onclick="cetakInvoice(${item.id})"
                                        class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">

                                        Cetak Invoice

                                    </button>
                                ` 
                                : ''
                            }
                        `
                        :
                        `
                            <button
                                onclick="openSpkDetail(${item.id})"
                                class="px-3 py-2 border border-gray-300 rounded-lg hover:bg-gray-100 transition">

                                Detail SPK

                            </button>
                        `
                    }

                </div>

            </td>

        </tr>

        `;

    });

}

async function terimaBayaran(id) {
    console.log(id);
    try {
        // Fetch detail SPK/Quotation berdasarkan ID untuk ditampilkan ke modal
        const response = await fetch(`/api/finance/spk-detail/${id}`);
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Gagal mengambil data pembayaran.');
        }

        const data = result.data; // Sesuaikan dengan struktur response backend Anda
        console.log(data);
        const quotation = data.quotation;
        const totalHarga = parseInt(quotation.total_amount);

        // Masukkan data ke elemen modal
        document.getElementById('paymentQuotationId').value = data.id;
        document.getElementById('paymentQuotationNumber').innerText = data.no_invoice;
        document.getElementById('paymentCustomer').innerText = quotation.nama_customer;
        document.getElementById('paymentTotalAmount').innerText = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(totalHarga);
        
        // Reset input
        document.getElementById('paymentAmount').value = totalHarga; // Default lunas
        document.getElementById('paymentNote').value = '';

        // Tampilkan modal
        document.getElementById('paymentModal').classList.remove('hidden');
        document.getElementById('paymentModal').classList.add('flex');

    } catch (err) {
        alert(err.message);
    }
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.remove('flex');
    document.getElementById('paymentModal').classList.add('hidden');
}

async function submitPayment() {
    const id = document.getElementById('paymentQuotationId').value;
    const cabang = document.getElementById('cabangUser').value;
    const amount = document.getElementById('paymentAmount').value;
    const note = document.getElementById('paymentNote').value;

    try {
        const response = await fetch(`/api/finance/payment/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                amount: amount,
                note: note,
                cabang: cabang
            })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message);
        }

        closePaymentModal();
        location.reload(); // Atau panggil ulang fungsi load data tabel Anda
        alert('Pembayaran berhasil diterima.');

    } catch (err) {
        alert(err.message);
    }
}

function cetakInvoice(id){
    window.open(`/invoice/spk/${id}/pdf`, '_blank');
}

function openSpkDetail(id) {

    if(role === 'gudang'){
        window.open(`/gudang/spk/${id}/pdf`, '_blank');
    }else if(role === 'akuntansi'){
        window.open(`/finance/spk/${id}/pdf`, '_blank');
    }else{
        window.open(`/production/spk/${id}/pdf`, '_blank');
    }

}

async function acceptSpk(id){

    if(!confirm('Terima SPK ini?')){
        return;
    }

    try{
        let url = '';
        if (role === 'gudang') {
            url = `/api/gudang/spk/${id}/accept`;
        } else if (role === 'akuntansi') {
            url = `/api/finance/spk/${id}/accept`;
        } else {
            url = `/api/production/spk/${id}/accept`;
        }

        const response = await fetch(url,{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':
                document.querySelector(
                    'meta[name="csrf-token"]'
                ).content
            }
        });

        const result = await response.json();

        if(!response.ok){
            throw new Error(result.message);
        }

        alert(result.message);

        window.location.reload();

    }catch(err){

        alert(err.message);

    }

}

async function kirimSpk(id){

    if(!confirm('Terima SPK ini?')){
        return;
    }

    try{
        let url = `/api/production/spk/${id}/send`;

        const response = await fetch(url,{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':
                document.querySelector(
                    'meta[name="csrf-token"]'
                ).content
            }
        });

        const result = await response.json();

        if(!response.ok){
            throw new Error(result.message);
        }

        alert(result.message);

        window.location.reload();

    }catch(err){

        alert(err.message);

    }

}

async function kirimBarang(id){

    if(!confirm('Terima SPK ini?')){
        return;
    }

    try{
        let url = `/api/gudang/bahan/${id}/send`;

        const response = await fetch(url,{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':
                document.querySelector(
                    'meta[name="csrf-token"]'
                ).content
            }
        });

        const result = await response.json();

        if(!response.ok){
            throw new Error(result.message);
        }

        alert(result.message);

        window.location.reload();

    }catch(err){

        alert(err.message);

    }

}

async function cancelSpk(id){

    if(!confirm('Batalkan pengerjaan SPK ini?')){
        return;
    }

    try{

        const url = role === 'gudang'
            ? `/api/gudang/spk/${id}/cancel`
            : `/api/production/spk/${id}/cancel`;

        const response = await fetch(url,{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':
                document.querySelector(
                    'meta[name="csrf-token"]'
                ).content
            }
        });

        const result = await response.json();

        alert(result.message);

        window.location.reload();

    }catch(e){

        alert('Gagal membatalkan pengerjaan.');

    }

}

loadSpk();

</script>

@endsection