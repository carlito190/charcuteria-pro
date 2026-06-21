@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center px-3 py-2.5 text-xs font-bold bg-indigo-600 text-white rounded-lg shadow-sm transition tracking-wide uppercase'
            : 'flex items-center px-3 py-2.5 text-xs font-medium text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition tracking-wide uppercase';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
