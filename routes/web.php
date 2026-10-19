<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\warehouseController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\RequisitionController;
use App\Http\Controllers\SPKController;
use App\Http\Controllers\QuotationController;
use Illuminate\Support\Facades\Auth;
use App\Models\RequestPlat;
use App\Models\SPKWarehouse;
use App\Models\SPKFinance;
use App\Models\SPKManufacture;
use App\Models\SPKProduction;
Route::get('/', function () {
    return view('login');
});

Route::post('/login', [UserController::class, 'login'])->name('login');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/dashboard', [UserController::class, 'dashboard'])->name('dashboard');
Route::get('/purchase-order/{id}/print', [PurchaseOrderController::class,'print'])->name('purchase-order.print');
Route::post('/plat/request', [QuotationController::class, 'requestPlat'])->name('request_plat');
Route::post('/plat/cancel', [QuotationController::class, 'cancelPlat'])->name('cancel_plat');
Route::get('/quotations/{id}/pdf', [QuotationController::class,'PDF'])->name('quotation.pdf');
Route::put('/quotations/{id}/kirim', [QuotationController::class,'sendQuotation'])->name('quotation.send');
Route::put('/quotations/{id}/reject', [QuotationController::class,'rejectQuotation'])->name('quotation.reject');
Route::put('/quotations/{id}/approve', [QuotationController::class,'approveQuotation'])->name('quotation.approve');
Route::post('/quotations/{id}/generate-film', [QuotationController::class, 'generateFilm'])->name('films.generate');
Route::post('/quotations/{id}/delete-film', [QuotationController::class, 'deleteFilm'])->name('films.delete');
Route::get('/purchase-order/{id}/quotation', [PurchaseOrderController::class,'quotation'])->name('purchase-order.quotation');
Route::get('/purchase-order/{id}/invoice', [PurchaseOrderController::class,'invoice'])->name('purchase-order.invoice');
Route::get('/purchase-order/{id}/faktur', [PurchaseOrderController::class,'faktur'])->name('purchase-order.faktur');
Route::get('/purchase-order/{id}/surat-jalan', [PurchaseOrderController::class,'suratJalan'])->name('purchase-order.suratJalan');
Route::put('/requisition/approve/{id}', [RequisitionController::class, 'approve'])->name('requisition.approve');
Route::get('/transactions', [RequisitionController::class, 'transactions'])->name('transactions.index');
Route::post('/quotations/check-stock', [QuotationController::class, 'checkStock'])->name('quotations.checkStock');
Route::put('/quotations/approve/{id}', [QuotationController::class, 'approve'])->name('quotations.approve');
Route::put('/quotations/reject/{id}', [QuotationController::class, 'reject'])->name('quotations.reject');
Route::post('/gudang/request/{id}/approve', [WarehouseController::class, 'approveQuotation'])->name('gudang.approveQuotation');
Route::post('/gudang/request/{id}/reject', [WarehouseController::class, 'rejectQuotation'])->name('gudang.rejectQuotation');
Route::get('/gudang/request', [WarehouseController::class, 'request'])->name('gudang.requests');
Route::get('/spk', [WarehouseController::class, 'spk'])->name('gudang.spk');
Route::get('/spk-manufacture', [WarehouseController::class, 'spkManufacture'])->name('manufacture.spk');
Route::get('/gudang/spk/{spk}/pdf', [SpkController::class, 'pdf'])->middleware('auth');
Route::get('/production/spk/{spk}/pdf', [SpkController::class, 'pdfProduction'])->middleware('auth');
Route::get('/finance/spk/{spk}/pdf', [SpkController::class, 'pdfFinance'])->middleware('auth');
Route::get('/invoice/spk/{spk}/pdf', [SpkController::class, 'pdfInvoice'])->middleware('auth');
Route::get('/finance/surat-jalan-pdf/{spk}', [SpkController::class, 'pdfSuratJalan']);
Route::post('/api/gudang/spk/{spk}/accept', [SpkController::class,'accept'])->middleware('auth');
Route::post('/api/gudang/bahan/{spk}/send', [QuotationController::class,'send'])->middleware('auth');
Route::post('/api/finance/spk/{spk}/accept', [SpkController::class,'acceptFinance'])->middleware('auth');
Route::post('/gudang/spk/{spk}/cancel', [SpkController::class,'cancel'])->middleware('auth');
Route::post('/api/manufacture/spk/{spk}/accept', [SpkController::class,'acceptManufacture'])->middleware('auth');
Route::post('/api/production/spk/{spk}/accept', [SpkController::class,'acceptProduction'])->middleware('auth');
Route::post('/api/production/spk/{spk}/send', [SpkController::class,'sendSPK'])->middleware('auth');
Route::post('/api/production/spk/{spk}/cancel', [SpkController::class,'cancelProduction'])->middleware('auth');
Route::resource('warehouse', warehouseController::class);
Route::resource('users', UserController::class);
Route::resource('purchase-order', PurchaseOrderController::class);
Route::get('/getBarang/{id}', [PurchaseOrderController::class, 'getBarang'])->name('getBarang');
Route::resource('sales', SalesController::class);
Route::resource('requisition', RequisitionController::class);
Route::resource('quotations', QuotationController::class);
Route::get('/spk/warehouse/{id}', [SPKController::class,'warehouse']);

Route::get('/spk/production/{id}', [SPKController::class,'production']);

// GUDANG
Route::get('/api/gudang/requests', function () {
    return \App\Models\RequestPlat::where('status', 0)->with([
        'quotation:id,quotation_number,nama_customer,cabang,total_amount,status',
        'requester:id,name',
        'approver:id,name',
    ])
    ->whereHas('quotation', function ($query) {
        $query->where('cabang', Auth::user()->cabang);
    })
    ->latest()
    ->get();
})->middleware('auth');
Route::get('/api/gudang/requests-all', function () {
    return \App\Models\RequestPlat::with([
        'quotation:id,quotation_number,nama_customer,cabang,total_amount,status',
        'requester:id,name',
        'approver:id,name',
    ])
    ->whereHas('quotation', function ($query) {
        $query->where('cabang', Auth::user()->cabang);
    })
    ->latest()
    ->get();
})->middleware('auth');
Route::get('/api/gudang/request-count', function () {
    $count = RequestPlat::where('status', 0)
        ->whereHas('quotation', function ($query) {
            $query->where('cabang', Auth::user()->cabang);
        })
        ->count();

    return response()->json([
        'count' => $count
    ]);
})->middleware('auth');

// SPK
Route::get('/api/gudang/spk', function () {

    $user = Auth::user();


    // Untuk Gudang
    if($user->role == 'gudang'){

        return \App\Models\SPKWarehouse::with([
            'quotation.barang'
        ])
        ->where('status',0)
        ->where('cabang',$user->cabang)
        ->latest()
        ->get();

    }


    // Untuk Production
    if($user->role == 'production'){

        return \App\Models\SPKProduction::with([
            'warehouse.quotation.barang',
            'pic'
        ])
        ->where('status',0)
        ->where('cabang',$user->cabang)
        ->latest()
        ->get();

    }

    if($user->role == 'akuntansi'){

        return \App\Models\SPKFinance::with([
            'quotation.barang',
        ])
        ->where('status',0)
        ->where('cabang',$user->cabang)
        ->latest()
        ->get();

    }

    if($user->role == 'manufacture'){

        return \App\Models\SPKManufacture::with([
            'quotation.barang',
        ])
        ->where('status',0)
        ->where('cabang',$user->cabang)
        ->latest()
        ->get();

    }


    return response()->json([]);

})->middleware('auth');

Route::get('/api/gudang/spk-all', function () {

    $user = Auth::user();

    if ($user->role == 'gudang') {

        return SPKWarehouse::with([
            'quotation.barang'
        ])
        ->select('*', 'spk_warehouse.status as status_spk')
        ->where('cabang', $user->cabang)
        ->latest()
        ->get();
    }

    if ($user->role == 'production') {

        return SPKProduction::with([
            'warehouse.quotation.barang',
            'pic'
        ])
        ->select('*', 'spk_production.status as status_spk')
        ->where('cabang', $user->cabang)
        ->latest()
        ->get();
    }

    if ($user->role == 'akuntansi') {

        return SPKFinance::with([
            'quotation.barang',
        ])
        ->select('*', 'spk_finance.status as status_spk')
        ->where('cabang', $user->cabang)
        ->latest()
        ->get();
    }

    if ($user->role == 'manufacture') {

        return SPKManufacture::with([
            'quotation.barang',
        ])
        ->select('*', 'spk_manufacture.status as status_spk')
        ->where('cabang', $user->cabang)
        ->latest()
        ->get();
    }

    return response()->json([]);

})->middleware('auth');

Route::get('/api/gudang/spk-count', function () {

    $user = Auth::user();


    if($user->role == 'gudang'){

        $count = \App\Models\SPKWarehouse::where('status',0)
            ->where('cabang',$user->cabang)
            ->count();

    }
    elseif($user->role == 'production'){


        $count = \App\Models\SPKProduction::where('status',0)
            ->where('cabang',$user->cabang)
            ->count();

    }
    elseif($user->role == 'akuntansi'){


        $count = \App\Models\SPKFinance::where('status',0)
            ->where('cabang',$user->cabang)
            ->count();

    }
    else{

        $count = 0;

    }


    return response()->json([
        'count'=>$count
    ]);


})->middleware('auth');