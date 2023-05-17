@props (['label'])

<div {{ $attributes->class('flex justify-between space-x-6') }}>
    <div class="whitespace-nowrap">
        {{ $label }}
    </div>

    <div class="overflow-auto">
        {{ $slot }}
    </div>
</div>
