<div>
    <div class="flex flex-col">
        @if ($this->keys->count())
            @can ('manageKeys', $token)
                <div class="flex justify-end items-center font-semibold divide-x select-none divide-theme-secondary-200">
                    <span class="pr-2 cursor-pointer link" wire:click="selectAll" role="button">@lang('actions.select_all')</span>
                    <span class="pl-2 cursor-pointer link" wire:click="deselectAll" role="button">@lang('actions.deselect_all')</span>
                </div>
            @endif

            <div class="mt-2 space-y-5">
            @foreach($this->keys as $option)
                <div
                    @class([
                        'flex flex-col flex-1 rounded-lg border-2 cursor-pointer transition-colors ease-in-out duration-100',
                        'border-theme-primary-500 bg-theme-primary-50' => in_array($option->id, $this->selectedOptions, true) || $this->isRegistered($option->id),
                        'border-theme-secondary-200' => ! (in_array($option->id, $this->selectedOptions, true) || $this->isRegistered($option->id)),
                    ])
                >
                    <label
                        for="selected_option-{{ $option->id }}"
                        class="flex overflow-x-auto justify-between items-center p-5 w-full min-w-0 text-sm font-semibold cursor-pointer select-none"
                    >
                        <div class="flex-shrink-0">
                            <input
                                type="checkbox"
                                id="selected_option-{{ $option->id }}"
                                name="selectedOption"
                                class="w-5 h-5 transition duration-150 ease-in-out cursor-pointer text-theme-success-600 input-checkbox form-checkbox"
                                {{ in_array($option->id, $this->selectedOptions) ? 'checked' : '' }}
                                {{ $this->isRegistered($option->id) ? 'checked' : '' }}
                                @can ('manageKeys', $token)
                                wire:change="$emit('setOption', '{{ $option->id }}')"
                                @else
                                disabled
                                @endcan
                            />
                        </div>

                        <div class="flex overflow-x-auto flex-col justify-between ml-3 w-full text-sm font-semibold whitespace-nowrap sm:flex-row">
                            <span class="flex overflow-x-auto mr-2">
                                <span class="flex-shrink-0">{{ $option->name }} (</span>
                                <span class="truncate">{{ $option->user->name }}</span>
                                <span class="flex-shrink-0">)</span>
                            </span>

                            <span class="antialiased text-theme-secondary-500">
                                {{ $option->created_at_local->format(DateFormat::TIME_PARENTHESES) }}
                            </span>
                        </div>
                    </label>
                </div>
            @endforeach
            </div>

            @if($token->canBeEdited())
                <div class="mt-6">
                    <button class="w-full button-secondary" wire:click="toggleModal">@lang('tokens.secure-shell-keys.add_new_ssh_key')</button>
                </div>
            @endif

            <div @class([
                'justify-end' => $token->canBeEdited(),
                'justify-between' => !$token->canBeEdited(),
                'flex flex-col sm:flex-row mt-8',
            ])>
                @if(!$token->canBeEdited())
                    <div class="mb-5 w-full sm:mt-5 sm:mb-0">
                        <span>{!! trans('pages.user-settings.additional_ssh_keys', ['href' => route('user.ssh-keys')]) !!}</span>
                    </div>
                @endif

                @can ('manageKeys', $token)
                    <x-tokens.onboard-buttons
                        :token="$token"
                        step="secure_shell_keys"
                        :title="$token->canBeEdited() ? trans('actions.continue') : trans('actions.save')"
                        :disable-submit="!$this->canSubmit"
                        :show-cancel="$token->canBeEdited()"
                        on-click="store"
                    />
                @endcan
            </div>

            @if ($this->modalShown)
                <x-ark-modal>
                    @slot('title')
                        @lang('pages.user-settings.create_ssh_title')
                    @endslot

                    @slot('description')
                        <div class="flex flex-col">
                            <span>@lang('pages.user-settings.create_ssh_description')</span>

                            <form class="space-y-4" wire:submit.prevent="storeKey">
                                <x-ark-input type="text" name="name" :label="trans('forms.ssh_key.input_name')" :errors="$errors" />
                                <x-ark-textarea :rows="3" name="public_key" :label="trans('forms.ssh_key.input_public_key')" :errors="$errors" />
                            </form>
                        </div>
                    @endslot

                    @slot('buttons')
                        <div class="mt-5 space-x-3 sm:flex sm:mt-4">
                            <button class="button-secondary" wire:click="toggleModal">@lang('actions.cancel')</button>
                            <button type="button" class="button-primary" wire:click="storeKey">@lang('actions.create')</button>
                        </div>
                    @endslot
                </x-ark-modal>
            @endif
        @else
            <span>@lang('pages.user-settings.create_ssh_description')</span>

            <form class="space-y-4" wire:submit.prevent="storeKey">
                <x-ark-input type="text" name="name" :label="trans('forms.ssh_key.input_name')" :errors="$errors" />
                <x-ark-textarea :rows="3" name="public_key" :label="trans('forms.ssh_key.input_public_key')" :errors="$errors" />
            </form>

            <div class="flex justify-end mt-3">
                <x-tokens.onboard-buttons
                    class="inline-block"
                    :token="$token"
                    step="secure_shell_keys"
                    :title="trans('actions.create')"
                    on-click="storeKey"
                />
            </div>
        @endif
    </div>
</div>
