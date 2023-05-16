<div>
    @if ($this->requiresConfirmation)
        <x-ark-modal class="mx-6 w-full" width-class="max-w-2xl" wire-close="close">
            @slot('title') @lang('pages.token.overwrite_modal.title') @endslot

            @slot('description')
                <div class="mt-4">
                    @lang('pages.token.overwrite_modal.description')
                </div>
            @endslot

            @slot('buttons')
                <div class="space-x-3">
                    <button class="mt-4 button-secondary" wire:click="emitDefaults()">@lang('actions.fill_empty')</button>
                    <button class="mt-4 button-primary" wire:click="emitDefaults(true)">@lang('actions.overwrite_all')</button>
                </div>
            @endslot
        </x-ark-modal>
    @endif
</div>
