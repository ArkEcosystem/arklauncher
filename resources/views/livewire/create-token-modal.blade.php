<div>
    @if ($this->token)
        <x-ark-modal
            wire-close="cancel"
            :title="trans('actions.create_token')"
            title-class="header-1"
            width-class="max-w-lg"
        >
            <x-slot name="description">
                <div class="space-y-5">
                    <div class="flex justify-center py-5">
                        <img
                            class="w-64"
                            src="{{ asset('/images/modal/warning.svg') }}"
                            alt="{{ trans('actions.create_token') }}"
                        />
                    </div>

                    <x-ark-alert type="warning">
                        {{ trans('tokens.create_token_modal.description1') }}
                    </x-ark-alert>

                    <div class="input-wrapper">
                        <input type="text" value="{{ $this->token->name }}" class="font-semibold text-center input-text" readonly />
                    </div>

                    <p>{{ trans('tokens.create_token_modal.description2') }}</p>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="flex flex-col justify-end space-y-4 w-full sm:flex-row sm:space-y-0 sm:space-x-3">
                    <button class="button-secondary" wire:click="continue">
                        {{ trans('actions.continue') }}
                    </button>

                    <button class="button-primary" wire:click="deletePendingToken">
                        {{ trans('actions.create_new') }}
                    </button>
                </div>
            </x-slot>
        </x-ark-modal>
    @endif
</div>
