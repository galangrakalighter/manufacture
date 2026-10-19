<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\SPKWarehouse;
use App\Models\SPKProduction;
use App\Models\SPKManufacture;
use App\Models\laporanManufacture;
use App\Models\SPKFinance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
class SPKController extends Controller
{

    public function warehouse($id)
    {

        $quotation = Quotation::with([
            'items.inventory',
            'requestPlat'
        ])
        ->findOrFail($id);

        return view('spk.warehouse',compact('quotation'));

    }



    public function production($id)
    {

        $quotation = Quotation::with([
            'items.inventory',
            'requestPlat'
        ])
        ->findOrFail($id);


        return view('spk.production',compact('quotation'));

    }

    public function sendWarehouse(Request $request, $id){
        $request->validate([
            'note' => 'nullable|string|max:1000'
        ]);

        $quotation = Quotation::findOrFail($id);

        // Cek apakah SPK Warehouse sudah pernah dibuat
        $exists = SPKWarehouse::where('quotation_id', $quotation->id)->exists();

        if ($exists) {
            return response()->json([
                'message' => 'SPK Warehouse sudah pernah dibuat.'
            ], 422);
        }

        $spk = SPKWarehouse::create([
            'quotation_id' => $quotation->id,
            'spk_number'   => 'SPK-WH-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
            'status'       => 0,
            'cabang'       => $request->cabang,
            'catatan'      => $request->note
        ]);

        return response()->json([
            'message' => 'SPK berhasil dikirim ke Warehouse.',
            'data'    => $spk
        ]);
    }

    public function sendFinance(Request $request, $id)
    {
        $request->validate([
            'note' => 'nullable|string|max:1000',
        ]);

        $quotation = Quotation::findOrFail($id);

        // Cek apakah SPK Finance sudah pernah dibuat
        $exists = SPKFinance::where('quotation_id', $quotation->id)->exists();

        if ($exists) {
            return response()->json([
                'message' => 'SPK Finance sudah pernah dibuat.'
            ], 422);
        }

        $spk = SPKFinance::create([
            'quotation_id' => $quotation->id,
            'spk_number'   => 'SPK-FIN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
            'status'       => 0,
            'cabang'       => $request->cabang,
            'catatan'      => $request->note
        ]);

        return response()->json([
            'message' => 'SPK berhasil dikirim ke Finance.',
            'data'    => $spk
        ]);
    }

    public function sendLaporan(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'barang_jadi' => 'nullable|string',
            'waste' => 'nullable|string',
        ]);

        // Cari data SPK berdasarkan ID
        // Sesuaikan nama model (misal: SPKProduction atau SPKManufacture)
        $spk = SPKManufacture::findOrFail($id);

        $spk->update([
            'status' => 2
        ]);

        $laporan = laporanManufacture::create([
            'quotation_id' => $spk->quotation_id,
            'hasil_jadi' => $request->barang_jadi,
            'waste' => $request->waste,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil disimpan!',
            'data' => $laporan
        ]);
    }

    public function sendSPK(SPKProduction $spk){
        $quotationId = $spk->quotation_id ?? optional($spk->warehouse)->quotation_id;

        $manufacture = SPKManufacture::where('quotation_id', $quotationId)->first();
        if($manufacture == null){
            $spkBaru = SPKManufacture::create([
                'quotation_id' => $quotationId,
                'spk_number' => 
                    'SPK-MAN-' 
                    . now()->format('Ymd')
                    . '-'
                    . strtoupper(Str::random(5)),
                'status' => 0,
                'production' => true,
    
                'cabang' => $spk->cabang,
    
            ]);            
        }else{
            $spkBaru = SPKManufacture::find($manufacture->id);
            $spkBaru->update([
                'production' => true
            ]);
        }
    
        return response()->json([

            'message' => 'SPK berhasil dikirim ke Manufacture.',

            'data' => $spkBaru

        ]);
    }

    public function sendProduction(Request $request, $id)
    {
        $request->validate([

            'pic_production_id' => 'required|exists:users,id',

            'note' => 'nullable|string|max:1000'

        ]);



        // $id adalah ID SPK Warehouse
        $warehouseSpk = SPKWarehouse::where('quotation_id', $id)->first();

        // Cek apakah production sudah dibuat
        $exists = SPKProduction::where('spk_warehouse_id', $warehouseSpk->id)->exists();

        if($exists){

            return response()->json([

                'message' => 'SPK Production sudah pernah dibuat.'

            ],422);

        }



        $spk = SPKProduction::create([

            'spk_warehouse_id' => $warehouseSpk->id,

            'spk_number' => 
                'SPK-PROD-' 
                . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(5)),


            'pic_id' => 
                $request->pic_production_id,


            'status' => 0,


            'cabang' => $warehouseSpk->cabang,


            'catatan' => $request->note

        ]);



        return response()->json([

            'message' => 'SPK berhasil dikirim ke Production.',

            'data' => $spk

        ]);

    }

    public function pdf(SPKWarehouse $spk)
    {
        $spk->load([
            'quotation.barang'
        ]);

        $pdf = Pdf::loadView('spk.warehouse', [
            'spk' => $spk
        ]);

        return $pdf->stream($spk->spk_number . '.pdf');
    }

    public function pdfFinance(SPKFinance $spk)
    {
        $spk->load([
            'quotation.barang'
        ]);

        $pdf = Pdf::loadView('spk.finance', [
            'spk' => $spk
        ]);

        return $pdf->stream($spk->spk_number . '.pdf');
    }

    public function pdfInvoice(SPKFinance $spk){
        $spk->load([
            'quotation.barang'
        ]);

        $pdf = Pdf::loadView('pdf.invoice_pelanggan', [
            'spk' => $spk
        ]);

        return $pdf->stream($spk->spk_number . '.pdf');
    }

    public function pdfSuratJalan(SPKFinance $spk)
    {
        // Load relasi yang dibutuhkan untuk surat jalan (seperti customer, barang, dsb)
        $spk->load([
        'quotation.barang'
        ]);
            
        // Generate PDF menggunakan view khusus surat jalan
        $pdf = Pdf::loadView('pdf.surat_jalan', [
            'spk' => $spk
        ]);

        // Stream PDF di browser dengan nama file Surat-Jalan-[nomor].pdf
        return $pdf->stream('Surat-Jalan-' . ($spk->no_invoice ?? $spk->spk_number) . '.pdf');
    }

    public function accept(SPKWarehouse $spk)
    {
        if($spk->status != 0){

            return response()->json([
                'message' => 'SPK sudah diproses.'
            ],422);

        }

        $spk->update([
            'status' => 1
        ]);

        return response()->json([
            'message' => 'SPK berhasil diterima.'
        ]);
    }

    public function acceptFinance(SPKFinance $spk)
    {
        if($spk->status != 0){

            return response()->json([
                'message' => 'SPK sudah diproses.'
            ],422);

        }

        $spk->update([
            'status' => 1,
            'no_invoice' => 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5))
        ]);

        return response()->json([
            'message' => 'SPK berhasil diterima.'
        ]);
    }

    public function cancel(SPKWarehouse $spk)
    {
        if($spk->status != 1){

            return response()->json([
                'message' => 'SPK sudah diproses.'
            ],422);

        }

        $spk->update([
            'status' => 0
        ]);

        return response()->json([
            'message' => 'SPK berhasil dibatalkan.'
        ]);
    }

    public function pdfProduction(SPKProduction $spk)
    {
        $spk->load([
            'warehouse.quotation.barang',
            'pic'
        ]);

        $pdf = Pdf::loadView('spk.production', [
            'spk' => $spk
        ]);

        return $pdf->stream($spk->spk_number . '.pdf');
    }

    public function acceptProduction(SPKProduction $spk)
    {
        if ($spk->status != 0) {

            return response()->json([
                'message' => 'SPK sudah diproses.'
            ], 422);

        }

        $spk->update([
            'status' => 1
        ]);

        return response()->json([
            'message' => 'SPK berhasil diterima.'
        ]);
    }

    public function acceptManufacture(SPKManufacture $spk)
    {
        if ($spk->status != 0) {

            return response()->json([
                'message' => 'SPK sudah diproses.'
            ], 422);

        }

        $spk->update([
            'status' => 1
        ]);

        return response()->json([
            'message' => 'SPK berhasil diterima.'
        ]);
    }

    public function cancelProduction(SPKProduction $spk)
    {
        if ($spk->status != 1) {

            return response()->json([
                'message' => 'SPK belum sedang diproses.'
            ], 422);

        }

        $spk->update([
            'status' => 0
        ]);

        return response()->json([
            'message' => 'SPK berhasil dibatalkan.'
        ]);
    }

}