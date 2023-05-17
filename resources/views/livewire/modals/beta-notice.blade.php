<div>
    @if ($modalShown)
        <x-ark-modal
            class="w-full"
            :title="trans('pages.beta.title')"
            width-class="max-w-134"
            buttons-style="flex flex-col-reverse"
            title-class="inline-block text-2xl font-bold dark:text-theme-secondary-200"
        >
            <x-slot name="description">
                <div class="flex justify-center px-10 mt-8 w-full">
                    <img
                        class="beta-notice"
                        src="{{ asset("images/modal/information.svg") }}"
                    />
                </div>

                <div class="mt-6 w-full">
                    <x-ark-alert>
                        <x-slot name="message">
                            <p>@lang('pages.beta.content_1')</p>
                            <p>@lang('pages.beta.content_2')</p>
                        </x-slot>
                    </x-ark-alert>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="flex justify-between items-center space-x-3">
                    <div>
                        <x-ark-checkbox
                            name="agree"
                            :label="trans('pages.beta.dismiss')"
                            class="mt-0"
                        />
                    </div>

                    <div class="flex items-center space-x-3">
                        <button
                            type="button"
                            class="button-primary"
                            wire:click="close"
                            @if (! $agree)
                                disabled
                            @endif
                        >
                            {{ trans('actions.continue') }}
                        </button>
                    </div>
                </div>
            </x-slot>
        </x-ark-modal>
    @endif
</div>
