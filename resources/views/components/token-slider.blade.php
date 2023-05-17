<div wire:ignore x-data="{ tokenId: {{ $selectedToken?->id ?? 'null' }} }">
    <x-ark-slider id="tokens" pagination-class="flex justify-end" space-between="20" :columns="4" top-pagination hide-navigation>
        @foreach ($tokens as $token)
            <x-ark-slider-slide>
                <div class="block relative rounded-lg border-2 cursor-pointer hover:shadow-xl border-theme-primary-100 transition-default" :class="{ 'border-theme-primary-500 bg-theme-primary-50': tokenId === {{ $token->id }} }">
                    <div @click="livewire.emit('setToken', {{ $token->id }}); tokenId = {{ $token->id }}" class="flex flex-col flex-shrink-0 justify-center items-center py-6 px-10">
                        <x-blockchain-logo :token="$token" />
                        <div class="mt-4 max-w-full text-lg font-bold text-theme-secondary-900 truncate">{{ $token->name }}</div>
                        <div class="flex mt-3 space-x-8 text-sm font-semibold select-none text-theme-secondary-700">
                            <span class="flex items-center">
                                <x-ark-icon name="server" class="mr-2 text-theme-secondary-900" />
                                {{ $token->servers_count }}
                                <span class="hidden ml-1 lg:block">
                                    {{ trans('pages.manage-tokens.table_servers') }}
                                </span>
                            </span>
                            <span class="flex items-center">
                                @if ($token->hasProvisionedGenesisServer())
                                    <x-ark-icon name="circle.check-mark" class="mr-2 text-theme-success-600" />
                                @else
                                    <x-ark-icon name="circle.pause" class="mr-2 text-theme-warning-600" />
                                @endif
                                {{ $token->hasProvisionedGenesisServer() ? trans('pages.manage-tokens.status_deployed_short') : trans('pages.manage-tokens.status_pending_short') }}
                            </span>
                        </div>
                    </div>

                    <button
                        wire:click="editToken({{ $token->id }})"
                        class="absolute top-0 right-0 p-4 text-theme-secondary-500 transition-default hover:text-theme-primary-500"
                    >
                        <x-ark-icon name="pencil" size="sm" />
                    </button>
                </div>
            </x-ark-slider-slide>
        @endforeach
        @for ($i = $tokens->count(); $i < 4; $i++)
            <x-token-slider-placeholder />
        @endfor
    </x-ark-slider>
</div>
