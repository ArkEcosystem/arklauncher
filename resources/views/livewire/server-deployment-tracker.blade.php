@push('scripts')
    <script src="{{ asset('js/clipboard.js') }}"></script>
    <script src="{{ asset('js/file-download.js')}}"></script>
@endpush

<div>
    @if($hasFinalState || $showDeploymentFailedModal)
    <div class="items-start mb-6">
    @else
    <div class="items-start mb-6" wire:poll.15000ms>
    @endif
        <div class="px-6 mx-auto">
            <span class="flex items-center text-theme-secondary-500">
                <x-ark-icon name="server" /><span class="ml-2">{{ $server->name }}</span>
            </span>

            <h2 class="mt-6">@lang('pages.server.installation.initial_setup')</h2>

            <div class="flex justify-between mt-2 text-sm font-semibold text-theme-secondary-500">
                <span>@lang('pages.server.installation.events')</span>
                <span>@lang('pages.server.installation.finished')</span>
            </div>

            <div class="flex items-center mt-4">
                @if (! $currentStatus)
                    <x-ark-status-circle type="running" />
                    <div class="flex justify-between ml-3 w-full text-sm font-semibold tracking-wider">
                        <span>@lang('pages.server.installation.states.setting_up')</span>
                        <span>@lang('actions.running')...</span>
                    </div>
                @elseif($currentStatus && $this->hasEverHadStatus($this->getFirstState()))
                    <x-ark-status-circle type="success" />
                    <div class="flex justify-between ml-3 w-full text-sm font-semibold tracking-wider">
                        <span class="text-theme-success-600">@lang('pages.server.installation.states.setting_up')</span>
                        <span class="text-theme-secondary-600">{{ ($server?->created_at_local ?? now())->format(DateFormat::TIME_PARENTHESES) }}</span>
                    </div>
                @elseif($currentStatus && ! $this->hasEverHadStatus($this->getFirstState()) && $server->isFailed())
                    <x-ark-status-circle type="failed" />
                    <div class="flex justify-between ml-3 w-full text-sm font-semibold tracking-wider">
                        <span class="text-theme-danger-600">@lang('pages.server.installation.states.setting_up')</span>
                        <span class="text-theme-secondary-600">@lang('actions.failed')</span>
                    </div>
                @else
                    <x-ark-status-circle type="awaiting" />
                    <div class="flex justify-between ml-3 w-full text-sm font-semibold tracking-wider">
                        <span class="text-theme-secondary-600">@lang('pages.server.installation.states.setting_up')</span>
                        <span class="text-theme-secondary-600">@lang('actions.waiting')...</span>
                    </div>
                @endif
            </div>

            @foreach($this->groups as $group => $states)
                <h2 class="block mt-6 text-2xl">{{ Str::title($group) }}</h2>

                <div class="flex justify-between mt-2 text-sm font-semibold text-theme-secondary-500">
                    <span>@lang('pages.server.installation.events')</span>
                    <span>@lang('pages.server.installation.finished')</span>
                </div>
                @foreach($states as $state)
                    @if($currentStatus && $currentStatus->name === $state && ! $server->isProvisioned())
                        <div class="flex items-center mt-4">
                            <x-ark-status-circle type="running" />
                            <div class="flex justify-between ml-3 w-full text-sm font-semibold tracking-wider">
                                <span>@lang('pages.server.installation.states.'.$state)</span>
                                <span>@lang('actions.running')...</span>
                            </div>
                        </div>
                    @elseif($server->isFailed() && $this->hasEverHadStatus('failed_'.$state))
                        <div class="flex items-center mt-4">
                            <x-ark-status-circle type="failed" />
                            <div class="flex justify-between ml-3 w-full text-sm font-semibold tracking-wider">
                                <span>@lang('pages.server.installation.states.'.Str::replaceFirst('failed_', '', $state))</span>
                                <span>@lang('actions.failed')</span>
                            </div>
                        </div>
                    @elseif($this->hasEverHadStatus($state))
                        <div class="flex items-center mt-4">
                            <x-ark-status-circle type="success" />
                            <div class="flex justify-between ml-3 w-full text-sm font-semibold tracking-wider">
                                <span class="text-theme-success-600">@lang('pages.server.installation.states.'.$state)</span>
                                <span class="text-theme-secondary-600">{{ ($server->latestStatus($state)?->created_at_local ?? now())->format(DateFormat::TIME_PARENTHESES) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center mt-4">
                            <x-ark-status-circle type="awaiting" />
                            <div class="flex justify-between ml-3 w-full text-sm font-semibold tracking-wider">
                                <span class="text-theme-secondary-600">@lang('pages.server.installation.states.'.$state)</span>
                                <span class="text-theme-secondary-600">@lang('actions.waiting')...</span>
                            </div>
                        </div>
                    @endif
                @endforeach

                @if(!$loop->last)
                    <hr class="mt-8 border-theme-secondary-200" />
                @endif
            @endforeach
        </div>
    </div>

    @if ($showDeploymentFailedModal)
        <x-ark-modal
            width-class="max-w-xl"
            :title="trans('pages.server.installation.failed_modal.title')"
            :esc-to-close="false"
            :close-button-only="true"
        >
            <x-slot name="description">
                <div class="flex justify-center items-center">
                    <img src="/images/installation_failed.svg" class="mt-4" />
                </div>

                <div class="mt-4">
                    <x-ark-alert type="error" :title="trans('pages.server.installation.failed_modal.error')">
                        {{ trans('pages.server.installation.failed_modal.description', [
                            'preset' => ucfirst($this->preset),
                            'reason' => $this->failureReason,
                        ]) }}
                    </x-ark-alert>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="flex justify-end">
                    <a class="button-primary" href="{{ route('tokens.details', $token) }}">
                        {{ trans('actions.go_home') }}
                    </a>
                </div>
            </x-slot>
        </x-ark-modal>
    @else
        @if (! $server->getMetaAttribute(ServerAttributeEnum::DISCLAIMER_MODAL_SEEN) && $userCanManageServer)
            <x-ark-modal>
                @slot('title')
                    @lang('pages.server.installation.passwords_modal.title')
                @endslot

                @slot('description')
                    <div class="mt-4">
                        <x-ark-alert type="warning" :message="trans('pages.server.installation.passwords_modal.description')" />
                    </div>
                    <div class="mt-6 sm:col-span-2">
                        <dd class="mt-1 text-sm leading-5 text-theme-secondary-900">
                            <ul class="rounded-md">
                                <li class="flex justify-between items-center py-3 pr-4 pl-3 text-sm leading-5 border-b border-dashed border-theme-secondary-300">
                                    <div class="flex flex-col flex-1 w-0">
                                        <span class="text-theme-secondary-600">
                                            @lang('pages.server.installation.passwords_modal.username')
                                        </span>
                                        <span class="font-semibold truncate">{{ $token->normalized_token }}</span>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        <x-ark-clipboard :value="$token->normalized_token" />
                                    </div>
                                </li>
                                <li class="flex justify-between items-center py-3 pr-4 pl-3 text-sm leading-5 border-b border-dashed border-theme-secondary-300">
                                    <div class="flex flex-col flex-1 w-0">
                                        <span class="text-theme-secondary-600">
                                            @lang('pages.server.installation.passwords_modal.user_password')
                                        </span>
                                        <span class="font-semibold truncate">{{ $server->user_password }}</span>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        <x-ark-clipboard :value="$server->user_password" />
                                    </div>
                                </li>
                                <li class="flex justify-between items-center py-3 pr-4 pl-3 text-sm leading-5">
                                    <div class="flex flex-col flex-1 w-0">
                                        <span class="text-theme-secondary-600">
                                            @lang('pages.server.installation.passwords_modal.sudo_password')
                                        </span>
                                        <span class="font-semibold truncate">{{ $server->sudo_password }}</span>
                                    </div>
                                    <div class="flex-shrink-0 ml-4">
                                        <x-ark-clipboard :value="$server->sudo_password" />
                                    </div>
                                </li>
                            </ul>
                        </dd>
                    </div>
                @endslot

                @slot('buttons')
                    <div class="flex justify-end mt-5 space-x-3">
                        <x-ark-file-download
                            :filename="$token->slug . '_' . $server->name"
                            :content="$this->getCredentials()"
                        />

                        <button type="button" class="button-primary" wire:click="closeDisclaimerModal">@lang('actions.understand')</button>
                    </div>
                @endslot
            </x-ark-modal>
        @endif

        @if ($server->getMetaAttribute(ServerAttributeEnum::DISCLAIMER_MODAL_SEEN) && !$server->getMetaAttribute(ServerAttributeEnum::SERVER_CREATED_MODAL_SEEN) && $server->isProvisioned())
            <x-ark-modal width-class="max-w-lg">
                @slot('title')
                    @lang('pages.server.installation.deployed_modal.title')
                @endslot

                @slot('description')
                    <div class="flex justify-center items-center">
                        <img src="/images/installation_complete.svg" class="mt-4" />
                    </div>

                    <div class="mt-4">
                        <x-ark-alert type="info" title="Congratulations">
                            @if($server->isGenesis())
                                @lang('pages.server.installation.deployed_modal.blockchain_description')
                            @else
                                @lang('pages.server.installation.deployed_modal.description', ['preset' => $server->preset])
                            @endif
                        </x-ark-alert>
                    </div>
                @endslot

                @slot('buttons')
                    <div class="flex justify-end space-x-3">
                        @if ($server->isGenesis())
                            <a
                                class="button-primary"
                                href="{{ route('tokens.details', ['token' => $server->token]) }}"
                                wire:click="closeServerCreatedModal"
                            >
                                @lang('actions.view_details')
                            </a>
                        @else
                            <button class="button-primary" wire:click="closeServerCreatedModal">@lang('actions.close')</button>
                        @endif
                    </div>
                @endslot
            </x-ark-modal>
        @endif
    @endif
</div>
