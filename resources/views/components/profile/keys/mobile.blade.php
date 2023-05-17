@props (['keys'])

<div class="table-list-mobile">
    @foreach ($keys as $key)
        <div class="space-y-4 {{ $loop->first ? '' : 'mt-8 pt-8 border-t border-theme-secondary-300 border-dashed' }}">
            <x-profile.keys.item :label="trans('tables.name')">
                <span class="block font-bold text-theme-primary-500 truncate">
                    {{ $key->name }}
                </span>
            </x-profile.keys.item>

            <x-profile.keys.item :label="trans('tables.fingerprint')">
                <span class="block truncate">
                    {{ $key->fingerprint }}
                </span>
            </x-profile.keys.item>

            <x-profile.keys.item :label="trans('tables.date_added')">
                {{ $key->created_at_local->format(DateFormat::DATE) }}
            </x-profile.keys.item>

            <div class="flex sm:justify-end">
                <button
                    class="flex justify-center items-center space-x-2 w-full sm:w-auto button-cancel"
                    wire:click="$emit('deleteSecureShellKey', '{{ $key->id }}')">
                    <x-ark-icon name="trash" size="sm" />
                    <span class="block sm:hidden">{{ trans('actions.delete') }}</span>
                </button>
            </div>
        </div>
    @endforeach
</div>
