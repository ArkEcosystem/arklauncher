<div>
    @push('scripts')
        <script src="{{ mix('js/swiper.js')}}"></script>
    @endpush

    <div>
        <x-ark-input name="serverName" :label="trans('forms.create_server.server_name')" :errors="$errors" />
    </div>

    @if ($this->token->hasServerProviders())
        <div class="mt-4" wire:ignore x-data="{ selected: '', select(value) { this.selected = value; } }" x-init="select('{{ $this->selectedProviderType }}')">
            <label class="mb-2 input-label">@lang('forms.create_server.server_provider')</label>
            <x-ark-slider id="providers" space-between="20" pagination-class="flex justify-end" top-pagination hide-navigation>
                @foreach ($this->getUniqueProviders() as $providerOption)
                    <x-ark-slider-slide>
                        <div
                            id="server-{{ $providerOption->type }}"
                            :class="{
                                'border-theme-primary-500 bg-theme-primary-100': selected === '{{ $providerOption->type }}',
                                'border-theme-secondary-200': selected !== '{{ $providerOption->type }}'
                            }"
                            class="flex flex-col flex-1 items-center py-8 px-5 rounded-lg border-2 transition-colors duration-100 ease-in-out cursor-pointer"
                            @click="select('{{ $providerOption->type }}'); livewire.emit('setProvider', '{{ $providerOption->id }}');"
                            data-tippy-content="{{ ServerProviderTypeEnum::label($providerOption->type) }}"
                            data-tippy-trigger="mouseenter"
                        >
                            <div class="flex flex-col items-center">
                                <div class="mb-1 w-24 h-14 bg-center bg-no-repeat bg-contain" style="background-image: url('/images/server-providers/{{ $providerOption->type }}.svg')"></div>
                            </div>
                            <div class="mt-2">
                                <input type="radio" name="provider" value="{{ $providerOption->type }}" class="form-radio radio-checkmark" wire:model="selectedProviderType" />
                            </div>
                        </div>
                    </x-ark-slider-slide>
                @endforeach
            </x-ark-slider>
        </div>
    @endif

    @if ($this->selectedProvider && $this->hasMultipleKeysOnProvider)
        <div>
            <x-ark-select :label="trans('forms.create_server.select_server_provider')" name="selectedProviderSelectedKey" :errors="$errors">
                <option value="">@lang('actions.select_provider')</option>

                @foreach($this->providerEntries as $option)
                    @if ($option->allIndexed())
                        <option value="{{ $option->id }}">{{ $option->name }}</option>
                    @else
                        <option value="{{ $option->id }}" disabled>{{ $option->name }}</option>
                    @endif
                @endforeach
            </x-ark-select>
        </div>
    @endif

    @if(! empty($selectedProvider))
        <div class="{{ $this->hasMultipleKeysOnProvider ? 'my-4' : '' }}">
            <x-ark-select :label="trans('forms.create_server.region')" name="region" :errors="$errors" :disabled="! $this->canSelect()">
                <option value="">@lang('actions.select_region')</label>

                @foreach($this->regions as $option)
                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                @endforeach
            </x-ark-select>
        </div>

        <div class="my-4">
            <x-ark-select :label="trans('forms.create_server.plan')" name="plan" :errors="$errors" :disabled="! $this->region">
                <option value="">@lang('actions.select_plan')</option>

                @if ($this->region)
                    @foreach($this->formattedPlans as $option)
                        <option value="{{ $option->id }}">
                            {{ $option->cores }} CPU | {{ $option->disk }} GB | {{ $option->formatted_memory }} RAM [{{ $option->uuid }}]
                        </option>
                    @endforeach
                @endif
            </x-ark-select>
        </div>
    @endif

    <div class="flex justify-end mt-5">
        <x-tokens.onboard-buttons :state="$this->canSubmit() ? '' : 'disabled'" :token="$token" step="server_config" on-click="store" />
    </div>
</div>
