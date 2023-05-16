<div
    x-data="{
        isLoading: false,
        pendingFirstProvider: false,
        selected: '',
        select(value) {
            this.selected = value;
        },
        formSubmit() {
            if (! this.isLoading) {
                this.isLoading = true;

                @this.store().finally(() => this.isLoading = false);
            }
        }
    }"
    @pending-first-provider.window="pendingFirstProvider = ($event.detail === true)"
>
    @push ('scripts')
        <script src="{{ mix('js/swiper.js')}}"></script>
    @endpush

    @if (in_array($this->provider, ['digitalocean', 'hetzner']))
        <x-ark-alert type="info">
            {!! trans('pages.server-providers.'.$this->provider.'_referral') !!}
        </x-ark-alert>
    @endif

    <div
        class="grid grid-cols-2 gap-5 my-8"
        wire:ignore
        x-init="select('{{ ServerProviderTypeEnum::DIGITALOCEAN }}')"
    >
        @foreach (config('deployer.server_providers') as $provider => $enabled)
            <x-server-providers.select :provider="$provider" :enabled="$enabled" />
        @endforeach
    </div>

    <form
        @submit.prevent="formSubmit"
        class="p-5 rounded-lg bg-theme-secondary-100"
    >
        <div class="flex flex-col space-y-4 w-full sm:flex-row sm:space-y-0 sm:space-x-4">
            <x-ark-input
                type="text"
                :tooltip="trans('forms.create_server_provider.profile_name_tooltip')"
                tooltip-class="ml-2"
                name="name"
                :label="trans('forms.create_server_provider.profile_name')"
                autocomplete="off"
                class="w-full"
                :disabled="$this->isSubmittingFirstProvider"
            />

            <x-ark-input
                type="password"
                name="access_token"
                :label="trans('forms.create_server_provider.access_secret')"
                autocomplete="off"
                class="w-full"
                :disabled="$this->isSubmittingFirstProvider"
            />

            @if ($selectedProvider === ServerProviderTypeEnum::AWS)
                <x-ark-input
                    type="password"
                    name="access_key"
                    :label="trans('forms.create_server_provider.access_key')"
                    :disabled="$this->isSubmittingFirstProvider"
                />
            @endif
        </div>

        @if ($providers->isNotEmpty())
            <div class="mt-4 w-full">
                <button
                    type="submit"
                    class="w-full button-secondary"
                    @cannot('create', [Domain\Server\Models\ServerProvider::class, $this->token])
                    disabled
                    @endcannot
                    @if ($this->isSubmittingFirstProvider)
                    disabled
                    @endif
                >
                    {{ trans('actions.add_provider') }}
                </button>
            </div>
        @endif
    </form>

    <div class="mt-4">
        <x-ark-flash />
    </div>

    @if ($providers->isNotEmpty() && ! $this->isSubmittingFirstProvider)
        <section class="mt-8">
            <h2 class="header-2">{{ trans('tokens.server-providers.active_providers') }}</h2>

            <section class="hidden mt-8 md:block">
                <x-server-providers.desktop :providers="$providers" />
            </section>

            <section class="block mt-8 md:hidden">
                <x-server-providers.mobile :providers="$providers" />
            </section>
        </section>
    @endif

    @if($token->canBeEdited())
        <x-divider />

        <div class="flex justify-end">
            @if($providers->isNotEmpty())
                <x-tokens.onboard-buttons
                    :token="$token"
                    step="server_providers"
                    :title="null"
                    :show-cancel="$token->canBeEdited()"
                    :disable-cancel="$this->isSubmittingFirstProvider"
                    :disable-submit="$this->isSubmittingFirstProvider"
                />
            @else
                <x-tokens.onboard-buttons
                    :token="$token"
                    step="server_providers"
                    :title="null"
                    :show-cancel="$token->canBeEdited()"
                    submit-button
                    alpine-click="formSubmit"
                />
            @endif
        </div>

        @if ($this->isSubmittingFirstProvider)
            @livewire('redirect-on-server-provider-completion', ['token' => $token])
        @endif
    @endif

    @livewire('delete-server-provider', ['token' => $token])


</div>
