@component('layouts.token', ['token' => $token])
    @push('scripts')
        <script src="{{ asset('js/file-download.js')}}"></script>
    @endpush

    @slot('title')
        @lang('pages.details.page_name')
    @endslot

    <div class="flex flex-col">
        <section class="pb-8 mt-8 border-b border-theme-secondary-200">
            <div class="xl:flex xl:space-x-8">
                <div class="xl:w-1/2">
                    <h3 class="pb-1 xl:pb-3 header-4">@lang('pages.details.about')</h3>

                    <div class="divide-y divide-theme-secondary-200">
                        <x-tokens.details-item :title="trans('pages.details.symbol')">
                            {{ $configuration['symbol'] }}
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.token_name')">
                            {{ $configuration['token'] }}
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.total_premine')">
                            {{ Format::readableCrypto((int) $configuration['totalPremine']) }}
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.reward_per_block')">
                            {{ Format::readableCrypto((int) $configuration['rewardPerBlock']) }} {{ $configuration['token'] }}
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.blocktime')">
                            {{ $configuration['blocktime'] }} s
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.transactions_per_block')">
                            {{ $configuration['transactionsPerBlock'] }}
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.forgers')">
                            {{ $configuration['forgers'] }}
                        </x-tokens.details-item>
                    </div>
                </div>

                <div class="mt-8 xl:mt-0 xl:w-1/2">
                    <h3 class="pb-1 xl:pb-3 header-4">@lang ('pages.details.network_details')</h3>

                    <div class="divide-y divide-theme-secondary-200">
                        <x-tokens.details-item :title="trans('pages.details.prefixes')">
                            <div class="flex space-x-3 font-semibold">
                                <span class="pr-3 border-r border-theme-secondary-200">{{ $configuration['mainnetPrefix'] }}</span>
                                <span class="pr-3 border-r border-theme-secondary-200">{{ $configuration['devnetPrefix'] }}</span>
                                <span>{{ $configuration['testnetPrefix'] }}</span>
                            </div>
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.p2p_port')">
                            {{ $configuration['p2pPort'] }}
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.api_port')">
                            {{ $configuration['apiPort'] }}
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.webhook_port')">
                            {{ $configuration['webhookPort'] }}
                        </x-tokens.details-item>

                        <x-tokens.details-item :title="trans('pages.details.explorer_port')">
                            {{ $configuration['explorerPort'] }}
                        </x-tokens.details-item>
                    </div>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row md:flex-col-reverse lg:flex-row mt-5 @can('delete', $token) justify-between @else justify-end @endcan">
                @can ('delete', $token)
                    <button
                        onclick="window.livewire.emit('deleteToken', {{ $token->id }})"
                        class="inline-flex justify-center items-center mt-3 w-full sm:mt-0 sm:w-auto md:mt-3 lg:mt-0 button-cancel"
                    >
                        <x-ark-icon name="trash" size="sm" class="mr-2" />
                        <span>@lang ('actions.delete_blockchain')</span>
                    </button>
                @endcan

                <div class="flex flex-col space-y-3 w-full sm:flex-row sm:space-y-0 sm:space-x-3 sm:w-auto md:justify-between lg:justify-start">
                    <livewire:download-install-script :token="$token" />

                    <x-ark-file-download
                        :filename="'configuration_' . $token->slug"
                        :content="json_encode($configuration)"
                        :title="trans('pages.details.config')"
                        class="justify-center w-full sm:w-auto"
                        wrapper-class="w-full sm:w-auto"
                    />
                </div>
            </div>
        </section>

        <section class="pb-8 mt-8 border-b border-theme-secondary-200">
            <h2 class="header-2">@lang ('pages.details.important_links.title')</h2>

            <div class="flex items-center mt-6 sm:space-x-4 md:mt-8">
                <div class="hidden flex-shrink-0 justify-center items-center sm:flex">
                    <img src="{{ asset('/images/server/block-explorer.svg') }}" class="w-11 h-11" />
                </div>

                <div class="flex flex-col">
                    <h4 class="text-base font-bold">@lang ('pages.details.important_links.explorer.title')</h4>

                    <div class="flex items-center mt-0.5 space-x-6">
                        @foreach (NetworkTypeEnum::all() as $network)
                            @if ($token->network($network)->hasGenesis())
                                <x-ark-external-link
                                    :url="'http://'.$token->network($network)->getGenesis()->ip_address.':'.$configuration['explorerPort']"
                                    :text="trans('pages.details.important_links.explorer.networks.'.$network)"
                                />
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-8">
            <h2 class="header-2">@lang ('pages.details.useful_resources.title')</h2>

            <div class="grid grid-cols-1 gap-x-8 mt-2 divide-y divide-dashed md:mt-4 xl:grid-cols-2 xl:divide-y-0 divide-theme-secondary-200">
                @php ($usefulResources = collect(trans('pages.details.useful_resources.links'))->splitIn(2))

                @foreach ($usefulResources as $links)
                    <div class="divide-y divide-dashed divide-theme-secondary-200">
                        @foreach ($links as $url => $label)
                            <div class="py-4">
                                <x-ark-external-link :url="$url" :text="$label" />
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>

        @livewire('delete-token')
    </div>
@endcomponent
