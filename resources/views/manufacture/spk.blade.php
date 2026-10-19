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
    id="productionModal"
    class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <div class="bg-white rounded-xl w-full max-w-lg shadow-xl">

        <div class="border-b px-6 py-4">
            <h2 class="text-lg font-bold">
                Input Keterangan Barang Jadi & Waste
            </h2>
        </div>

        <div class="p-6">

            <input type="hidden" id="productionSpkId">

            <div class="space-y-4">

                <div>
                    <label class="text-sm text-gray-500">
                        Nomor SPK
                    </label>
                    <div id="modalSpkNumber" class="font-semibold"></div>
                </div>

                <div>
                    <label class="text-sm text-gray-500 font-medium">
                        Keterangan Barang Jadi
                    </label>
                    <textarea
                        id="barangJadiNote"
                        name="barang_jadi"
                        class="w-full border rounded-lg p-3 mt-1"
                        rows="3"
                        placeholder="Masukkan keterangan barang jadi..."></textarea>
                </div>

                <div>
                    <label class="text-sm text-gray-500 font-medium">
                        Waste (Sisa / Limbah)
                    </label>
                    <textarea
                        id="wasteNote"
                        name="waste"
                        class="w-full border rounded-lg p-3 mt-1"
                        rows="3"
                        placeholder="Masukkan keterangan atau jumlah waste..."></textarea>
                </div>

            </div>

        </div>

        <div class="border-t px-6 py-4 flex justify-end gap-2">

            <button
                onclick="closeProductionModal()"
                class="px-4 py-2 rounded-lg border hover:bg-gray-100 transition">
                Batal
            </button>

            <button
                onclick="submitProductionResult()"
                class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">
                Simpan Data
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
        const quotation = role === 'gudang' || role === 'akuntansi' || role === 'Akuntansi' || role === 'manufacture'
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
                <div class="flex justify-center items-center gap-2">
                    ${
                        item.status_spk == 0
                        ?
                        (
                            // Pengecekan jika warehouse atau production bernilai false
                            (!item.warehouse || !item.production)
                            ?
                            `
                                ${!item.warehouse ? '<span class="text-xs text-red-600 bg-red-50 px-2 py-1 rounded-md font-medium">Bahan belum Diterima</span>' : ''}
                                ${!item.production ? '<span class="text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded-md font-medium">SPK belum Diterima</span>' : ''}
                            `
                            :
                            `
                                <button
                                    onclick="acceptSpk(${item.id})"
                                    class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                    Terima SPK
                                </button>
                            `
                        )
                        :
                        item.status_spk == 1
                        ?
                        `
                            <button
                                onclick="openLaporanModal(${item.id})"
                                class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                Kirim Laporan
                            </button> 
                        `
                        :
                        item.status_spk == 2
                        ?
                        `
                            <span class="text-xs text-green-700 bg-green-50 px-3 py-2 rounded-lg font-semibold">
                                Laporan Telah Dikirim
                            </span>
                        `
                        :
                        ''
                    }
                </div>
            </td>

        </tr>

        `;

    });

}

function openLaporanModal(id) {
    // Cari data item berdasarkan ID jika Anda menyimpannya dalam variabel global/array data utama
    // Contoh: const item = globalDataList.find(d => d.id === id);
    
    // Set ID ke hidden input
    document.getElementById('productionSpkId').value = id;
    
    // Jika ingin menampilkan nomor SPK pada modal, Anda bisa isi di sini (opsional)
    // document.getElementById('modalSpkNumber').innerText = item ? item.nomor_spk : 'SPK #' + id;

    // Kosongkan form input sebelumnya
    document.getElementById('barangJadiNote').value = '';
    document.getElementById('wasteNote').value = '';

    // Tampilkan modal (menghapus class 'hidden' dan mengubah ke 'flex')
    const modal = document.getElementById('productionModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeProductionModal() {
    const modal = document.getElementById('productionModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

// Fungsi untuk menyimpan data laporan ke backend (menggunakan Fetch API Laravel)
function submitProductionResult() {
    const spkId = document.getElementById('productionSpkId').value;
    const barangJadi = document.getElementById('barangJadiNote').value;
    console.log(barangJadi);
    const waste = document.getElementById('wasteNote').value;

    // Validasi sederhana
    if (!barangJadi && !waste) {
        alert('Harap isi minimal salah satu keterangan (Barang Jadi atau Waste)!');
        return;
    }

    // Kirim data menggunakan AJAX/Fetch ke route Laravel Anda
    fetch(`/api/spk-manufacture/laporan/${spkId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            barang_jadi: barangJadi,
            waste: waste
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || data.message) {
            alert('Laporan berhasil disimpan!');
            closeProductionModal();
            window.location.reload();
            // Refresh tabel data Anda di sini (contoh: loadSpkData())
        } else {
            alert('Gagal menyimpan laporan.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alertTerjadiKesalahan();
    });
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
        let url = `/api/manufacture/spk/${id}/accept`;

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

loadSpk();

</script>

@endsection