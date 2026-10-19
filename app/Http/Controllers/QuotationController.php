<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quotation;
use App\Models\SPKWarehouse;
use App\Models\SPKManufacture;
use App\Models\RequestPlat;
use Illuminate\Support\Str;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Events\RequestPlatUpdated;
use Barryvdh\DomPDF\Facade\Pdf;
class QuotationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Mengambil data quotation beserta itemnya
        $cabang = Auth::user()->cabang;
        if($cabang == 'Pusat'){
            $inventories = Warehouse::all(); 
            $sales = User::all();
        }else{
            // Mengambil data inventori untuk pilihan di dropdown modal
            $inventories = Warehouse::where('cabang', $cabang)->get();    
            $sales = User::where('cabang', Auth::user()->cabang)->where('role', 'sales')->get();
        }

        return view('quotations_new', compact('inventories', 'cabang', 'sales'));
    }

    public function PDF($id){
        $po = Quotation::find($id);

        $pdf = Pdf::loadView('pdf.penawaran', compact('po'))->setPaper('A4', 'portrait');

        return $pdf->stream($po->quotation_number . '.pdf');
    }

    public function getDataQuotation($cabang)
    {
        $query = Quotation::with([
            'sales',
            'barang',
            'requestPlat',
            'spkWarehouse',
            'spkFinance',
            'laporan'
        ]);

        if ($cabang != 'Pusat') {
            $query->where('cabang', $cabang);
        }

        $quotations = $query->get();

        return response()->json($quotations);
    }

    public function getInventories($cabang)
    {
        if ($cabang == 'Pusat') {
            $inventories = Warehouse::all();
        } else {
            $inventories = Warehouse::where('cabang', $cabang)->get();
        }

        return response()->json($inventories);
    }

    public function requestPlat(Request $request)
    {
        $quotation = Quotation::findOrFail($request->plat_id);

        $quotation->update([
            'status' => 5
        ]);

        $requestPlat = RequestPlat::create([
            'quotation_id'    => $quotation->id,
            'request_user_id' => Auth::id(),
            'status'          => 0,
        ]);

        broadcast(new RequestPlatUpdated($requestPlat))->toOthers();

        return response()->json([
            'status' => 'Berhasil Request',
            'data'   => $quotation
        ]);
    }

    public function cancelPlat(Request $request)
    {
        $quotation = Quotation::findOrFail($request->plat_id);

        $quotation->update([
            'status' => 4
        ]);

        RequestPlat::where('quotation_id', $quotation->id)->delete();

        broadcast(new RequestPlatUpdated())->toOthers();

        return response()->json([
            'status' => 'Berhasil Cancel Request',
            'data'   => $quotation
        ]);
    }

    public function approve($id)
    {
        DB::transaction(function () use ($id) {

            $quotation = Quotation::with('items.inventory')
                ->where('id', $id)
                ->where('cabang', Auth::user()->cabang)
                ->firstOrFail();

            // Sudah pernah di-approve
            if ($quotation->status) {
                abort(400, 'Quotation sudah di-approve.');
            }

            // Cek ulang stok
            foreach ($quotation->items as $item) {

                if ($item->inventory->jumlah < $item->quantity) {
                    abort(
                        400,
                        "Stok {$item->inventory->barang} tidak mencukupi."
                    );
                }
            }

            // Kurangi stok
            foreach ($quotation->items as $item) {

                $warehouse = $item->inventory;

                $warehouse->decrement('jumlah', $item->quantity);
            }

            // Approve quotation
            $quotation->update([
                'status'      => 1,
                'approved_by' => Auth::id(),      // optional
                'approved_at' => now(),           // optional
            ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Quotation berhasil di-approve.');
    }

    public function reject($id)
    {
        DB::transaction(function () use ($id) {

            $quotation = Quotation::with('items.inventory')
                ->where('id', $id)
                ->where('cabang', Auth::user()->cabang)
                ->firstOrFail();

            // Approve quotation
            $quotation->update([
                'status'      => 0,
                'approved_by' => null,      // optional
                'approved_at' => null,           // optional
            ]);
        });

        return redirect()
            ->back()
            ->with('error', 'Quotation berhasil di-reject.');
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
        // 1. Validasi input form modal
        $request->validate([
            'nama_pemesan'      => 'required|string|max:255',
            'alamat_pemesan'    => 'required|string',
            'nama_tempat'       => 'required|string|max:255', // Sebagai penerima
            'alamat_tempat'     => 'required|string',       // Sebagai alamat_penerima
            'tanggal_pesan'     => 'required|date',
            'tipe_pemesanan'    => 'required|string',
            'jenis_kertas'      => 'required|exists:warehouse,id', // id_barang
            'jumlah_beli'       => 'required|integer|min:1',       // quantity
            'harga_per_box'     => 'required|string',              // harga (format rupiah string)
            'judul_cetak'       => 'required|string|max:255',
            'ukuran'            => 'required|string|max:255',
            'jumlah_box'        => 'required|integer|min:1',
            'jumlah_ply'        => 'required|integer|min:1',
            'isi_per_box'       => 'required|integer|min:1',       // perbox
            'perporasi'         => 'required|string|max:255',
            'cabang'            => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            // Membersihkan format harga dari string rupiah (misal: "Rp 150.000" jadi angka 150000)
            $hargaBersih = str_replace(['Rp', '.', ' '], '', $request->harga_per_box);
            $hargaBersih = (float) $hargaBersih;

            // Hitung total amount (jumlah_box * harga_per_box atau sesuai kebutuhan bisnis Anda)
            $totalAmount = $request->jumlah_box * $hargaBersih;

            // Mengambil ID Sales jika yang login adalah admin/bukan sales, atau ambil dari user login jika sales
            $idSales = null;
            if (Auth::user()->role === 'sales') {
                $idSales = Auth::id();
            } else {
                // Jika admin memilih dari dropdown, cari user berdasarkan nama
                $salesUser = User::find($request->salesman);
                $idSales = $salesUser ? $salesUser->id : null;
            }

            // 2. Simpan Data Quotation
            Quotation::create([
                'quotation_number'     => 'Q-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5)),
                'nama_customer'        => $request->nama_pemesan,
                'alamat_customer'      => $request->alamat_pemesan,
                'penerima'             => $request->nama_tempat,
                'alamat_penerima'      => $request->alamat_tempat,
                'id_sales'             => $idSales,
                'tanggal_pesan'        => $request->tanggal_pesan,
                'tipe_pemesanan'       => $request->tipe_pemesanan,
                'judul_cetak'          => $request->judul_cetak,
                'perbox'               => $request->isi_per_box,
                'ukuran'               => $request->ukuran,
                'perporasi'            => $request->perporasi,
                'jumlah_box'           => $request->jumlah_box,
                'jumlah_ply'           => $request->jumlah_ply,
                'keterangan'           => $request->keterangan,
                'id_barang'            => $request->jenis_kertas,
                'quantity'             => $request->jumlah_beli,
                'total_amount'         => $totalAmount,
                'cabang'               => $request->cabang,
                'status'               => 0, // atau 0 sesuai struktur Anda
                'harga'                => $hargaBersih
            ]);
        });

        return redirect()->back()->with('success', 'Purchase Order / Quotation berhasil disimpan!');
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
        $request->validate([
            'nama_customer'    => 'nullable|string|max:255',
            'alamat_customer'  => 'nullable|string',
            'penerima'         => 'nullable|string|max:255',
            'alamat_penerima'  => 'nullable|string',
            'tanggal_pesan'    => 'nullable|date',
            'tipe_pemesanan'   => 'nullable|string',

            'jenis_kertas'        => 'nullable|exists:warehouse,id',
            'jumlah_beli'         => 'nullable|integer|min:1',
            'harga'            => 'nullable|string',

            'judul_cetak'      => 'nullable|string|max:255',
            'ukuran'           => 'nullable|string|max:255',
            'jumlah_box'       => 'nullable|integer|min:1',
            'jumlah_ply'       => 'nullable|integer|min:1',
            'perbox'           => 'nullable|integer|min:1',
            'perporasi'        => 'nullable|string|max:255',
            'cabang'           => 'nullable|string',

            'keterangan'       => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {

            $quotation = Quotation::where('id', $id)
                ->where('cabang', Auth::user()->cabang)
                ->firstOrFail();

            // Membersihkan format harga
            // Contoh: "Rp 150.000" menjadi 150000
            $hargaBersih = str_replace(
                ['Rp', '.', ' '],
                '',
                $request->harga_per_box
            );

            $hargaBersih = (float) $hargaBersih;

            // Hitung total
            $totalAmount = $request->jumlah_box * $hargaBersih;

            // Update quotation
            $quotation->update([
                'nama_customer'   => $request->nama_pemesan,
                'alamat_customer' => $request->alamat_pemesan,

                'penerima'        => $request->nama_tempat,
                'alamat_penerima' => $request->alamat_tempat,

                'tanggal_pesan'   => $request->tanggal_pesan,
                'tipe_pemesanan'  => $request->tipe_pemesanan,

                'id_barang'       => $request->jenis_kertas,
                'quantity'        => $request->jumlah_beli,

                'harga'           => $hargaBersih,
                'total_amount'    => $totalAmount,

                'judul_cetak'     => $request->judul_cetak,
                'ukuran'          => $request->ukuran,
                'jumlah_box'      => $request->jumlah_box,
                'jumlah_ply'      => $request->jumlah_ply,
                'perbox'          => $request->isi_per_box,
                'perporasi'       => $request->perporasi,

                'keterangan'      => $request->keterangan,
                'cabang'          => $request->cabang,
            ]);
        });

        return redirect()->back()->with('success', 'Quotation berhasil diperbarui.');
    }

    public function send(SPKWarehouse $spk){
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
                'warehouse' => true,
    
                'cabang' => $spk->cabang,
    
            ]);            
        }else{
            $spkBaru = SPKManufacture::find($manufacture->id);
            $spkBaru->update([
                'warehouse' => true
            ]);
        }
    
        return response()->json([

            'message' => 'SPK berhasil dikirim ke Manufacture.',

            'data' => $spkBaru

        ]);
    }

    public function sendQuotation($id){
        $quotation = Quotation::find($id);

        $quotation->update([
            'status' => 1
        ]);

        return redirect()->back()->with('success', 'Quotation berhasil dikirim.');
    }

    public function rejectQuotation(Request $request, $id)
    {
        $request->validate([
            'keterangan_reject' => 'nullable|string',
        ]);

        $quotation = Quotation::where('id', $id)
            ->where('cabang', Auth::user()->cabang)
            ->firstOrFail();

        $quotation->update([
            'status' => 2,
            'keterangan_reject' => $request->keterangan_reject,
        ]);

        return redirect()->back()->with('success', 'Quotation berhasil ditolak.');
    }

    public function generateFilm($id)
    {
        try {
            // 1. Ambil data quotation beserta relasi customer jika ada
            $quotation = Quotation::findOrFail($id);

            // 2. Rangkai Format Nomor Film: DMC / MMYY / CUST / Judul / Ukuran
            $mmyy = now()->format('mmyy'); // Contoh: 0626
            $cust = $quotation->nama_customer ?? 'CUST'; // Ambil kode customer
            $judul = preg_replace('/[^A-Za-z0-9]/', '', $quotation->judul_pekerjaan); // Bersihkan spasi/simbol pada judul
            $ukuran = $quotation->ukuran ?? 'Std'; // Ukuran cetak

            $kodeMasterFilm = "DMC/{$mmyy}/${cust}/{$judul}/{$ukuran}";

            // 3. Simpan ke database Master Film
            $quotation->update([
                'film' => $kodeMasterFilm
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nomor film berhasil digenerate!',
                'data' => $quotation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate film: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteFilm($id)
    {
        try {
            $quotation = Quotation::findOrFail($id);

            // Kosongkan kolom film (set menjadi null)
            $quotation->update([
                'film' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Nomor film berhasil dihapus!',
                'data' => $quotation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus film: ' . $e->getMessage()
            ], 500);
        }
    }

    public function approveQuotation(Request $request, $id)
    {
        $request->validate([
            'keterangan_reject' => 'nullable|string',
        ]);

        $quotation = Quotation::where('id', $id)
            ->where('cabang', Auth::user()->cabang)
            ->firstOrFail();
        
        if(Auth::user()->role == 'pricing'){
            $quotation->update([
                'status' => 3,
            ]);
        }else{    
            $quotation->update([
                'status' => 4,
            ]);
        }


        return redirect()->back()->with('success', 'Quotation berhasil di Approve.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $quotation = Quotation::findOrFail($id);
        $quotation->delete(); // Karena sudah ada onDelete('cascade') di migration, items otomatis terhapus

        return redirect()->back()->with('success', 'Quotation berhasil dihapus.');
    }

    public function checkStock(Request $request)
    {
        $request->validate([
            'quotation_id' => 'required|exists:quotations,id',
        ]);

        $quotation = Quotation::with('items.inventory')
            ->where('id', $request->quotation_id)
            ->where('cabang', Auth::user()->cabang)
            ->firstOrFail();

        $kurang = [];

        foreach ($quotation->items as $item) {

            $stok = $item->inventory->jumlah ?? 0;

            if ($stok < $item->quantity) {

                $kurang[] = [
                    'barang' => $item->inventory->barang,
                    'qty'    => $item->quantity,
                    'stock'  => $stok,
                ];
            }
        }

        if (count($kurang) > 0) {
            return response()->json([
                'success' => false,
                'items'   => $kurang
            ]);
        }

        return response()->json([
            'success' => true
        ]);
    }
}
