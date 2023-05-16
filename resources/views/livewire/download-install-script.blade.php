<div class="relative">
    @php($availableNetworks = $this->availableNetworks())
    @php($hasAvailableNetworks = count($availableNetworks) > 0)

    <x-ark-dropdown
        wrapper-class="inline-block items-center w-full sm:w-auto"
        button-class="justify-center space-x-2 w-full sm:w-auto button-secondary"
        :close-on-click="true"
        :disabled="!$hasAvailableNetworks"
    >
        <x-slot name="button">
            <x-ark-icon
                name="arrows.arrow-down-bracket"
                size="sm"
            />

            <span>@lang('actions.install_script')</span>
        </x-slot>
        <div class="block justify-center items-center py-3">
            @foreach($availableNetworks as $network)
                <div
                    class="block py-4 px-10 w-full font-semibold text-left capitalize cursor-pointer focus-visible:rounded text-theme-secondary-900 hover:bg-theme-secondary-100"
                    wire:click="download('{{ $network }}')"
                >
                    {{ ucfirst($network) }}
                </div>
            @endforeach
        </div>
    </x-ark-dropdown>
</div>
