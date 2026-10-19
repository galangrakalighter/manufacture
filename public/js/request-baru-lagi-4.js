const API_BASE_URL = "https://demoklien.lightermediagroup.com";
const role = document.getElementById('role').value;
const token = document.querySelector('meta[name="csrf-token"]').content;
async function requestPlat(id) {
    try {

        const response = await fetch(`${API_BASE_URL}/plat/request`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                plat_id: id,
                timestamp: new Date().toISOString()
            })
        });

        if (!response.ok) {
            throw new Error(`Gagal meminta plat: ${response.statusText}`);
        }

        const data = await response.json();
       showNotification("Berhasil Meminta Plat " + data.data.quotation_number);
        
        // Refresh tabel saja
        await loadQuotations();
    } catch (error) {
        console.error('Error saat requestPlat:', error);
        throw error;
    }
}

function showNotification(message, type = 'success') {
    const container = document.getElementById('notification-area'); // Pastikan ID ini ada di HTML
    const div = document.createElement('div');
    
    // Styling mengikuti tema Laravel (menggunakan Tailwind)
    const colorClass = type === 'success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200';
    
    div.className = `${colorClass} p-4 rounded-xl border mb-4 animate-in fade-in slide-in-from-top-2 duration-300`;
    div.innerText = message;
    
    container.appendChild(div);
    
    // Hapus pesan otomatis setelah 3 detik
    setTimeout(() => {
        div.remove();
    }, 1500);
}

async function cancelPlat(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/plat/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                plat_id: id,
                timestamp: new Date().toISOString()
            })
        });

        if (!response.ok) {
            throw new Error(`Gagal membatalkan plat: ${response.statusText}`);
        }

        const data = await response.json();
        showNotification(`Permintaan Plat ${data.data.quotation_number} Berhasil Di Batalkan`);
        
        // Refresh tabel saja
        await loadQuotations();
    } catch (error) {
        console.error('Error saat cancelPlat:', error);
        throw error;
    }
}

let quotations = [];

async function loadQuotations() {
    let cabang = document.getElementById('cabang').value;
    const response = await fetch('/api/quotations/' + cabang);

    quotations = await response.json();

    console.log(quotations);

    renderQuotationTable();
}

function renderQuotationTable() {

    const tbody = document.getElementById('quotation-table');

    tbody.innerHTML = '';

    quotations.forEach((q, index) => {

        tbody.innerHTML += `
            <tr class="hover:bg-gray-50">

                <td class="px-6 py-4">${index + 1}</td>

                <td class="px-6 py-4">
                    ${q.quotation_number}
                </td>

                <td class="px-6 py-4">
                    ${q.nama_customer}
                </td>

                <td class="px-6 py-4">
                    ${q.keterangan_reject ? q.keterangan_reject : ''}
                </td>
                <td class="px-6 py-4">
                    Rp ${Number(q.total_amount).toLocaleString('id-ID')}
                </td>

                <td class="px-6 py-4 text-center">
                    ${renderAction(q, index)}
                </td>

            </tr>
        `;
    });

}

function openKirimModal(id, quotationNumber) {
    const modal = document.getElementById('modal-kirim');
    const form = document.getElementById('form-kirim');
    const textSpan = document.getElementById('quotation-number-text');

    // Ubah action form sesuai dengan ID quotation, contoh: /quotations/{id}/kirim
    form.action = `/quotations/${id}/kirim`;
    
    // Tampilkan nomor quotation pada teks konfirmasi
    textSpan.textContent = quotationNumber;

    // Tampilkan modal
    modal.classList.remove('hidden');
}

function closeKirimModal() {
    const modal = document.getElementById('modal-kirim');
    modal.classList.add('hidden');
}

function renderAction(q, index) {
    if (role === 'sales') {
        if(q.status == 0){
            return `
                <button
                    onclick="openModalEdit(quotations[${index}])"
                    class="text-blue-600 hover:text-blue-800">
                    Edit
                </button>
    
                <button
                    onclick="openDeleteModal('/quotations/${q.id}')"
                    class="text-red-600 hover:text-red-800">
                    Delete
                </button>

                <button
                    onclick="openKirimModal(${q.id}, '${q.quotation_number}')"
                    class="text-red-600 hover:text-red-800">
                    Kirim
                </button>

                <a
                    href="/quotations/${q.id}/pdf" 
                    target="_blank"
                    class="text-green-600 hover:text-green-800 inline-block">
                    Buka Dokumen
                </a>
            `;
        }else if(q.status == 1){
            return `
                <span class="text-gray-400 text-sm italic">
                    Menunggu Approval
                </span>
                <a href="/quotations/${q.id}/pdf" 
                    target="_blank"
                    class="text-green-600 hover:text-green-800 inline-block">
                    Buka Dokumen
                </a>
            `;
        }else if(q.status == 2){
            return `
                <button
                    onclick="openModalEdit(quotations[${index}])"
                    class="text-blue-600 hover:text-blue-800">
                    Edit
                </button>
    
                <button
                    onclick="openDeleteModal('/quotations/${q.id}')"
                    class="text-red-600 hover:text-red-800">
                    Delete
                </button>

                <button
                    onclick="openKirimModal(${q.id}, '${q.quotation_number}')"
                    class="text-red-600 hover:text-red-800">
                    Kirim
                </button>

                <a href="/quotations/${q.id}/pdf" 
                    target="_blank"
                    class="text-green-600 hover:text-green-800 inline-block">
                    Buka Dokumen
                </a>
            `;
        }else{
            return `
                <span class="text-gray-400 text-sm italic">
                    Sudah Di Approve
                </span>
                <a href="/quotations/${q.id}/pdf" 
                    target="_blank"
                    class="text-green-600 hover:text-green-800 inline-block">
                    Buka Dokumen
                </a>
            `;
        }
    }

    if (role === 'pricing'){
        if(q.status == 1){
            return `
                <button
                    onclick="openApproveModal(quotations[${index}])"
                    class="text-blue-600 hover:text-blue-800">
                    Detail
                </button>
            `;
        }else if(q.status == 2){
            return `
                <span class="text-gray-400 text-sm italic">
                    Di Tolak
                </span>
            `;
        }else{
            return `
                <span class="text-gray-400 text-sm italic">
                    Sudah Di Approve
                </span>
            `;
        }
    }

    if (role === 'pracetak') {

        if (q.status == 0) {
            return `
                <span class="text-gray-400 text-sm italic">
                    Belum Di-approve
                </span>
            `;
        }

        if (q.status == 1) {
            return `
                <span class="text-gray-400 text-sm italic">
                    Menunggu Approval
                </span>
            `;
        }

        if (q.status == 2) {
            return `
                <span class="text-gray-400 text-sm italic">
                    Ditolak
                </span>
            `;
        }

        if (q.status == 4) {
            return `
                <button
                    onclick="requestPlat(${q.id})"
                    class="px-4 py-2 border-2 border-blue-600 text-blue-600 text-sm font-bold rounded-full hover:bg-blue-50 transition-all duration-200">
                    Request Plat
                </button>
            `;
        }

        if (q.status == 5) {
            return `
                <button
                    onclick="cancelPlat(${q.id})"
                    class="px-4 py-2 border border-red-200 text-red-500 text-sm font-medium rounded-lg hover:bg-red-50 hover:border-red-300 transition-all duration-200">
                    Cancel Request
                </button>
            `;
        }

        if (q.status == 6) {
            return `
                <button
                    onclick="openPlatDetailModal(quotations[${index}])"
                    class="px-3 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50">
                    Detail Plat
                </button>
            `;
        }
    }

    if(role == 'penjualan'){
        if(q.status == 6){
            return `
            ${
                q.spk_warehouse == null
                ?
                `
                <button
                    onclick="openWarehouseModal(quotations[${index}])"
                    class="px-3 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
                    SPK Warehouse
                </button>
                `
                :
                `
                <button
                    onclick="openProductionModal(quotations[${index}])"
                    class="px-3 py-2 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50">
                    SPK Production
                </button>
                `
            }
            <button
                onclick="openFinanceModal(quotations[${index}])"
                class="px-3 py-2 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50">
                SPK Finance
            </button>
            ${
                q.spk_finance != null && q.laporan != null
                ?
                `
                <button
                    onclick="cetakSuratJalan(${q.spk_finance.id})"
                    class="px-3 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50">
                    Cetak Surat Jalan
                </button>
                `
                :
                ''
            }
            `
        }else if(q.status == 7){
            console.log(q);
            return `
                ${
                    q.spk_warehouse == null
                    ?
                    `
                    <button
                        onclick="openWarehouseModal(quotations[${index}])"
                        class="px-3 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50">
                        SPK Warehouse
                    </button>
                    `
                    :
                    `
                    <button
                        onclick="openProductionModal(quotations[${index}])"
                        class="px-3 py-2 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50">
                        SPK Production
                    </button>
                    `
                }
                <button
                    onclick="openFinanceModal(quotations[${index}])"
                    class="px-3 py-2 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50">
                    SPK Finance
                </button>
                ${
                    q.spk_finance != null && q.laporan != null
                    ?
                    `
                    <button
                        onclick="cetakSuratJalan(${q.spk_finance.id})"
                        class="px-3 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50">
                        Cetak Surat Jalan
                    </button>
                    `
                    :
                    ''
                }
            `;
        }else if(q.status == 3){
            return `
                <form action="/quotations/${q.id}/approve" method="POST" class="inline">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
                    <input type="hidden" name="_method" value="PUT">
                    <button type="submit" class="text-blue-600 hover:text-blue-800">
                        Approve
                    </button>
                </form>
            `;
        }else if(q.status > 3){
            return `
                <span class="text-gray-400 text-sm italic">
                    Sudah Approve
                </span>
            `;
        }else{
            return `
                <span class="text-gray-400 text-sm italic">
                    Tidak Ada Akses
                </span>
            `;
        }
    }

    return `
        <span class="text-gray-400 text-sm italic">
            Tidak Ada Akses
        </span>
    `;
}

// async function deleteFilm(id) {
//     try {
//         const response = await fetch(`/quotations/${id}/delete-film`, {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
//                 'Accept': 'application/json'
//             },
//             credentials: 'same-origin',
//             body: JSON.stringify({
//                 timestamp: new Date().toISOString()
//             })
//         });

//         const data = await response.json();

//         if (!response.ok) {
//             throw new Error(data.message || `Gagal menghapus film: ${response.statusText}`);
//         }

//         // Menampilkan notifikasi sukses (sesuaikan dengan fungsi showNotification Anda)
//         showNotification("Berhasil Menghapus Nomor Film");
        
//         // Refresh tabel
//         await loadQuotations(); // atau location.reload();
//     } catch (error) {
//         console.error('Error saat deleteFilm:', error);
//         alert(error.message);
//         throw error;
//     }
// }

// async function generateFilm(id) {
//     try {
//         const response = await fetch(`/quotations/${id}/generate-film`, {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
//                 'Accept': 'application/json'
//             },
//             credentials: 'same-origin',
//             body: JSON.stringify({
//                 timestamp: new Date().toISOString()
//             })
//         });

//         const data = await response.json();

//         if (!response.ok) {
//             throw new Error(data.message || `Gagal generate film: ${response.statusText}`);
//         }

//         // Menampilkan notifikasi sukses (sesuaikan fungsi showNotification dengan yang Anda punya)
//         showNotification("Berhasil Generate No Film: " + data.data.film);
        
//         // Refresh tabel / reload halaman
//         location.reload(); 
//     } catch (error) {
//         console.error('Error saat generateFilm:', error);
//         // Anda bisa mengganti alert ini dengan fungsi showNotification versi error jika ada
//         alert(error.message); 
//         throw error;
//     }
// }

function cetakSuratJalan(id) {
    if (!id) {
        alert('ID SPK Finance tidak ditemukan.');
        return;
    }
    // Mengarahkan ke route Laravel untuk download/stream PDF surat jalan
    window.open(`/finance/surat-jalan-pdf/${id}`, '_blank');
}

function openWarehouseModal(q){

    console.log(q);

    document.getElementById('warehouseQuotationId').value = q.id;

    document.getElementById('warehouseQuotationNumber').innerText =
        q.quotation_number;

    document.getElementById('warehouseCustomer').innerText =
        q.nama_customer;

    document.getElementById('warehouseNote').value = '';

    let html = `
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">
                        Barang
                    </th>
                    <th class="px-3 py-2 text-center">
                        Jumlah
                    </th>
                    <th class="px-3 py-2 text-center">
                        Isi Per Box
                    </th>
                    <th class="px-3 py-2 text-center">
                        Jumlah Box
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t">
                    <td class="px-3 py-2">
                        <div class="font-medium">
                            ${q.barang?.barang ?? '-'}
                        </div>
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${q.quantity ?? '-'}
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${q.perbox ?? '-'}
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${q.jumlah_box ?? '-'}
                    </td>
                </tr>
            </tbody>
        </table>
    `;

    document.getElementById('warehouseItems').innerHTML = html;

    document
        .getElementById('warehouseModal')
        .classList.remove('hidden');

    document
        .getElementById('warehouseModal')
        .classList.add('flex');
}

function closeWarehouseModal(){

    const modal = document.getElementById('warehouseModal');

    modal.classList.remove('flex');
    modal.classList.add('hidden');

}

function openProductionModal(spk) {

    console.log(spk);

    document.getElementById('productionWarehouseSpkId').value = spk.id;

    document.getElementById('productionSpkNumber').innerText = 
        spk.spk_warehouse?.spk_number ?? '-';

    document.getElementById('productionCustomer').innerText = 
        spk.nama_customer ?? '-';

    document.getElementById('productionNote').value = '';

    let html = `
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">
                        Barang
                    </th>
                    <th class="px-3 py-2 text-center">
                        Jumlah
                    </th>
                    <th class="px-3 py-2 text-center">
                        Satuan
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t">
                    <td class="px-3 py-2">
                        <div class="font-medium">
                            ${spk.barang?.barang ?? '-'}
                        </div>
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${spk.barang?.jumlah ?? '-'}
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${spk.barang?.satuan ?? '-'}
                    </td>
                </tr>
            </tbody>
        </table>
    `;

    document.getElementById('productionItems').innerHTML = html;

    loadProductionPIC();

    document
        .getElementById('productionModal')
        .classList.remove('hidden');

    document
        .getElementById('productionModal')
        .classList.add('flex');
}

function openFinanceModal(q) {
    console.log(q);

    document.getElementById('financeQuotationId').value = q.id;

    document.getElementById('financeQuotationNumber').innerText =
        q.quotation_number;

    document.getElementById('financeCustomer').innerText =
        q.nama_customer;

    document.getElementById('financeNote').value = '';

    // Helper untuk format rupiah (sesuaikan jika di backend sudah diformat)
    const formatRupiah = (angka) => {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka || 0);
    };

    // Asumsi properti harga satuan ada di q.harga atau q.price, dan total harga di q.total_harga / q.total
    const hargaPerbox = q.harga ?? q.price ?? 0;
    const jumlah = q.quantity ?? 0;
    const totalHarga = q.total_harga ?? (hargaPerbox * jumlah);

    let html = `
        <table class="w-full text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-3 py-2 text-left">Barang</th>
                    <th class="px-3 py-2 text-center">Jumlah</th>
                    <th class="px-3 py-2 text-right">Harga Perbox</th>
                    <th class="px-3 py-2 text-right">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-t">
                    <td class="px-3 py-2">
                        <div class="font-medium">
                            ${q.barang?.barang ?? '-'}
                        </div>
                    </td>
                    <td class="px-3 py-2 text-center">
                        ${jumlah}
                    </td>
                    <td class="px-3 py-2 text-right">
                        ${formatRupiah(hargaPerbox)}
                    </td>
                    <td class="px-3 py-2 text-right font-semibold">
                        ${formatRupiah(totalHarga)}
                    </td>
                </tr>
            </tbody>
            <tfoot class="bg-gray-50 border-t font-bold">
                <tr>
                    <td colspan="3" class="px-3 py-2 text-right">Grand Total:</td>
                    <td class="px-3 py-2 text-right text-purple-600">
                        ${formatRupiah(totalHarga)}
                    </td>
                </tr>
            </tfoot>
        </table>
    `;

    document.getElementById('financeItems').innerHTML = html;

    document
        .getElementById('financeModal')
        .classList.remove('hidden');

    document
        .getElementById('financeModal')
        .classList.add('flex');
}

function closeFinanceModal() {
    document.getElementById('financeModal').classList.remove('flex');
    document.getElementById('financeModal').classList.add('hidden');
}

async function loadProductionPIC(){

    const cabang = document.getElementById('cabang').value;
    console.log(cabang);

    const response = await fetch(
        `/api/users/production?cabang=${cabang}`
    );


    const data = await response.json();



    let html = `
        <option value="">
            Pilih PIC
        </option>
    `;



    data.forEach(user => {

        html += `
            <option value="${user.id}">
                ${user.name}
            </option>
        `;

    });



    document.getElementById(
        'productionPic'
    ).innerHTML = html;

}

function closeProductionModal(){

    const modal = document.getElementById('productionModal');

    modal.classList.remove('flex');
    modal.classList.add('hidden');

}

async function sendSpkProduction(){


    const id =
    document.getElementById(
        'productionWarehouseSpkId'
    ).value;



    const pic =
    document.getElementById(
        'productionPic'
    ).value;



    const note =
    document.getElementById(
        'productionNote'
    ).value;



    if(!pic){

        alert('Pilih PIC');

        return;

    }



    const response = await fetch(
        `/api/production/spk/${id}`,
        {

        method:'POST',

        headers:{

            'Content-Type':'application/json',

            'X-CSRF-TOKEN':
            document.querySelector(
                'meta[name="csrf-token"]'
            ).content

        },


        body:JSON.stringify({

            pic_production_id:pic,

            note:note

        })


    });



    const result = await response.json();



    if(!response.ok){

        alert(result.message);

        return;

    }



    closeProductionModal();


    loadSpk();


    alert(
        'SPK Production berhasil dibuat'
    );

}

async function sendSpkWarehouse(){

    const id = document.getElementById('warehouseQuotationId').value;

    const note = document.getElementById('warehouseNote').value;

    try{
        let cabang = document.getElementById('cabang').value;
        const response = await fetch(`/api/warehouse/spk/${id}`,{

            method:'POST',
            
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':document
                    .querySelector('meta[name="csrf-token"]').content
            },

            body:JSON.stringify({
                note:note,
                cabang: cabang
            })

        });

        const result = await response.json();

        console.log(result);

        if(!response.ok){
            throw new Error(result.message);
        }

        closeWarehouseModal();

        loadQuotations();

        alert('SPK berhasil dikirim ke Warehouse.');

    }catch(err){

        alert(err.message);

    }

}

async function sendSpkFinance(){

    console.log("masuk sini");

    const id = document.getElementById('financeQuotationId').value;

    const note = document.getElementById('financeNote').value;

    try{
        let cabang = document.getElementById('cabang').value;
        const response = await fetch(`/api/finance/spk/${id}`,{

            method:'POST',
            
            headers:{
                'Content-Type':'application/json',
                'X-CSRF-TOKEN':document
                    .querySelector('meta[name="csrf-token"]').content
            },

            body:JSON.stringify({
                note: note,
                cabang: cabang
            })

        });

        const result = await response.json();

        console.log(result);

        if(!response.ok){
            throw new Error(result.message);
        }

        closeFinanceModal();

        loadQuotations();

        alert('SPK berhasil dikirim ke Finance.');

    }catch(err){

        alert(err.message);

    }

}

function closePlatDetailModal(){

    document.getElementById('platDetailModal').classList.add('hidden');

}

function openPlatDetailModal(q){

    const plat = q.request_plat;


    document.getElementById('detail-lokasi').innerText =
        plat?.lokasi_plat ?? '-';


    document.getElementById('detail-catatan').innerText =
        plat?.catatan ?? '-';


    document.getElementById('detail-approved').innerText =
        plat?.approved_at ?? '-';


    document.getElementById('platDetailModal').classList.remove('hidden');

}

let inventories = [];

async function loadInventories(){
    let cabang = document.getElementById('cabang').value;
    const response = await fetch('/api/inventories/' + cabang);

    inventories = await response.json();
}

function inventoryOptions(selected = null){
    return inventories.map(inv => {
        return `
            <option
                value="${inv.id}"
                ${selected == inv.id ? 'selected' : ''}>
                ${inv.barang}
            </option>
        `;

    }).join('');

}

loadQuotations();
loadInventories()