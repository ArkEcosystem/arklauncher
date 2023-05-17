<button class="flex items-center font-semibold link" wire:click="sortBy('{{ $name }}')">
    {{ $slot }}

    @if($sortBy === $name)
        @if($sortDirection === 'desc')
            <x-ark-icon name="arrows.chevron-down-small" size="sm" class="ml-2" />
        @else
            <x-ark-icon name="arrows.chevron-up-small" size="sm" class="ml-2" />
        @endif
    @endif
</button>
