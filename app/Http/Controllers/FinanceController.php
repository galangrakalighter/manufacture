<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SPKFinance;
class FinanceController extends Controller
{
    public function getDataINV($id){
        $data = SPKFinance::with([
            'quotation.barang',
        ])
        ->select('*', 'spk_finance.status as status_spk')
        ->where('spk_finance.id', $id)
        ->latest()
        ->first();

        return response()->json(['data' => $data]);
    }

    public function payment(Request $request, $id){
        $request->validate([
            'amount' => 'required',
            'note' => 'nullable|string',
        ]);

        $cleanAmount = preg_replace('/[^0-9]/', '', $request->amount);

        DB::beginTransaction();
        try {
            // Cari data SPK Finance berdasarkan ID
            $spk = SPKFinance::with('quotation')->findOrFail($id);

            DB::table('transactions')->insert([
                'type' => 'masuk',
                'amount' => $cleanAmount,
                'category' => 'Terima Pembayaran',
                'description' => 'Pembayaran Client ' . $spk->quotation->nama_customer,
                'transaction_date' => now(),
                'reference_type' => 'client',
                'created_at' => now(),
                'updated_at' => now(),
                'keterangan' => $request->note,
                'no_invoice' => $spk->no_invoice,
                'cabang' => $request->cabang
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil disimpan.',
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }
}
