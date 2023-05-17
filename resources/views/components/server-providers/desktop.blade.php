@props (['providers'])

<x-ark-tables.table sticky class="min-w-full">
    <thead>
        <tr>
            <x-ark-tables.header class="w-2/3">
                {{ trans('tables.name') }}
            </x-ark-tables.header>

            <x-ark-tables.header class="last-cell">
                {{ trans('tables.date_added') }}
            </x-ark-tables.header>

            <x-ark-tables.header />
        </tr>
    </thead>

    <tbody>
        @foreach ($providers as $provider)
            <x-ark-tables.row>
                <x-ark-tables.cell class="w-2/3">
                    <div class="flex items-center space-x-3">
                        <span>
                            <img src="{{ asset('/images/server-providers/'.$provider->type.'.svg') }}" class="w-8" alt="">
                        </span>

                        <span class="block font-bold text-theme-primary-500 truncate">
                            {{ $provider->name }}
                        </span>
                    </div>
                </x-ark-tables.cell>

                <x-ark-tables.cell>{{ $provider->created_at_local->format(DateFormat::DATE) }}</x-ark-tables.cell>

                <x-ark-tables.cell>
                    <div class="flex justify-end w-full">
                        <button
                            type="button"
                            class="button-cancel"
                            wire:click="$emit('deleteServerProvider', '{{ $provider->id }}')"
                            @cannot ('delete', $provider)
                            disabled
                            @endcannot
                        >
                            <x-ark-icon name="trash" size="sm" />
                        </button>
                    </div>
                </x-ark-tables.cell>
            </x-ark-tables.row>
        @endforeach
    </tbody>
</x-ark-tables.table>
