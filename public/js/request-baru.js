const API_BASE_URL = "https://demoklien.lightermediagroup.com";
const role = document.getElementById('role').value;
console.log("masuk sini");
async function requestPlat(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/plat/request`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer YOUR_TOKEN' // Sesuaikan dengan sistem auth Anda
            },
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
                'Authorization': 'Bearer YOUR_TOKEN'
            },
            body: JSON.stringify({ 
                plat_id: id 
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
                    Rp ${Number(q.total_amount).toLocaleString('id-ID')}
                </td>

                <td class="px-6 py-4 text-center">
                    ${renderAction(q, index)}
                </td>

            </tr>
        `;
    });

}

function renderAction(q, index) {
    if (role === 'sales') {
        if(q.status === 0){
            return `
                <button
                    onclick="openEditModal(quotations[${index}])"
                    class="text-blue-600 hover:text-blue-800">
                    Edit
                </button>
    
                <button
                    onclick="openDeleteModal('/quotations/${q.id}')"
                    class="text-red-600 hover:text-red-800">
                    Delete
                </button>
            `;
        }else{
            return `
                <span class="text-gray-400 text-sm italic">
                    Sudah Di Approve
                </span>
            `;
        }
    }

    if (role === 'pricing'){
        if(q.status === 0){
            return `
                <button
                    onclick="openApproveModal(quotations[${index}])"
                    class="text-blue-600 hover:text-blue-800">
                    Detail
                </button>
            `;
        }else{
            return `
                <span class="text-gray-400 text-sm italic">
                    Sudah Di Approve
                </span>
            `;
        }
    }

    if (role === 'penjualan') {

        if (q.status == 0) {
            return `
                <span class="text-gray-400 text-sm italic">
                    Belum Di-approve
                </span>
            `;
        }

        if (q.status == 1) {
            return `
                <button
                    onclick="requestPlat(${q.id})"
                    class="px-4 py-2 border-2 border-blue-600 text-blue-600 text-sm font-bold rounded-full hover:bg-blue-50 transition-all duration-200">
                    Request Plat
                </button>
            `;
        }

        if (q.status == 2) {
            return `
                <button
                    onclick="cancelPlat(${q.id})"
                    class="px-4 py-2 border border-red-200 text-red-500 text-sm font-medium rounded-lg hover:bg-red-50 hover:border-red-300 transition-all duration-200">
                    Cancel Request
                </button>
            `;
        }

        if (q.status == 3) {

            return `
                <button
                    onclick="openPlatDetailModal(quotations[${index}])"
                    class="px-3 py-2 border border-green-600 text-green-600 rounded-lg hover:bg-green-50">
                    Detail Plat
                </button>

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
            `;
        }
    }

    return `
        <span class="text-gray-400 text-sm italic">
            Tidak Ada Akses
        </span>
    `;
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
                </tr>
            </thead>
            <tbody>
    `;

    q.items.forEach(item => {

        html += `
            <tr class="border-t">

                <td class="px-3 py-2">

                    <div class="font-medium">
                        ${item.inventory?.barang ?? '-'}
                    </div>

                </td>

                <td class="px-3 py-2 text-center">
                    ${item.quantity}
                </td>

            </tr>
        `;

    });

    html += `
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

function openProductionModal(spk){


    console.log(spk);


    document.getElementById(
        'productionWarehouseSpkId'
    ).value = spk.id;



    document.getElementById(
        'productionSpkNumber'
    ).innerText = spk.spk_warehouse.spk_number;



    document.getElementById(
        'productionCustomer'
    ).innerText =
        spk.nama_customer;



    document.getElementById(
        'productionNote'
    ).value='';



    let html=`

    <table class="w-full text-sm">

        <thead class="bg-gray-100">

            <tr>

                <th class="px-3 py-2 text-left">
                    Barang
                </th>


                <th class="px-3 py-2 text-center">
                    Qty
                </th>

            </tr>

        </thead>

        <tbody>

    `;



    spk.items.forEach(item=>{


        html+=`

        <tr class="border-t">


            <td class="px-3 py-2">

                ${item.inventory?.barang ?? '-'}

            </td>


            <td class="px-3 py-2 text-center">

                ${item.quantity}

            </td>


        </tr>

        `;


    });



    html+=`

        </tbody>

    </table>

    `;



    document.getElementById(
        'productionItems'
    ).innerHTML=html;



    loadProductionPIC();



    const modal=document.getElementById(
        'productionModal'
    );


    modal.classList.remove('hidden');

    modal.classList.add('flex');

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