<div>
    @if($this->serverId)
        <x-ark-modal class="mx-6 w-full">
            @slot('title')
                @lang('tokens.servers.remove_server_title')
            @endslot

            @slot('description')
                <div class="mt-4">
                    <div class="flex justify-center">
                        <img class="w-2/3" src="{{ asset('images/modal/delete.svg') }}" />
                    </div>
                    <div class="mt-4">@lang('tokens.servers.remove_server_description')</div>
                </div>
            @endslot

            @slot('buttons')
                <div class="flex justify-end mt-5 space-x-3">
                    <button class="button-secondary" wire:click="cancel">@lang('actions.cancel')</button>
                    <button class="inline-flex items-center space-x-2 button-cancel" wire:click="destroy">
                        <x-ark-icon name="trash" size="sm" />
                        <span>@lang('actions.delete')</span>
                    </button>
                </div>
            @endslot
        </x-ark-modal>
    @endif
</div>
