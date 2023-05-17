@props([
    'server',
])

<div class="flex justify-end h-10">
    @if ($server->isProvisioned() || $server->isFailed())
        @canany(['start', 'stop', 'restart', 'rename', 'delete'], $server)
            <div class="relative">
                <x-ark-dropdown
                    button-class="w-10 h-10 button-icon"
                    with-placement="left-start"
                    dropdown-content-classes="bg-white rounded-xl shadow-lg py-3"
                >
                    @if ($server->isProvisioned())
                        @can ('rename', $server)
                            <button
                                onclick="window.livewire.emit('renameServer', '{{ $server->id }}')"
                                class="dropdown-entry"
                            >
                                <span>
                                    <x-ark-icon
                                        name="pencil"
                                        size="sm"
                                    />

                                    <span>@lang('actions.rename')</span>
                                </span>
                            </button>
                        @endcan

                        @can ('start', $server)
                            <button
                                wire:click="startServer({{ $server->id }})"
                                class="dropdown-entry"
                            >
                                <span>
                                    <x-ark-icon
                                        name="play"
                                        size="sm"
                                    />

                                    <span>@lang('actions.start')</span>
                                </span>
                            </button>
                        @endcan

                        @can ('stop', $server)
                            <button
                                wire:click="stopServer({{ $server->id }})"
                                class="dropdown-entry"
                            >
                                <span>
                                    <x-ark-icon
                                        name="stop"
                                        size="sm"
                                    />

                                    <span>@lang('actions.stop')</span>
                                </span>
                            </button>
                        @endcan

                        @can ('restart', $server)
                            <button
                                wire:click="rebootServer({{ $server->id }})"
                                class="dropdown-entry"
                            >
                                <span>
                                    <x-ark-icon
                                        name="arrows.arrow-rotate-left"
                                        size="sm"
                                    />

                                    <span>@lang('actions.reboot')</span>
                                </span>
                            </button>
                        @endcan

                        <x-divider class="mx-8" spacing="4" />
                    @endif

                    @can ('delete', $server)
                        <button
                            onclick="window.livewire.emit('deleteServer', '{{ $server->id }}')"
                            class="dropdown-entry delete-server"
                        >
                            <span>
                                <x-ark-icon
                                    name="trash"
                                    size="sm"
                                />

                                <span>@lang('actions.delete')</span>
                            </span>
                        </button>
                    @endcan
                </x-ark-dropdown>
            </div>
        @endcanany
    @endif
</div>
