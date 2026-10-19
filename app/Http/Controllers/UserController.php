<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class UserController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard')->with('success', 'Selamat datang kembali!');
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    public function dashboard(){
        $user = Auth::user();

        $poQuery = PurchaseOrder::query();
        $warehouseQuery = Warehouse::query();

        // Jika bukan cabang pusat, filter berdasarkan cabang
        if (strtolower($user->cabang) != 'pusat') {
            $poQuery->where('cabang', $user->cabang);
            $warehouseQuery->where('cabang', $user->cabang);
        }

        $data = [
            'po_count' => (clone $poQuery)->count(),

            'masuk_count' => (clone $warehouseQuery)
                ->whereDate('created_at', now())
                ->count(),

            'stok_total' => (clone $warehouseQuery)
                ->sum('jumlah'),

            'recent_po' => (clone $poQuery)
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('dashboard', compact('data'));
    }

    public function index(Request $request){
        $query = User::query();
        if (Auth::user()->cabang !== 'Pusat') {
            $query->where('cabang', auth()->user()->cabang);
        }

        // Filter akan diproses setiap kali tombol "Cari Data" ditekan
        $users = $query->when($request->search, function ($q, $search) {
                $q->where(fn($sub) => $sub->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->role, fn($q, $role) => $q->where('role', $role))
            ->when($request->cabang, fn($q, $cabang) => $q->where('cabang', $cabang))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users', compact('users'));
    }

    public function getUserProduction(Request $request){
        $users = User::where('role', 'production')
            ->where('cabang', $request->cabang)
            ->select(
                'id',
                'name'
            )
            ->orderBy('name')
            ->get();


        return response()->json($users);
    }

    public function store(Request $request){

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8'],
            'role'     => ['required', 'string'],
            'cabang'   => ['required', 'string'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return back()->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, $id){
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email'],
            'role'     => ['required', 'string'],
            'cabang'   => ['required', 'string'],
            'password' => ['nullable', 'min:8'],
        ]);

        $user = User::find($id);

        // Jika password diisi, hash password baru
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy($id){
        $user = User::find($id);
        $user->delete();
        return back()->with('success', 'Pengguna Berhasil Dihapus');
    }
}
