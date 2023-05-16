<div>
    @if($this->serverId)
    <x-ark-modal class="mx-6 w-full" width-class="max-w-md" wire-close="cancel" title-class="header-2">
            @slot('title')
                @lang('tokens.servers.rename_server_title')
            @endslot

            @slot('description')
                @lang('tokens.servers.rename_server_description')

                <x-ark-input
                    :label="trans('tokens.servers.rename.enter_a_name')"
                    class="mt-5"
                    type="text"
                    id="name"
                    name="name"
                    required
                    autofocus
                    :errors="$errors"
                />
            @endslot

            @slot('buttons')
                <div class="flex justify-end mt-5 space-x-3">
                    <button class="button-secondary" wire:click="cancel">@lang('actions.cancel')</button>
                    <button class="button-primary" wire:click="rename">@lang('actions.save')</button>
                </div>
            @endslot
        </x-ark-modal>
    @endif
</div>
