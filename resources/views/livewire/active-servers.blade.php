@php($hasSecureShellKeys = $selectedToken->hasSecureShellKeys())
@php($hasServerProviders = $selectedToken->hasServerProviders())
@php($needsServerConfiguration = $selectedToken->needsServerConfiguration())
@php($hasGenesis = $selectedNetwork->hasGenesis())

@if(!$hasSecureShellKeys || !$hasServerProviders || $needsServerConfiguration)
    <x-ark-alert type="info">
        {!! trans('tokens.networks.onboarding_incomplete', ['route' => route('tokens.welcome', $selectedToken)]) !!}
    </x-ark-alert>
@endif

@if($hasSecureShellKeys && $hasServerProviders && $selectedToken->hasAuthorizedKeys() && ! $needsServerConfiguration)
    <div class="mt-5">
        @if($title)
            <h2>{{ $title }}</h2>
        @endif

        <x-home.active-servers.desktop-tabs
            :default="$selectedNetwork->id"
            :networks="$selectedToken->networks"
        />

        <x-home.active-servers.mobile-dropdown
            :selected="$selectedNetwork"
            :networks="$selectedToken->networks"
        />

        <div class="md:hidden">
            <x-home.active-servers.type-filter mobile />
        </div>

        @if($selectedNetworkServers->count() > 0)
            @if(! $hasGenesis)
                <x-ark-alert type="info" :message="trans('tokens.networks.no_genesis')" />
            @endif

            <div>
                <x-ark-flash />
            </div>

            <div>
                <x-home.active-servers.desktop-table
                    :sort-by="$sortBy"
                    :sort-direction="$sortDirection"
                    :servers="$filteredNetworkServers ?? $selectedNetworkServers"
                />
            </div>

            <x-home.active-servers.mobile-accordion
                :servers="$filteredNetworkServers ?? $selectedNetworkServers"
            />
        @else
            <div class="flex mt-5 text-center">
                <span>@lang('tokens.networks.no_servers_created')</span>
            </div>
        @endif

        @if($selectedToken->allows($this->user, 'server:create') && $selectedToken->hasAnyIndexedServerProvider())
            @if($hasGenesis && !$selectedNetwork->hasProvisionedGenesis())
                <div class="mt-5 text-sm">
                    <span>@lang('pages.manage-tokens.no_provisioned_genesis')</span>
                </div>
            @else
                <div class="flex flex-col mt-5 md:flex-row md:items-center md:space-x-4">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('tokens.servers.create', [$selectedToken, $selectedNetwork]) }}" class="flex justify-center items-center w-12 h-12 rounded hover:text-white transition-default bg-theme-primary-100 text-theme-primary-500 hover:bg-theme-primary-700">
                            <x-ark-icon name="plus" size="sm" />
                        </a>
                        <a href="{{ route('tokens.servers.create', [$selectedToken, $selectedNetwork]) }}" class="font-semibold link">@lang('pages.manage-tokens.add_server_title')</a>
                    </div>
                    <span class="mt-5 text-sm md:mt-0 md:ml-4 text-theme-secondary-500">@lang('pages.manage-tokens.add_server_description')</span>
                </div>
            @endif
        @endif
    </div>
@endif

@livewire('delete-server', ['token' => $selectedToken, 'network' => $selectedNetwork], key('delete-server-' . $selectedToken->id . '-' . $selectedNetwork->id))

@livewire('rename-server', ['token' => $selectedToken, 'network' => $selectedNetwork], key('rename-server-' . $selectedToken->id . '-' . $selectedNetwork->id))
