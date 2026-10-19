<aside id="sidebar" class="fixed left-0 top-0 h-screen w-64 bg-white border-r border-gray-200 z-30 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <div class="p-6 flex items-center gap-3">
        <div class="w-8 h-8 bg-blue-600 rounded-full"></div>
        <span class="font-bold text-lg">Warehouse System</span>
    </div>
    
    <nav class="mt-6 px-4 space-y-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <span class="font-medium">Dashboard</span>
        </a>
        @if(in_array(Auth::user()->role, ['admin', 'penjualan', 'sales', 'pricing', 'pracetak']))
            <details class="group" {{ request()->routeIs('penjualan.*') || request()->routeIs('purchase-order.*') || request()->routeIs('quotation.*') ? 'open' : '' }}>
                <summary class="flex items-center justify-between px-4 py-3 rounded-xl cursor-pointer transition
                    {{ request()->routeIs('penjualan.*') || request()->routeIs('purchase-order.*') || request()->routeIs('quotation.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50' }}">
                    <span>Penjualan</span>
                    <svg class="w-4 h-4 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>

                <div class="ml-4 mt-2 space-y-1">

                    {{-- Purchase Order --}}
                    @if(in_array(Auth::user()->role, ['admin', 'penjualan']))
                    <a href="{{ route('purchase-order.index') }}"
                    class="block px-4 py-2 rounded-lg transition
                    {{ request()->routeIs('purchase-order.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-blue-50' }}">
                        Purchase Order
                    </a>
                    @endif

                    {{-- Quotation --}}
                    @if(in_array(Auth::user()->role, ['admin', 'sales', 'pricing', 'penjualan', 'pracetak']))
                    <a href="{{ route('quotations.index') }}"
                    class="block px-4 py-2 rounded-lg transition
                    {{ request()->routeIs('quotations-order.*') ? 'bg-blue-100 text-blue-700' : 'text-gray-600 hover:bg-blue-50' }}">
                        Quotation
                    </a>
                    @endif

                </div>
            </details>
        @endif
        @if(Auth::user()->role == 'admin' || Auth::user()->role == 'gudang')
        <a href="{{ route('warehouse.index') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('warehouse.*') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50' }}">
            Warehouse
        </a>
        @endif
        @if(Auth::user()->role == 'gudang' || Auth::user()->role == 'production' || Auth::user()->role == 'manufacture')
        <a href="{{ Auth::user()->role == 'manufacture' ? route('manufacture.spk') : route('gudang.spk') }}" 
           class="flex items-center gap-3 px-4 py-3 rounded-xl transition {{ request()->routeIs('gudang.spk') ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-blue-50' }}">
            SPK
        </a>
        @endif
        @if(Auth::user()->role == 'admin')
        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <span class="font-medium">Manajemen Cabang</span>
        </a>
        @endif
        @if(Auth::user()->role != 'penjualan' && Auth::user()->role != 'sales')
        <a href="{{ route('requisition.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('requisition.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <span class="font-medium">Material Requisition</span>
        </a>
        @endif
        @if(Auth::user()->role == 'admin' || Auth::user()->role == 'akuntansi')
        <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('transactions.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-gray-600 hover:bg-blue-50 hover:text-blue-700' }}">
            <span class="font-medium">Akuntansi</span>
        </a>
        @endif
    </nav>
</aside>