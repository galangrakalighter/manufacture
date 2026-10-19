<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Warehouse;
use App\Models\RequestPlat;
use App\Models\Quotation;
use Illuminate\Support\Facades\Auth;
use App\Events\RequestPlatUpdated;
class warehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Warehouse::query();

        if (Auth::user()->cabang !== "Pusat") {
            $query->where('cabang', Auth::user()->cabang);
        }

        // Menambahkan filter pencarian jika ada input 'search'
        $warehouse = $query->when($request->search, function ($q, $search) {
            $q->where('barang', 'like', "%{$search}%");
        })
        ->when($request->cabang, fn($q, $cabang) => $q->where('cabang', $cabang))
        ->get();

        return view('warehouse', compact('warehouse'));
    }

    public function request(){
        return view('warehouse.request_plat');
    }

    public function spk(){
        return view('warehouse.spk');
    }
    public function spkManufacture(){
        return view('manufacture.spk');
    }

    public function approveQuotation(Request $request, $id){
        $request->validate([
            'lokasi_plat' => 'required|string|max:255',
            'catatan' => 'nullable|string'
        ]);

        $plat = RequestPlat::findOrFail($id);
        $quotation = Quotation::find($plat->quotation_id);
        $mmyy = now()->format('mmyy'); // Contoh: 0626
        $cust = $quotation->nama_customer ?? 'CUST'; // Ambil kode customer
        $judul = preg_replace('/[^A-Za-z0-9]/', '', $quotation->judul_pekerjaan); // Bersihkan spasi/simbol pada judul
        $ukuran = $quotation->ukuran ?? 'Std'; // Ukuran cetak

        $kodeMasterFilm = "(DMC/{$mmyy}/{$cust}/{$judul}{$ukuran})/{$request->lokasi_plat}";

        $quotation->update([
            'status' => 7,
            'film' => $kodeMasterFilm
        ]);

        $plat->update([
            'status' => 1,
            'approve_user_id' => Auth::id(),
            'approved_at' => now(),
            'lokasi_plat' => $request->lokasi_plat,
            'catatan' => $request->catatan,
        ]);

        broadcast(new RequestPlatUpdated());

        return response()->json([
            'status' => 'Request berhasil di-approve.'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->merge([
            'harga' => str_replace('.', '', $request->harga)
        ]);
        $request->validate([
            'barang' => 'required|string|max:255',
            'jumlah' => 'required|integer',
            'harga' => 'required|numeric',
            'satuan' => 'required|string|max:255'
        ]);

        Warehouse::create([
            'barang' => $request->barang,
            'jumlah' => $request->jumlah,
            'harga' => $request->harga,
            'satuan' => $request->satuan,
            'cabang' => Auth::user()->cabang
        ]);

        return redirect()->back()->with('success', 'Barang berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->merge([
            'harga' => str_replace('.', '', $request->harga)
        ]);
        $request->validate([
            'barang' => 'required|string|max:255',
            'jumlah' => 'required|integer',
            'harga' => 'required|numeric',
            'satuan' => 'required|string|max:255'
        ]);

        Warehouse::where('id', $id)->update([
            'barang' => $request->barang,
            'jumlah' => $request->jumlah,
            'harga' => $request->harga,
            'satuan' => $request->satuan,
            'cabang' => Auth::user()->cabang
        ]);

        return redirect()->back()->with('success', 'Barang berhasil Diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $warehouse = Warehouse::find($id);
        $warehouse->delete();
        return redirect()->back()->with('success', 'Barang berhasil dihapus');
    }
}
