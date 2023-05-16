@props([
    'selected',
    'networks',
])

<div
    x-data="{
        dropdownOpen: false,
        selected: '{{ $selected->id }}',
        select(tab) {
            this.selected = tab;

            window.dispatchEvent(new CustomEvent('tab-selected', { detail: tab }));
            livewire.emit('selectNetwork', value);
        },
        onTabSelected({ detail: tab })    {
            if (this.selected !== tab) {
                this.select(tab);
            }
        }
    }"
    class="mb-5 md:hidden"
    @tab-selected.window="onTabSelected"
>
    <x-ark-dropdown
        wrapper-class="relative p-2 w-full rounded-xl border border-theme-primary-100 dark:border-theme-secondary-800"
        button-class="p-3 w-full font-semibold text-left text-theme-secondary-900 dark:text-theme-secondary-200"
        dropdown-classes="left-0 w-full z-20"
        :init-alpine="false"
        dropdown-property="dropdownOpen"
    >
        <x-slot name="button">
            <div class="flex items-center space-x-4">
                <div wire:ignore>
                    <div x-show="dropdownOpen !== true">
                        <x-ark-icon name="menu" size="sm" />
                    </div>

                    <div x-show="dropdownOpen === true">
                        <x-ark-icon name="menu-show" size="sm" />
                    </div>
                </div>

                <div>@lang('tokens.'.$selected->name)</div>
            </div>
        </x-slot>

        <div class="block items-center py-3 mt-1">
            @foreach ($networks as $network)
                <button
                    type="button"
                    wire:key="{{ $network->id }}"
                    @click="select('{{ $network->id }}')"
                    @class([
                        'dropdown-entry',
                        'dropdown-entry-selected' => $selected->id === $network->id,
                    ])
                >
                    @lang('tokens.'.$network->name)
                </button>
            @endforeach
        </div>
    </x-ark-dropdown>
</div>
