<div>
    @if ($this->serverProviderId && $this->modalShown)
        <x-ark-modal
            title-class="header-2"
            width-class="max-w-lg"
            wire-close="cancel"
            :title="trans('tokens.server-providers.remove_server_provider_title')"
        >
            <x-slot name="description">
                <div class="mt-4">
                    <div class="flex justify-center">
                        <img
                            class="w-60"
                            src="{{ asset('images/modal/delete.svg') }}"
                            alt="{{ trans('tokens.server-providers.remove_server_provider_title') }}"
                        />
                    </div>

                    <p class="mt-4 mb-4">
                        {{ trans('tokens.server-providers.remove_server_provider_description') }}
                    </p>

                    <x-ark-alert-simple
                        type="warning"
                        :message="trans('tokens.server-providers.remove_server_provider_disclaimer')"
                    />

                    <label class="flex items-start py-5 px-5 mt-5 mb-2 rounded-lg border-2 transition-colors duration-100 ease-in-out bg-theme-primary-100 border-theme-primary-500" role="button" wire:click="toggleDeleteOnProvider">
                        <div class="flex absolute items-center h-5">
                            <input
                                type="checkbox"
                                name="selectedOption"
                                class="w-4 h-4 transition duration-150 ease-in-out text-theme-success-600 form-checkbox"
                                wire:model="deleteOnProvider"
                            />
                        </div>

                        <div class="pl-7 leading-5">
                            <p class="font-semibold">
                                {{ trans('tokens.server-providers.remove_server_provider_servers_title') }}
                            </p>

                            <p class="text-base antialiased text-theme-secondary-500">
                                {{ trans('tokens.server-providers.remove_server_provider_servers') }}
                            </p>
                        </div>
                    </label>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="flex flex-col-reverse justify-end space-y-4 space-y-reverse w-full sm:flex-row sm:space-y-0 sm:space-x-3">
                    <button class="button-secondary" wire:click="cancel">
                        {{ trans('actions.cancel') }}
                    </button>

                    <button class="inline-flex justify-center items-center space-x-2 button-cancel" wire:click="destroy">
                        <x-ark-icon name="trash" size="sm" />
                        <span>{{ trans('actions.delete') }}</span>
                    </button>
                </div>
            </x-slot>
        </x-ark-modal>
    @endif
</div>
