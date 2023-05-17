@props([
    'servers',
])

<div class="block px-8 mt-5 space-y-4 md:hidden">
    @foreach ($servers as $server)
        @php($isProvisioned = $server->isProvisioned())
        @php($isFailed = $server->isFailed())
        @php($pathShow = $server->pathShow())

        <x-ark-accordion :border="false" container-class="rounded-xl border border-theme-secondary-200">
            <x-slot name="title">
                <a href="{{ $server->pathShow() }}" class="link">
                    {{ $server->name }}
                </a>
            </x-slot>

            <div class="px-4 pb-6">
                <div class="space-y-3">
                    <div class="flex justify-between pb-3 border-b border-theme-secondary-200">
                        <span class="text-theme-secondary-500">@lang('tokens.networks.table_status')</span>
                        <div
                            class="flex items-center space-x-2"
                            @if(!$isProvisioned && !$isFailed)
                                wire:poll.60000ms
                            @endif
                        >
                            <x-servers.status :server="$server" />
                        </div>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-theme-secondary-200">
                        <span class="text-theme-secondary-500">@lang('tokens.networks.table_ip_address')</span>
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
                    <div class="flex justify-between pb-2 border-b border-theme-secondary-200">
                        <span class="text-theme-secondary-500">@lang('tokens.networks.table_size')</span>
                        <span>{{ $server->plan->disk }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-theme-secondary-200">
                        <span class="text-theme-secondary-500">@lang('tokens.networks.table_region')</span>
                        <span>{{ $server->region->name }}</span>
                    </div>
                    <div class="flex justify-between pb-2 border-b border-theme-secondary-200">
                        <span class="text-theme-secondary-500">@lang('tokens.networks.table_preset')</span>
                        @if($isProvisioned && PresetTypeEnum::isExplorer($server->preset))
                            <x-ark-external-link
                                url="http://{{ $server->ip_address }}:{{ $server->ip_address }}"
                                :text="ucfirst($server->preset)"
                            />
                        @else
                            <span>{{ ucfirst($server->preset) }}</span>
                        @endif
                    </div>
                </div>
                <div class="mt-5">
                    <div class="flex justify-between">
                        @if ($isProvisioned)
                            <div class="flex space-x-3">
                                @if ($server->hasEverHadStatus('provisioning') || $isFailed)
                                    @can('delete', [Domain\Server\Models\Server::class, $server])
                                        <button
                                            type="button"
                                            onclick="window.livewire.emit('deleteServer', '{{ $server->id }}')"
                                            class="w-14 h-12 button-cancel"
                                        >
                                            <x-ark-icon name="trash" size="sm" />
                                            <span class="sr-only">@lang('actions.delete')</span>
                                        </button>
                                    @endcan
                                @endif

                                @can('rename', [Domain\Server\Models\Server::class, $server])
                                <button
                                    type="button"
                                    onclick="window.livewire.emit('renameServer', '{{ $server->id }}')"
                                    class="w-14 h-12 button-icon"
                                >
                                    <x-ark-icon name="pencil" size="sm" />
                                    <span class="sr-only">@lang('actions.rename')</span>
                                </button>
                                @endcan
                            </div>

                            @canany(['start', 'stop', 'restart'], [Domain\Server\Models\Server::class, $server])
                                <div class="relative">
                                    <x-ark-dropdown
                                        button-class="w-12 h-12 button-icon"
                                        wrapper-class="inline-block top-0 right-0 text-left md:absolute"
                                        dropdown-classes="w-screen bottom-0 px-4 mb-16"
                                        dropdown-content-classes="bg-white rounded-xl shadow-lg py-3"
                                        with-placement="bottom"
                                    >
                                        @can('start', [Domain\Server\Models\Server::class, $server])
                                            <button
                                                type="button"
                                                wire:click="startServer({{ $server->id }})"
                                                class="dropdown-entry"
                                            >
                                                <x-ark-icon name="play" size="sm" class="inline-flex mr-2" />
                                                @lang('actions.start')
                                            </button>
                                        @endcan
                                        @can('stop', [Domain\Server\Models\Server::class, $server])
                                            <button
                                                type="button"
                                                wire:click="stopServer({{ $server->id }})"
                                                class="dropdown-entry"
                                            >
                                                <x-ark-icon name="stop" size="sm" class="inline-flex mr-2"/>
                                                @lang('actions.stop')
                                            </button>
                                        @endcan
                                        @can('restart', [Domain\Server\Models\Server::class, $server])
                                            <button
                                                type="button"
                                                wire:click="rebootServer({{ $server->id }})"
                                                class="dropdown-entry"
                                            >
                                                <x-ark-icon name="arrows.arrow-rotate-left" size="sm" class="inline-flex mr-2" />
                                                @lang('actions.reboot')
                                            </button>
                                        @endcan
                                    </x-ark-dropdown>
                                </div>
                            @endcanany
                        @endif
                    </div>
                </div>
            </div>
        </x-ark-accordion>
    @endforeach
</div>
