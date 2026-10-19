@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6 animate-in fade-in duration-500">
    
    @if (session('success') || session('error'))
        <div id="flash-msg" class="p-4 rounded-xl border {{ session('success') ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }} shadow-sm">
            {{ session('success') ?? session('error') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold">Manajemen Pengguna</h1>
            <p class="text-gray-500">Kelola akun pengguna dan cabang.</p>
        </div>
        <button onclick="toggleModal('modal-add', true)" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 transition flex items-center gap-2 font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </button>
    </div>

    <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" 
            placeholder="Cari nama atau email..." 
            class="w-full md:w-64 px-4 py-2.5 rounded-xl border border-gray-200 outline-none">
        
        <select name="role" class="px-4 py-2.5 rounded-xl border border-gray-200 outline-none">
            <option value="">Semua Role</option>
            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Kepala Cabang</option>
            <option value="penjualan" {{ request('role') == 'penjualan' ? 'selected' : '' }}>Admin Penjualan</option>
            <option value="gudang" {{ request('role') == 'gudang' ? 'selected' : '' }}>Admin Gudang</option>
            <option value="pembelian" {{ request('role') == 'pembelian' ? 'selected' : '' }}>Admin Pembelian</option>
            <option value="sales" {{ request('role') == 'sales' ? 'selected' : '' }}>Sales</option>
            <option value="pricing" {{ request('role') == 'pricing' ? 'selected' : '' }}>Pricing</option>
            <option value="pracetak" {{ request('role') == 'pracetak' ? 'selected' : '' }}>Pracetak</option>
        </select>

        @if(Auth::user()->cabang == 'Pusat')
        <select name="cabang" class="px-4 py-2.5 rounded-xl border border-gray-200 outline-none">
            <option value="">Semua Cabang</option>
            @foreach(['Jakarta', 'Bekasi'] as $c)
            <option value="{{ $c }}"  {{ request('cabang') == $c ? 'selected' : ''}}>{{ $c }}</option>
            @endforeach
        </select>
        @endif

        <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
            Cari Data
        </button>

        @if(request('search') || request('role') || request('cabang'))
            <a href="{{ route('users.index') }}" class="px-4 py-2 bg-gray-200 rounded-xl hover:bg-gray-300">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold text-sm">No</th>
                        <th class="px-6 py-4 font-semibold text-sm">Nama</th>
                        <th class="px-6 py-4 font-semibold text-sm">Email</th>
                        <th class="px-6 py-4 font-semibold text-sm">Role</th>
                        <th class="px-6 py-4 font-semibold text-sm">Cabang</th>
                        <th class="px-6 py-4 font-semibold text-sm">Dibuat</th>
                        <th class="px-6 py-4 font-semibold text-sm text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <!-- Nomor urut otomatis berlanjut antar halaman -->
                            <td class="px-6 py-4 text-sm">{{ $users->firstItem() + $loop->index }}</td>
                            <td class="px-6 py-4 font-medium">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $user->email }}</td>
                            
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $label = strtoupper($user->role); 
                                    if ($user->cabang != 'Pusat') {
                                        $label = match($user->role) {
                                            'admin'      => 'Kepala Cabang',
                                            'gudang'     => 'Admin Gudang',
                                            'penjualan'  => 'Admin Penjualan',
                                            'pembelian'  => 'Admin Pembelian',
                                            'akuntansi'  => 'Admin Akuntansi', 
                                            'sales'      => 'Sales', 
                                            'pricing'    => 'Pricing',
                                            'production' => 'Production',
                                            'pracetak'   => 'Pracetak',
                                            'manufacture' => 'Manufacture'
                                        };
                                    } else {
                                        $label = match($user->role) {
                                            'admin' => 'Owner',
                                        };
                                    }

                                    $colorClass = match($user->role) {
                                        'admin'      => 'bg-red-100 text-red-700',
                                        'penjualan'  => 'bg-blue-100 text-blue-700',
                                        'gudang', 'pembelian', 'akuntansi', 'sales', 'pricing', 'production', 'pracetak', 'manufacture' 
                                                    => 'bg-green-100 text-green-700',
                                        default      => 'bg-purple-100 text-purple-700',
                                    };
                                @endphp

                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ $label }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-sm">
                                <span class="bg-gray-100 px-2 py-1 rounded-lg text-xs">{{ strtoupper($user->cabang) }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $user->created_at?->format('d M Y') ?? '-' }}</td>
                            <td class="px-6 py-4 flex justify-center gap-2">
                                <button onclick="openEditModal({{ json_encode($user) }})" class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button onclick="openDeleteModal('{{ route('users.destroy', $user->id) }}')" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tombol Pagination Laravel -->
        <div class="p-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>
</div>

<div id="modal-add" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg p-8 shadow-2xl">
        <h2 class="text-xl font-bold mb-6">Tambah Pengguna</h2>
        <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
            @csrf
            <div><input type="text" name="name" placeholder="Nama Lengkap" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none">@error('name')<p class="text-red-500 text-xs mt-1">{{$message}}</p>@enderror</div>
            <div><input type="email" name="email" placeholder="Email" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none">@error('email')<p class="text-red-500 text-xs mt-1">{{$message}}</p>@enderror</div>
            <div class="relative"><input type="password" name="password" id="pass-add" placeholder="Password" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none"><button type="button" onclick="togglePass('pass-add')" class="absolute right-4 top-3.5 text-gray-400">👁️</button></div>
            <div class="grid grid-cols-2 gap-4">
                <select name="role" class="px-4 py-3 rounded-xl border border-gray-200 outline-none">
                    @if(Auth::user()->role == 'admin' && Auth::user()->cabang == 'Pusat')
                    <option value="admin">Kepala Cabang</option>
                    @endif
                    <option value="penjualan">Admin Penjualan</option>
                    <option value="gudang">Admin Gudang</option>
                    <option value="pembelian">Admin Pembelian</option>
                    <option value="akuntansi">Admin Akuntansi</option>
                    <option value="sales">Sales</option>
                    <option value="pricing">Pricing</option>
                    <option value="production">Production</option>
                    <option value="pracetak">Pracetak</option>
                    <option value="manufacture">Manufacture</option>
                </select>
                <select name="cabang" class="px-4 py-3 rounded-xl border border-gray-200 outline-none">
                    @foreach(['Jakarta', 'Bekasi'] as $c)<option value="{{$c}}">{{$c}}</option>@endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-6"><button type="button" onclick="toggleModal('modal-add', false)" class="px-6 py-2 text-gray-600">Batal</button><button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl">Simpan</button></div>
        </form>
    </div>
</div>

<div id="modal-edit" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-lg p-8 shadow-2xl">
        <h2 class="text-xl font-bold mb-6">Edit Pengguna</h2>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <input type="text" name="name" id="edit-name" placeholder="Nama Lengkap" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none">
            </div>
            <div>
                <input type="email" name="email" id="edit-email" placeholder="Email" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none">
            </div>
            <div class="pt-4 mt-2">
                <p class="text-xs text-gray-400 mb-2 italic">Kosongkan jika tidak ingin mengubah password.</p>
                <div class="relative mb-4">
                    <input type="password" name="password" id="pass-edit" placeholder="Password Baru" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none">
                    <button type="button" onclick="togglePass('pass-edit')" class="absolute right-4 top-3.5 text-gray-400">👁️</button>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <select name="role" id="edit-role" class="px-4 py-3 rounded-xl border border-gray-200 outline-none">
                    @if(Auth::user()->role == 'admin' && Auth::user()->cabang == 'Pusat')
                    <option value="admin">Kepala Cabang</option>
                    @endif
                    <option value="penjualan">Admin Penjualan</option>
                    <option value="gudang">Admin Gudang</option>
                    <option value="penbelian">Admin Pembelian</option>
                    <option value="akuntansi">Admin Akuntansi</option>
                    <option value="sales">Sales</option>
                    <option value="pricing">Pricing</option>
                    <option value="production">Production</option>
                    <option value="pracetak">Pracetak</option>
                    <option value="manufacture">Manufacture</option>
                </select>
                <select name="cabang" id="edit-cabang" class="px-4 py-3 rounded-xl border border-gray-200 outline-none">
                    @foreach(['Jakarta', 'Bekasi'] as $c)
                        <option value="{{$c}}">{{$c}}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="toggleModal('modal-edit', false)" class="px-6 py-2 text-gray-600">Batal</button>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl">Update</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-delete" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 hidden p-4">
    <div class="bg-white rounded-3xl w-full max-w-sm p-8 text-center shadow-2xl">
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        </div>
        <h2 class="text-xl font-bold mb-2">Hapus Pengguna?</h2>
        <p class="text-gray-500 mb-6">Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan.</p>
        <form id="deleteForm" method="POST">
            @csrf @method('DELETE')
            <div class="flex justify-center gap-3">
                <button type="button" onclick="toggleModal('modal-delete', false)" class="px-6 py-2 bg-gray-100 rounded-xl hover:bg-gray-200">Batal</button>
                <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-xl hover:bg-red-700">Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(id, show) { document.getElementById(id).classList.toggle('hidden', !show); }
    function togglePass(id) { const i = document.getElementById(id); i.type = i.type === 'password' ? 'text' : 'password'; }
    
    function openEditModal(u) {
        document.getElementById('editForm').action = '/users/' + u.id;
        document.getElementById('edit-name').value = u.name;
        document.getElementById('edit-email').value = u.email;
        document.getElementById('edit-role').value = u.role;
        document.getElementById('edit-cabang').value = u.cabang;
        toggleModal('modal-edit', true);
    }

    // Fungsi untuk modal hapus
    function openDeleteModal(url) {
        document.getElementById('deleteForm').action = url;
        toggleModal('modal-delete', true);
    }

    setTimeout(() => { const f = document.getElementById('flash-msg'); if(f) f.style.display = 'none'; }, 3000);
</script>
@endsection