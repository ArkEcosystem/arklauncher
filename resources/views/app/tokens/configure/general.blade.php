@push('scripts')
    <x-ark-pages-includes-crop-image-scripts />
@endpush

<x-tokens.heading
    :title="trans('pages.token.general.title')"
    :description="trans('pages.token.general.description')"
    :link="trans('urls.documentation.customize_general')"
/>

<x-divider spacing="6" />

<section>
    <div>
        <h2 class="header-4">
            <span>{{ trans('pages.token.import_deployer_config_title') }}</span>
            <span class="text-theme-secondary-500">{{ trans('forms.optional') }}</span>
        </h2>

        <p>{{ trans('pages.token.import_deployer_config_description') }}</p>
    </div>

    <div
        x-data="{
            select() {
                this.$refs.file.click()
            }
        }"
        class="flex relative flex-col items-start mt-5 space-y-3"
    >
        <form>
            <input
                x-ref="file"
                type="file"
                class="block hidden absolute top-0 opacity-0 cursor-pointer"
                wire:model="config"
                accept="application/JSON,text/plain"
            />

            <button
                type="button"
                class="flex flex-row items-center space-x-3 button-secondary"
                @click="select()"
            >
                <x-ark-icon name="arrows.arrow-up-bracket" size="sm" />
                <span>{{ trans('actions.upload') }}</span>
            </button>
        </form>

        @error('config')
            <p class="input-help--error">{{ $message }}</p>
        @enderror
    </div>
</section>

<x-divider spacing="6" />

<section>
    <x-tokens.subheading
        :title="trans('pages.token.general_title')"
        :description="trans('pages.token.general_description')"
        fields="['chainName', 'token', 'symbol']"
    />

    <div class="flex flex-col mt-5 w-full md:flex-row">
        <div class="flex flex-col md:pr-16 md:border-r border-theme-secondary-200">
            <label class="mb-2 input-label">{{ trans('forms.create_token.logo') }}</label>
            <livewire:logo-upload :token="$this->tokenObject" dimensions="w-48 h-48" />
        </div>

        <div class="grid grid-cols-1 gap-4 mt-6 sm:grid-cols-2 md:mt-0 md:ml-16 md:w-full">
            @foreach (['chainName', 'token', 'symbol'] as $item)
                <x-ark-input
                    type="text"
                    :name="$item"
                    :label="trans('forms.create_token.input_' . Str::snake($item))"
                    :placeholder="trans('forms.create_token.input_' . Str::snake($item) . '_placeholder')"
                    :errors="$errors"
                />
            @endforeach
        </div>
    </div>
</section>

<x-divider spacing="6" />

<section>
    <x-tokens.subheading
        :title="trans('pages.token.address_prefix_title')"
        :description="trans('pages.token.address_prefix_description')"
        fields="['mainnetPrefix', 'devnetPrefix', 'testnetPrefix']"
    />

    <div class="grid grid-cols-1 gap-4 mt-5 sm:grid-cols-2 md:grid-cols-3">
        @foreach (['mainnetPrefix', 'devnetPrefix', 'testnetPrefix'] as $item)
            <x-ark-input
                type="text"
                :name="$item"
                :label="trans('forms.create_token.input_' . Str::snake($item))"
                :placeholder="trans('forms.create_token.input_' . Str::snake($item) . '_placeholder')"
                :errors="$errors"
            />
        @endforeach
    </div>
</section>
