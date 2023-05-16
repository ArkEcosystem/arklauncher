@props (['providers'])

<div class="table-list-mobile">
    @foreach ($providers as $provider)
        <div class="space-y-4 {{ $loop->first ? '' : 'mt-8 pt-8 border-t border-theme-secondary-300 border-dashed' }}">
            <x-server-providers.item :label="trans('tables.name')">
                <div class="flex items-center space-x-3">
                    <span>
                        <img src="{{ asset('/images/server-providers/'.$provider->type.'.svg') }}" class="w-6" alt="">
                    </span>

                    <span class="block font-bold text-theme-primary-500 truncate">
                        {{ $provider->name }}
                    </span>
                </div>
            </x-server-providers.item>

            <x-server-providers.item :label="trans('tables.date_added')">
                {{ $provider->created_at_local->format(DateFormat::DATE) }}
            </x-server-providers.item>

            @can ('delete', $provider)
                <div class="flex sm:justify-end">
                    <button
                        class="flex justify-center items-center space-x-2 w-full sm:w-auto button-cancel"
                        wire:click="$emit('deleteServerProvider', '{{ $provider->id }}')"
                    >
                        <x-ark-icon name="trash" size="sm" />
                        <span class="block sm:hidden">{{ trans('actions.delete') }}</span>
                    </button>
                </div>
            @endcan
        </div>
    @endforeach
</div>
