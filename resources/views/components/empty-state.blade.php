@props(['icon' => 'fa-inbox', 'title' => 'Sin registros', 'message' => null])

<div {{ $attributes->merge(['class' => 'text-center py-12 px-4']) }}>
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 dark:text-slate-500 mb-4">
        <i class="fas {{ $icon }} text-2xl"></i>
    </div>
    <p class="font-semibold text-gray-800 dark:text-slate-200">{{ $title }}</p>
    @if($message)
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-2 max-w-md mx-auto">{{ $message }}</p>
    @endif
    @if(trim($slot ?? '') !== '')
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
