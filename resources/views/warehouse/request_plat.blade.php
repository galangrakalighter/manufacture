@extends('layouts.app')

@section('title', 'Request Plat')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Request Plat
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                Daftar permintaan plat yang menunggu persetujuan gudang.
            </p>
        </div>

        <button
            onclick="loadRequests()"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">

            Refresh

        </button>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <div class="bg-white rounded-xl shadow border p-5">

            <p class="text-sm text-gray-500">
                Total Request
            </p>

            <h2
                id="total-request"
                class="text-3xl font-bold mt-2">

                0

            </h2>

        </div>

        <div class="bg-white rounded-xl shadow border p-5">

            <p class="text-sm text-gray-500">
                Menunggu Approval
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

                Daftar Request

            </h3>

        </div>

        <div class="overflow-x-auto">

            <table class="w-full">

                <thead class="bg-gray-50">

                    <tr class="text-left text-sm text-gray-600">

                        <th class="px-5 py-3">No</th>
                        <th class="px-5 py-3">Quotation</th>
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Request By</th>
                        <th class="px-5 py-3">Tanggal</th>
                        <th class="px-5 py-3">Status</th>
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

<div id="approveModal"
    class="fixed inset-0 bg-black/40 hidden z-50 flex items-center justify-center">

    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">

        <div class="px-6 py-4 border-b">
            <h2 class="text-lg font-semibold">
                Approve Request Plat
            </h2>
        </div>

        <div class="p-6 space-y-4">

            <input type="hidden" id="approve_id">

            <div>
                <label class="block text-sm font-medium mb-2">
                    Lokasi Plat <span class="text-red-500">*</span>
                </label>

                <input
                    id="lokasi_plat"
                    type="text"
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200"
                    placeholder="Contoh : Gudang A - Rak B3">
            </div>

            <div>

                <label class="block text-sm font-medium mb-2">
                    Catatan
                </label>

                <textarea
                    id="catatan"
                    rows="4"
                    class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-blue-200"
                    placeholder="Catatan tambahan..."></textarea>

            </div>

        </div>

        <div class="px-6 py-4 border-t flex justify-end gap-3">

            <button
                onclick="closeApproveModal()"
                class="px-4 py-2 rounded-lg border">

                Batal

            </button>

            <button
                onclick="submitApprove()"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">

                Approve

            </button>

        </div>

    </div>

</div>

<script>

async function loadRequests(){

    const tbody=document.getElementById('request-table');

    tbody.innerHTML=`
        <tr>
            <td colspan="7" class="text-center py-8">
                Memuat...
            </td>
        </tr>
    `;

    const response=await fetch('/api/gudang/requests-all');

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

        let status='';

        if(item.status==0){

            status=`
                <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs">
                    Menunggu
                </span>
            `;

        }else if(item.status==1){

            status=`
                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs">
                    Disetujui
                </span>
            `;

        }else{

            status=`
                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs">
                    Ditolak
                </span>
            `;

        }

        tbody.innerHTML+=`

        <tr class="border-b hover:bg-gray-50">

            <td class="px-5 py-4">

                ${index+1}

            </td>

            <td class="px-5 py-4 font-medium">

                ${item.quotation.quotation_number}

            </td>

            <td class="px-5 py-4">

                ${item.quotation.nama_customer}

            </td>

            <td class="px-5 py-4">

                ${item.requester.name}

            </td>

            <td class="px-5 py-4">

                ${item.created_at}

            </td>

            <td class="px-5 py-4">

                ${status}

            </td>

            <td class="px-5 py-4">

                <div class="flex justify-center gap-2">

                    ${
                        item.status == 0 
                        ?
                        `
                        <button
                            onclick="openApproveModal(${item.id})"
                            class="px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">

                            Approve

                        </button>
                        `
                        :
                        `
                        <span class="px-3 py-2 text-gray-400 text-sm">
                            Sudah Approved
                        </span>
                        `
                    }

                </div>

            </td>

        </tr>

        `;

    });

}

async function approve(id){

    if(!confirm('Approve request ini?')) return;

    await fetch('/gudang/request/'+id+'/approve',{

        method:'POST',

        headers:{
            'X-CSRF-TOKEN':document
                .querySelector('meta[name="csrf-token"]')
                .content
        }

    });

    loadRequests();

}

async function reject(id){

    if(!confirm('Tolak request ini?')) return;

    await fetch('/gudang/request/'+id+'/reject',{

        method:'POST',

        headers:{
            'X-CSRF-TOKEN':document
                .querySelector('meta[name="csrf-token"]')
                .content
        }

    });

    loadRequests();

}

loadRequests();

function openApproveModal(id){

        document.getElementById('approve_id').value=id;

        document.getElementById('lokasi_plat').value='';

        document.getElementById('catatan').value='';

        document.getElementById('approveModal')
            .classList.remove('hidden');

    }

    function closeApproveModal(){

        document.getElementById('approveModal')
            .classList.add('hidden');

    }

    async function submitApprove(){

        const id=document.getElementById('approve_id').value;

        const lokasi=document.getElementById('lokasi_plat').value.trim();

        const catatan=document.getElementById('catatan').value;

        console.log(id);

        if(lokasi===""){

            alert("Lokasi plat wajib diisi.");

            return;

        }

        const response=await fetch('/gudang/request/'+id+'/approve',{

            method:'POST',

            headers:{
                'Content-Type':'application/json',

                'X-CSRF-TOKEN':document
                    .querySelector('meta[name="csrf-token"]')
                    .content
            },

            body:JSON.stringify({

                lokasi_plat:lokasi,

                catatan:catatan

            })

        });

        const result=await response.json();

        console.log(response.status);

        alert(result.status);

        closeApproveModal();

        loadRequests();

    }

</script>

@endsection