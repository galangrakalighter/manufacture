<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MaterialRequisition;
use App\Models\Warehouse;
use App\Models\RequisitionItem;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
class RequisitionController extends Controller
{
    public function index(){
       $requisitions = MaterialRequisition::with(['creator', 'approver', 'items.warehouse'])
                                            ->where('material_requisitions.cabang', Auth::user()->cabang)
                                            ->latest()
                                            ->get();
        $all_warehouse = Warehouse::all();
        return view('requisition', compact('requisitions', 'all_warehouse'));
    }
    public function store(Request $request) {
        $req = MaterialRequisition::create([
            'user_id' => Auth::id(),
            'catatan' => $request->catatan,
            'status'  => 'Pending',
            'cabang' => Auth::user()->cabang
        ]);

        foreach ($request->items as $index => $itemInput) {
            // Cek apakah input adalah angka (ID barang) atau teks (Nama barang baru)
            if (is_numeric($itemInput)) {
                $warehouse_id = $itemInput;
            } else {
                // Jika barang baru, masukkan ke table warehouse sebagai status 'Pending' atau lain-lain
                $newBarang = Warehouse::create([
                    'barang' => $itemInput,
                    'jumlah' => 0, // Awalnya 0
                    'tipe'   => null,
                    'harga'  => 0,
                    'satuan' => 'centimeter',
                    'cabang' => Auth::user()->cabang
                ]);
                $warehouse_id = $newBarang->id;
            }

            RequisitionItem::create([
                'requisition_id' => $req->id,
                'warehouse_id'   => $warehouse_id,
                'jumlah_minta'   => $request->jumlah[$index]
            ]);
        }
        return redirect()->back()->with('success', 'Permintaan terkirim!');
    }

    public function approve(Request $request, $id) {
        $req = MaterialRequisition::findOrFail($id);
        $req->update([
            'status' => 'Approved',
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
        ]);

        $totalSemua = 0;

        foreach ($request->item_ids as $index => $itemId) {
            $hargaBersih = str_replace('.', '', $request->harga[$index]); 
            
            $item = RequisitionItem::find($itemId);
            if ($item) {
                $item->harga = $hargaBersih;
                $item->save(); // PENTING: Harus ada ini agar tersimpan ke database

                $warehouse = Warehouse::find($item->warehouse_id);

                if ($warehouse) {
                    $warehouse->increment('jumlah', $item->jumlah_minta);
                }
                
                $totalSemua += ($hargaBersih * $item->jumlah_minta);
            }
        }

        Transaction::create([
            'type'             => 'keluar',
            'amount'           => $totalSemua,
            'category'         => 'Pembelian Material',
            'description'      => 'Pembelian untuk Requisition #' . $req->id,
            'transaction_date' => now(),
            'reference_id'     => $req->id,
            'reference_type'   => 'requisition'
        ]);

        return redirect()->back()->with('success', 'Permintaan Disetujui.');
    }
    public function reject($id) {
        MaterialRequisition::findOrFail($id)->update([
            'status' => 'Reject',
            'approved_by' => Auth::user()->id,
            'approved_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Permintaan Ditolak.');
    }

    public function update(Request $request, $id) {
        MaterialRequisition::findOrFail($id)->update(['catatan' => $request->catatan]);
        return redirect()->back()->with('success', 'Permintaan diperbarui!');
    }

    public function destroy($id) {
        MaterialRequisition::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Permintaan dihapus!');
    }

    public function transactions(){
        $transactions = Transaction::with(['requisition.items.warehouse'])
                               ->orderBy('created_at', 'desc')
                               ->get();
        return view('transactions', compact('transactions'));
    }
}
