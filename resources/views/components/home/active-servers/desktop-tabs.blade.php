@props([
    'networks',
    'default',
])

<div class="hidden md:block">
    <x-ark-tabbed
        class="relative"
        panel-wrapper-class="mt-6 w-full"
        tablist-class="relative w-full"
        :default-selected="$default"
        on-selected="function (value) {
            livewire.emit('selectNetwork', value);

            window.dispatchEvent(new CustomEvent('tab-selected', { detail: tab }));
        }"
        x-data="{
            onTabSelected({ detail: tab })    {
                if (this.selected !== tab) {
                    this.select(tab);
                }
            }
        }"
        @tab-selected.window="onTabSelected"
    >
        <x-slot name="tabs">
            @foreach ($networks as $network)
                <x-ark-tab
                    :name="$network->id"
                    class="flex items-center space-x-2 focus-visible:rounded"
                    selected-class="focus-visible:border-transparent"
                >
                    <span>@lang('tokens.'.$network->name)</span>

                    <div class="flex justify-center items-center ml-3 w-6 h-6 text-sm rounded text-theme-secondary-500 bg-theme-secondary-200">
                        {{ $network->servers_count }}
                    </div>
                </x-ark-tab>
            @endforeach

            <x-home.active-servers.type-filter />
        </x-slot>
    </x-ark-tabbed>
</div>
