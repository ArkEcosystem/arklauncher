<div {{ $attributes->merge(['class' => 'relative']) }}>
    <span class="absolute max-w-full truncate">
        {{ $slot }}
    </span>
    <span class="overflow-hidden h-0">&nbsp;</span>
</div>
