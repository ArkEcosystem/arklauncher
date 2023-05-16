@props([
    'sortBy',
    'sortDirection',
    'servers',
])

<div class="hidden relative md:block">
    <div class="table-container">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="xl:w-72">
                        <x-sort-by-button name="name" :sortBy="$sortBy" :sort-direction="$sortDirection">
                            @lang('tokens.networks.table_name')
                        </x-sort-by-button>
                    </th>
                    <th class="xl:w-36">
                        <x-sort-by-button name="status" :sortBy="$sortBy" :sort-direction="$sortDirection">
                            @lang('tokens.networks.table_status')
                        </x-sort-by-button>
                    </th>
                    <th class="xl:w-40">@lang('tokens.networks.table_ip_address')</th>
                    <th class="hidden lg:table-cell xl:w-36">
                        <x-sort-by-button name="plan.disk" :sortBy="$sortBy" :sort-direction="$sortDirection">
                            @lang('tokens.networks.table_size')
                        </x-sort-by-button>
                    </th>
                    <th class="hidden xl:table-cell xl:w-36">
                        <x-sort-by-button name="region.name" :sortBy="$sortBy" :sort-direction="$sortDirection">
                            @lang('tokens.networks.table_region')
                        </x-sort-by-button>
                    </th>
                    <th class="xl:w-36">@lang('tokens.networks.table_preset')</th>
                    <th class="xl:w-40"><div class="text-right">@lang('tables.actions')</div></th>
                </tr>
            </thead>
            <tbody>
                @foreach($servers as $server)
                    @php($isProvisioned = $server->isProvisioned())
                    @php($isFailed = $server->isFailed())
                    @php($pathShow = $server->pathShow())

                    <tr id="{{ 'server-' . $server->id }}">
                        <td class="font-semibold xl:w-48">
                            <div wire:loading.class="w-full h-6 bg-gray-200 rounded-md animate-pulse"></div>
                            <span wire:loading.class="hidden">
                                <a href="{{ $pathShow }}" class="font-semibold link">{{ $server->name }}</a>
                            </span>
                        </td>

                        <td class="xl:w-36">
                            <div wire:loading.class="w-full h-6 bg-gray-200 rounded-md animate-pulse"></div>
                            <div
                                wire:loading.class="hidden"
                                @if(!$isProvisioned && !$isFailed)
                                    wire:poll.60000ms
                                @endif
                            >
                                <x-servers.status :server="$server" />
                            </div>
                        </td>

                        <td class="justify-center xl:w-40">
                            <div wire:loading.class="w-full h-6 bg-gray-200 rounded-md animate-pulse"></div>
                            <div wire:loading.class="hidden">
                                @if($server->ip_address)
                                    <div class="flex items-center space-x-2">
                                        <span>{{ $server->ip_address }}</span>
                                        <x-ark-clipboard
                                            class="flex justify-center items-center text-theme-primary-300 hover:text-theme-primary-400"
                                            :value="$server->ip_address"
                                            :no-styling="true"
                                            icon-only
                                        />
                                    </div>
                                @else
                                    @lang('tokens.networks.server_unavailable')
                                @endif
                            </div>
                        </td>

                        <td class="hidden lg:table-cell xl:w-36">
                            <div wire:loading.class="w-full h-6 bg-gray-200 rounded-md animate-pulse"></div>
                            <span wire:loading.class="hidden">
                                {{ $server->plan->disk }}
                            </span>
                        </td>

                        <td class="hidden xl:table-cell xl:w-36">
                            <div wire:loading.class="w-full h-6 bg-gray-200 rounded-md animate-pulse"></div>
                            <span wire:loading.class="hidden">
                                {{ $server->region->name }}
                            </span>
                        </td>

                        <td class="xl:w-36">
                            <div wire:loading.class="w-full h-6 bg-gray-200 rounded-md animate-pulse"></div>
                            <div wire:loading.class="hidden">
                                @if($isProvisioned && $server->isExplorer())
                                    <x-ark-external-link
                                        url="http://{{ $server->ip_address }}:{{ $server->token->config['explorerPort'] }}"
                                        :text="ucfirst($server->preset)"
                                    />
                                @else
                                    <span>{{ ucfirst($server->preset) }}</span>
                                @endif
                            </div>
                        </td>

                        <td class="whitespace-nowrap xl:w-12">
                            <div wire:loading.class="w-full h-6 bg-gray-200 rounded-md animate-pulse"></div>
                            <div wire:loading.class="hidden">
                                <x-home.active-servers.action-dropdown
                                    :server="$server"
                                />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
