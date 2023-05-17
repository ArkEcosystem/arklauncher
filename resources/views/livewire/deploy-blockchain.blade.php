<div>
    @if($this->tokenId)
        <x-ark-modal>
            @slot('title')
                @lang('actions.deploy_blockchain')
            @endslot

            @slot('description')
                <div class="flex flex-col sm:flex-row">
                    <span>@lang('tokens.deploy_blockchain_modal.description')</span>
                </div>
                <div class="mt-4">
                    <div>
                        @foreach($this->options as $key => $option)
                            <div
                                class="flex flex-col flex-1 rounded-lg border-2 px-5 py-5 mb-2 cursor-pointer transition-colors ease-in-out duration-100 {{ $this->selectedOption === $key ? 'border-theme-primary-500 bg-theme-primary-100' : 'border-theme-secondary-200' }}"
                                wire:click="$emit('setNetworkOption', '{{ $key }}')"
                            >
                                <div class="mt-5 md:col-span-2 md:mt-0">
                                    <div class="flex items-start">
                                        <div class="flex absolute items-center h-5">
                                            <input
                                                type="checkbox"
                                                id="{{ $key }}"
                                                name="selectedOption"
                                                class="w-4 h-4 transition duration-150 ease-in-out text-theme-primary-600 form-checkbox"
                                                style={{ $key === 'blockchain' ? "pointer-events:none;" : "pointer-events:auto;" }}
                                                {{ $this->selectedOption === $key ? 'checked' : '' }}
                                            />
                                        </div>

                                        <div class="pl-7 leading-5">
                                            <label for="selectedOption" class="font-bold">@lang('forms.create_server.'.$key)</label>
                                            <p class="text-base antialiased">{{ $option }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endslot

            @slot('buttons')
                <div class="flex justify-end mt-5 space-x-3">
                    <button class="button-secondary" wire:click="cancel">@lang('actions.cancel')</button>
                    <button class="inline-flex items-center ml-2 button-primary" wire:click="deploy" {{ ! $this->selectedOption ? 'disabled' : ''}}>
                        @lang('actions.deploy')
                    </button>
                </div>
            @endslot
        </x-ark-modal>
    @endif
</div>
