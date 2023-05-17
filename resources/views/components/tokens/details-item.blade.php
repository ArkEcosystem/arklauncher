@props (['title'])

<div {{ $attributes->class('flex justify-between py-3') }}>
    <div>{{ $title }}:</div>
    
    <div class="font-semibold text-theme-secondary-900">
        {{ $slot }}
    </div>
</div>
