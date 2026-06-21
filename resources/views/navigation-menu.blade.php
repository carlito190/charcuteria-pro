<nav class="w-full flex flex-col justify-between h-screen text-slate-700 border-r border-slate-200 shadow-sm">

    {{-- CONTENEDOR SUPERIOR: LOGO Y ENLACES --}}
    <div class="flex flex-col flex-1">

        {{-- CABECERA (Logo + Botón Móvil) --}}
        <div class="h-16 flex items-center justify-between px-5 bg-slate-50 lg:bg-transparent border-b border-slate-200 lg:border-b-0">
            <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                <x-application-mark class="block h-8 w-auto text-indigo-600" />
                <span class="font-black text-xs tracking-wider text-slate-900 uppercase hidden lg:inline-block">
                    CJ JIREH MARKET
                </span>
            </a>

            {{-- Hamburguesa para pantallas móviles reales --}}
            <div class="flex items-center lg:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-slate-500 hover:text-slate-800 hover:bg-slate-100 focus:outline-none transition">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- MENÚ DE NAVEGACIÓN (Forzado a bloquearse o esconderse según el dispositivo) --}}
        <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:block lg:flex-1 px-3 py-4 space-y-6 overflow-y-auto max-h-[calc(100vh-8rem)] scrollbar-thin">

            {{-- SECCIÓN: OPERACIONES DIARIAS --}}
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Transacciones</p>
                <div class="space-y-0.5">
                    <x-sidebar-link href="{{ route('sales.create') }}" :active="request()->routeIs('sales.create')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ __('Registrar Venta') }}
                    </x-sidebar-link>

                    <x-sidebar-link href="{{ route('purchases') }}" :active="request()->routeIs('purchases')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        {{ __('Registrar Compra') }}
                    </x-sidebar-link>

                    <x-sidebar-link href="{{ route('transfers.index') }}" :active="request()->routeIs('transfers.index')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                        {{ __('Transferencias') }}
                    </x-sidebar-link>
                </div>
            </div>

            {{-- SECCIÓN: HISTORIALES Y REPORTES --}}
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Historiales</p>
                <div class="space-y-0.5">
                    <x-sidebar-link href="{{ route('sales.index') }}" :active="request()->routeIs('sales.index')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                        {{ __('Historial de Ventas') }}
                    </x-sidebar-link>

                    <x-sidebar-link href="{{ route('purchases.index') }}" :active="request()->routeIs('purchases.index')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        {{ __('Historial de Compras') }}
                    </x-sidebar-link>
                </div>
            </div>

            {{-- SECCIÓN: INVENTARIO Y ALMACÉN --}}
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Almacén</p>
                <div class="space-y-0.5">
                    <x-sidebar-link href="{{ route('products') }}" :active="request()->routeIs('products')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                        {{ __('Productos (Stock)') }}
                    </x-sidebar-link>

                    <x-sidebar-link href="{{ route('providers') }}" :active="request()->routeIs('providers')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        {{ __('Proveedores') }}
                    </x-sidebar-link>

                     <x-sidebar-link href="{{ route('clients.index') }}" :active="request()->routeIs('clients.index')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        {{ __('Clientes') }}
                    </x-sidebar-link>

                    <x-sidebar-link href="{{ route('cxc.index') }}" :active="request()->routeIs('cxc.index')">
                        {{-- 📊 Icono optimizado para Cuentas por Cobrar (Estilo Reporte Financiero/Recibo) --}}
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21m-15-16.5h15M3 6.75h18M3 12h18M3 17.25h18" />
                        </svg>
                        {{ __('Cuentas por Cobrar') }}
                    </x-sidebar-link>

                    <x-sidebar-link href="{{ route('categories') }}" :active="request()->routeIs('categories')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" /></svg>
                        {{ __('Categorías') }}
                    </x-sidebar-link>

                    <x-sidebar-link href="{{ route('brands.index') }}" :active="request()->routeIs('brands.index')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ __('Marcas') }}
                    </x-sidebar-link>
                </div>
            </div>

            {{-- SECCIÓN: CONFIGURACIONES GENERALES --}}
            <div>
                <p class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Estructura</p>
                <div class="space-y-0.5">
                    <x-sidebar-link href="{{ route('branches') }}" :active="request()->routeIs('branches')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0V11m0 5H9m11-3a2 2 0 11-4 0 2 2 0 014 0zM7 7h.01M7 11h.01M7 15h.01" /></svg>
                        {{ __('Sucursales') }}
                    </x-sidebar-link>

                    <x-sidebar-link href="{{ route('exchange-rates') }}" :active="request()->routeIs('exchange-rates')">
                        <svg class="size-4 me-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ __('Tasas de Cambio') }}
                    </x-sidebar-link>
                </div>
            </div>

        </div>
    </div>

    {{-- CONTENEDOR INFERIOR: USUARIO --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:block bg-slate-50 p-3 border-t border-slate-200">
        <div class="relative">
            <x-dropdown align="top-left" width="48">
                <x-slot name="trigger">
                    <button class="w-full flex items-center p-2 rounded-lg hover:bg-slate-200/50 transition text-left focus:outline-none">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <img class="size-8 rounded-full object-cover border border-slate-200" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                        @else
                            <div class="size-8 rounded-full bg-indigo-600 flex items-center justify-center font-bold text-xs text-white uppercase shadow-sm">
                                {{ substr(Auth::user()->name, 0, 2) }}
                            </div>
                        @endif
                        <div class="ms-3 flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-800 truncate uppercase">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-slate-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <svg class="size-4 text-slate-400 ms-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" /></svg>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <div class="block px-4 py-1.5 text-[10px] font-black text-slate-400 uppercase tracking-wider">
                        {{ __('Administración') }}
                    </div>
                    <x-dropdown-link href="{{ route('profile.show') }}" class="text-xs uppercase font-medium">
                        {{ __('Mi Perfil') }}
                    </x-dropdown-link>
                    <x-dropdown-link href="{{ route('users.index') }}" class="text-xs uppercase font-medium">
                        {{ __('Gestión de Usuarios') }}
                    </x-dropdown-link>
                    <div class="border-t border-gray-200 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <x-dropdown-link href="{{ route('logout') }}" @click.prevent="$root.submit();" class="text-xs uppercase font-bold text-red-600 hover:bg-red-50">
                            {{ __('Cerrar Sesión') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</nav>
