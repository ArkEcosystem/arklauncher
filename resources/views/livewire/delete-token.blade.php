<div>
    @if($this->tokenId)
        <x-ark-modal>
            @slot('title')
                @lang('actions.delete_token')
            @endslot

            @slot('description')
                <div class="flex flex-col sm:flex-row">
                    <span>@lang('tokens.delete_token_modal.description')</span>
                </div>
                <div class="mt-4">
                    <div>
                        @foreach($this->options as $key => $option)
                            @if ($key === 'blockchain')
                                <div
                                    class="flex flex-col flex-1 py-5 px-5 mb-2 rounded-lg border-2 transition-colors duration-100 ease-in-out cursor-pointer bg-theme-primary-100 border-theme-primary-500"
                                >
                            @else
                                <div
                                    class="{{ $this->shouldBeDisabled($key) ? 'disabled' : ''}} flex flex-col flex-1 rounded-lg border-2 px-5 py-5 mb-2 cursor-pointer transition-colors ease-in-out duration-100 {{ in_array($key, $this->selectedOptions) ? 'border-theme-primary-500 bg-theme-primary-100' : 'border-theme-secondary-200' }}"
                                    wire:click="$emit('setOption', '{{ $key }}')"
                                >
                            @endif
                                <div class="mt-5 md:col-span-2 md:mt-0">
                                    <div class="flex items-start">
                                        <div class="flex absolute items-center h-5">
                                            <input
                                                type="checkbox"
                                                id="{{ $key }}"
                                                name="selectedOption"
                                                class="w-4 h-4 transition duration-150 ease-in-out text-theme-primary-600 form-checkbox"
                                                style={{ $key === 'blockchain' ? "pointer-events:none;" : "pointer-events:auto;" }}
                                                {{ in_array($key, $this->selectedOptions) ? 'checked' : '' }}
                                                @if($this->shouldBeDisabled($key)) disabled @endif
                                            />
                                        </div>
                                        <div class="pl-7 leading-5">
                                            <label for="selectedOption" class="font-bold">{{ Str::title($key) }}</label>
                                            <p class="text-base antialiased text-theme-secondary-500">{{ $option }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col mt-4">
                    <span class="input-label">@lang('tokens.delete_token_modal.name')</span>
                    <div class="mb-2 input-wrapper">
                        <input type="text" value="{{ $this->token->name }}" class="text-center input-text" readonly/>
                    </div>
                    <x-ark-input name="token_name" label=" " :placeholder="trans('tokens.delete_token_modal.confirmation_placeholder')"></x-ark-input>
                </div>
            @endslot

            @slot('buttons')
                <div class="flex justify-end mt-5 space-x-3">
                    <button class="button-secondary" wire:click="cancel">@lang('actions.cancel')</button>
                    <button class="inline-flex items-center button-cancel" wire:click="destroy" {{ count($this->selectedOptions) === 0 || ! $this->hasConfirmedName() ? 'disabled' : ''}}>
                        <x-ark-icon name="trash" size="sm" />
                        <span class="ml-2">@lang('actions.delete')</span>
                    </button>
                </div>
            @endslot
        </x-ark-modal>
    @endif
</div>
