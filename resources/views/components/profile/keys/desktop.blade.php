@props (['keys'])

<x-ark-tables.table sticky class="min-w-full">
    <thead>
        <tr>
            <x-ark-tables.header class="w-56">
                {{ trans('tables.name') }}
            </x-ark-tables.header>

            <x-ark-tables.header class="w-96">
                {{ trans('tables.fingerprint') }}
            </x-ark-tables.header>

            <x-ark-tables.header class="last-cell">
                {{ trans('tables.date_added') }}
            </x-ark-tables.header>

            <x-ark-tables.header class="w-33" />
        </tr>
    </thead>

    <tbody>
        @foreach ($keys as $key)
            <x-ark-tables.row>
                <x-ark-tables.cell>
                    <x-truncated-cell class="w-full font-bold text-theme-primary-500">
                        {{ $key->name }}
                    </x-truncated-cell>
                </x-ark-tables.cell>

                <x-ark-tables.cell>
                    <span class="block truncate">
                        {{ $key->fingerprint }}
                    </span>
                </x-ark-tables.cell>

                <x-ark-tables.cell><span class="whitespace-nowrap">{{ $key->created_at_local->format(DateFormat::DATE) }}</span></x-ark-tables.cell>

                <x-ark-tables.cell>
                    <button type="button" class="button-cancel" wire:click="$emit('deleteSecureShellKey', '{{ $key->id }}')">
                        <x-ark-icon name="trash" size="sm" />
                    </button>
                </x-ark-tables.cell>
            </x-ark-tables.row>
        @endforeach
    </tbody>
</x-ark-tables.table>
