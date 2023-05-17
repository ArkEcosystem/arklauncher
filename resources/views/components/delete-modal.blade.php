@props([
    'actionMethod',
    'closeMethod',
    'title',
    'description',
    'image' => '/images/modal/question.svg',
    'canSubmit' => false,
])

<x-ark-modal
    title-class="header-2"
    width-class="max-w-lg"
    :wire-close="$closeMethod"
>
    <x-slot name="title">
        {{ $title }}
    </x-slot>

    <x-slot name="description">
        <div class="flex flex-col">
            <div class="flex justify-center mt-8 w-full">
                <img
                    src="{{ asset($image) }}"
                    class="w-60"
                    alt="{{ $title }}"
                />
            </div>

            <div class="mt-8">
                {{ $description }}
            </div>
        </div>
    </x-slot>

    <x-slot name="buttons">
        <div class="flex flex-col-reverse justify-end space-y-4 space-y-reverse w-full sm:flex-row sm:space-y-0 sm:space-x-3">
            <button
                type="button"
                dusk="confirm-password-form-cancel"
                class="button-secondary"
                wire:click="{{ $closeMethod }}"
            >
                {{ trans('actions.cancel') }}
            </button>

            <button
                type="submit"
                dusk="confirm-password-form-submit"
                class="inline-flex justify-center items-center space-x-2 button-cancel"
                wire:click="{{ $actionMethod }}"
                @unless($canSubmit)
                    disabled
                @endunless
            >
                <x-ark-icon name="trash" size="sm" />

                <span>{{ trans('actions.delete') }}</span>
            </button>
        </div>
    </x-slot>
</x-ark-modal>
