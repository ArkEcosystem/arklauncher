<div>
    @if ($this->tokenId)
        <x-ark-modal
            class="w-full md:mx-auto"
            width-class="max-w-xl"
            wire-close="close">
            @slot('title') @lang('pages.user-settings.teams.leave_title') @endslot

            @slot('description')
                <div class="mt-4">
                    <div class="flex justify-center">
                        <img class="w-64" src="{{ asset('images/modal/warning.svg') }}" />
                    </div>

                    <x-ark-alert
                        class="mt-6"
                        type="warning"
                        :message="trans('pages.user-settings.teams.leave_description', [
                            'token' => $this->token->name,
                        ])"
                    />
                </div>
            @endslot

            @slot('buttons')
                <div class="flex justify-end space-x-3">
                    <button class="button-secondary" wire:click="close()">@lang('actions.cancel')</button>
                    <button class="inline-flex items-center space-x-2 button-primary" wire:click="leave()">
                        <span>@lang('actions.leave')</span>
                    </button>
                </div>
            @endslot
        </x-ark-modal>
    @endif
</div>

