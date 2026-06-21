<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    {{-- Agregamos x-data para controlar globalmente si el menú está colapsado o no --}}
    <body class="font-sans antialiased bg-gray-100" x-data="{ sidebarOpen: true }">
        <x-banner />

        {{-- Contenedor Flex Principal --}}
        <div class="min-h-screen flex flex-col lg:flex-row relative overflow-hidden">

            {{-- MENÚ LATERAL IZQUIERDO (Se oculta dinámicamente con transiciones suaves) --}}
            <div :class="sidebarOpen ? 'w-full lg:w-64 opacity-100' : 'w-0 lg:w-0 opacity-0 pointer-events-none'"
                 class="transition-all duration-300 ease-in-out bg-white overflow-hidden shrink-0 z-30">
                @livewire('navigation-menu')
            </div>

            {{-- CONTENEDOR DERECHO: El resto de las páginas del sistema --}}
            <div class="flex-1 w-full min-w-0 flex flex-col">

                {{-- ENCABEZADO SUPERIOR: Aquí va la barra con el botón de las 3 rayas --}}
                <header class="bg-white shadow-sm border-b border-slate-200 h-16 flex items-center shrink-0 px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between w-full">

                        {{-- Botón Hamburguesa (3 rayas) --}}
                        <button @click="sidebarOpen = !sidebarOpen" class="inline-flex items-center justify-center p-2 rounded-md text-slate-500 hover:text-slate-800 hover:bg-slate-100 focus:outline-none transition-all mr-4">
                            <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        {{-- Título o Slot del Header --}}
                        <div class="flex-1">
                            @if (isset($header))
                                {{ $header }}
                            @endif
                        </div>
                    </div>
                </header>

                {{-- Contenido del módulo cargado (ej. Inventario, Transferencias) --}}
                <main class="flex-1 p-4 sm:p-6 lg:px-4 lg:py-6 overflow-y-auto">
                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('modals')
        @livewireScripts
    </body>
</html>
