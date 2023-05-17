@props ([
    'provider',
    'enabled',
])

@if ($enabled)
    <div
        :class="{
            'border-theme-primary-500 bg-theme-primary-100': selected === '{{ $provider }}',
            'border-theme-secondary-200': selected !== '{{ $provider }}',
            'cursor-not-allowed': pendingFirstProvider,
        }"
        class="flex flex-col flex-1 items-center py-8 px-5 rounded-lg border-2 transition-colors duration-100 ease-in-out cursor-pointer"
        @click="if (! pendingFirstProvider && ! isLoading) {
            select('{{ $provider }}');
            livewire.emit('setProvider', '{{ $provider }}');
        }"
        data-tippy-content="{{ ServerProviderTypeEnum::label($provider) }}"
        data-tippy-trigger="mouseenter"
    >
        <div class="flex flex-col items-center">
            <div
                class="mb-1 w-24 h-14 bg-center bg-no-repeat bg-contain"
                style="background-image: url('/images/server-providers/{{ $provider }}.svg')"
                :class="{ 'filter grayscale': pendingFirstProvider }"
                wire:target="store"
                wire:loading.class="filter grayscale"
            ></div>
        </div>

        <div class="mt-2">
            <input
                type="radio"
                name="provider"
                value="{{ $provider }}"
                class="form-radio radio-checkmark"
                wire:model="provider"
                :disabled="pendingFirstProvider"
                wire:target="store"
                wire:loading.attr="disabled"
            />
        </div>
    </div>
@else
    <div
        class="flex flex-col flex-1 items-center py-8 px-5 rounded-lg border-2 opacity-75 cursor-not-allowed border-theme-secondary-200"
        data-tippy-content="{{ ServerProviderTypeEnum::label($provider) }}"
        data-tippy-trigger="mouseenter"
    >
        <div class="flex flex-col items-center">
            <div
                class="mb-1 w-24 h-14 bg-center bg-no-repeat bg-contain"
                style="background-image: url('/images/server-providers/{{ $provider }}.svg'); filter: grayscale(100%); -webkit-filter: grayscale(100%);"
            ></div>
        </div>

        <div class="mt-2">
            <span class="text-sm">{{ trans('actions.coming_soon') }}</span>
        </div>
    </div>
@endif
