@extends('layouts.app')

@section('content')
    <x-ark-container class="mx-auto max-w-4xl">
        <div class="items-start">
            <div class="items-center">
                <h1 class="mb-4">@lang('tokens.onboarding.page_header')</h1>
                <p class="leading-relaxed text-theme-secondary-700">@lang('tokens.onboarding.page_subheader')</p>
            </div>

            <div class="flex flex-row flex-wrap">
                <div class="w-full">
                    <div class="items-start mt-2">
                        <div class="mx-auto space-y-6 divide-y divide-dashed divide-theme-secondary-300">
                            {{-- Customize Your Blockchain --}}
                            <x-onboard-step
                                name="configuration"
                                :title="trans('tokens.onboarding.customize_your_blockchain_title')"
                                :description="trans('tokens.onboarding.customize_your_blockchain_description')"
                                route="tokens.edit"
                                :token="$token"/>

                            {{-- Connect Server Provider --}}
                            <x-onboard-step
                                name="server_providers"
                                :title="trans('tokens.onboarding.connect_server_provider_title')"
                                :description="trans('tokens.onboarding.connect_server_provider_description')"
                                route="tokens.server-providers"
                                :token="$token"/>

                            {{-- Server Configuration --}}
                            <x-onboard-step
                                name="server_config"
                                :title="trans('tokens.onboarding.server_configuration_title')"
                                :description="trans('tokens.onboarding.server_configuration_description')"
                                route="tokens.server-configuration"
                                :token="$token"/>

                            {{-- SSH Keys --}}
                            <x-onboard-step
                                name="secure_shell_keys"
                                :title="trans('tokens.onboarding.manage_secure_shell_keys_title')"
                                :description="trans('tokens.onboarding.manage_secure_shell_keys_description')"
                                route="tokens.ssh-keys"
                                :token="$token"/>

                            {{-- Invite Your Team --}}
                            <x-onboard-step
                                optional
                                name="collaborators"
                                :title="trans('tokens.onboarding.invite_your_team_title')"
                                :description="trans('tokens.onboarding.invite_your_team_description')"
                                route="tokens.collaborators"
                                :token="$token"/>

                            {{-- Time to Deploy! --}}
                            <div class="flex pt-6">
                                @if($token->onboarding()->fulfilled())
                                    <div class="mt-1" data-tippy-content="{{ trans('tooltips.steps.active') }}">
                                        <x-ark-status-circle type="active" />
                                    </div>
                                @else
                                    <div class="mt-1 opacity-40" data-tippy-content="{{ trans('tooltips.steps.locked') }}">
                                        <x-ark-status-circle type="locked" />
                                    </div>
                                @endif
                                <div @class(['ml-3', 'opacity-40' => ! $token->onboarding()->fulfilled()])>
                                    <button
                                        type="button"
                                        @if($token->onboarding()->fulfilled())
                                        onclick="window.livewire.emit('deployBlockchain', {{ $token->id }})"
                                        @else
                                        disabled
                                        @endif
                                        @class([
                                            'text-lg font-semibold text-theme-primary-500 select-none transition-default',
                                            'text-theme-secondary-900 cursor-not-allowed' => ! $token->onboarding()->fulfilled(),
                                            'text-theme-primary-500' => $token->onboarding()->fulfilled(),
                                        ])
                                    >
                                        @lang('tokens.onboarding.deploy_your_blockchain_title')
                                    </button>
                                    <p class="mt-2">
                                        @lang('tokens.onboarding.deploy_your_blockchain_description')
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end mt-8 space-x-3">
                            @can('delete', [Domain\Token\Models\Token::class, $token])
                                <button
                                    type="button"
                                    onclick="window.livewire.emit('deleteToken', {{ $token->id }})"
                                    class="inline-flex items-center button-cancel"
                                >
                                    <x-ark-icon name="trash" size="sm" class="mr-2" />@lang('actions.delete')
                                </button>
                            @endcan

                            <button
                                type="button"
                                onclick="window.livewire.emit('deployBlockchain', {{ $token->id }})"
                                class="button-primary {{ $token->onboarding()->fulfilled() === false ? 'disabled' : ''}}"
                                {{ $token->onboarding()->fulfilled() === false ? 'disabled' : ''}}
                            >
                                {{ trans('actions.deploy') }}
                            </button>
                        </div>

                        <livewire:delete-token />
                        <livewire:deploy-blockchain />
                    </div>
                </div>
            </div>
        </div>
    </x-ark-container>
@endsection
