<div class="flex flex-col space-y-8">
    <div>
        <h1 class="mb-4 text-lg md:text-3xl">@lang('pages.user-settings.teams.my_teams_title')</h1>
        <span>@lang('pages.user-settings.teams.my_teams_description')</span>
    </div>

    @if (session('status') === LeaveTeamModal::ALERT_STATUS)
        <x-ark-flash />
    @endif

    <div class="flex flex-col mx-auto w-full">
        @if($currentUser->tokens->isNotEmpty())
            <div class="flex-1">
                <ul class="md:hidden" role="list">
                    @foreach($currentUser->tokens as $token)
                        <li class="flex flex-col space-y-4">
                            <dl class="flex flex-col space-y-4 text-theme-secondary-700">
                                <div class="flex items-center space-x-4 min-w-0">
                                    <dt class="flex-shrink-0">
                                        <x-blockchain-logo :token="$token" size="small" rounded="base" />
                                    </dt>
                                    <dd class="flex-grow text-right truncate">
                                        <a href="{{ route('tokens.show', $token) }}" class="font-semibold text-theme-primary-600 link">{{ $token->name }}</a>
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-theme-secondary-500">
                                        @lang('tables.role')
                                    </dt>
                                    <dd>
                                        <button
                                            type="button"
                                            class="font-semibold text-theme-secondary-600 link"
                                            onclick="livewire.emit('showCollaboratorPermissions', {{ json_encode($currentUser->permissionsOn($token)) }})"
                                        >
                                            {{ ucfirst($currentUser->roleOn($token)) }}
                                        </button>
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="font-semibold text-theme-secondary-500">
                                        @lang('tables.date_invited')
                                    </dt>
                                    <dd>
                                        {{ $token->created_at_local->format(DateFormat::DATE) }}
                                    </dd>
                                </div>
                            </dl>

                            <div class="flex justify-end">
                                <span
                                    class="flex justify-end w-full sm:w-auto"
                                    @if ($currentUser->ownsToken($token))
                                    data-tippy-content="{{ trans('pages.user-settings.teams.leave_owner') }}"
                                    data-tippy-trigger="mouseenter"
                                    @endif
                                >
                                    <button
                                        type="button"
                                        class="w-full sm:w-auto button-secondary"
                                        @if ($currentUser->ownsToken($token))
                                        disabled
                                        @else
                                        onclick="livewire.emit('showLeaveTeamModal', {{ $token->id }})"
                                        @endif
                                    >
                                        @lang('actions.leave')
                                    </button>
                                </span>
                            </div>
                            @unless ($loop->last)
                                <x-divider spacing="4" />
                            @endunless
                        </li>
                    @endforeach
                </ul>

                <div class="hidden md:block table-container">
                    <table x-data="{}" class="w-full">
                        <thead>
                            <tr>
                                <th>@lang('tables.blockchain_name')</th>
                                <th>@lang('tables.role')</th>
                                <th class="text-right">@lang('tables.date_invited')</th>
                                @if(! ($hideButtons ?? false)) <th></th> @endif
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($currentUser->tokens as $token)
                            <tr class="border-t border-theme-secondary-200">
                                <td>
                                    <div class="flex items-center space-x-4">
                                        <x-blockchain-logo :token="$token" size="small" rounded="base" />
                                        <a href="{{ route('tokens.show', $token) }}" class="font-semibold text-theme-primary-600 link">
                                            {{ $token->name }}
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="font-semibold text-theme-secondary-600 link"
                                        role="button"
                                        onclick="livewire.emit('showCollaboratorPermissions', {{ json_encode($currentUser->permissionsOn($token)) }})"
                                    >
                                        {{ ucfirst($currentUser->roleOn($token)) }}
                                    </span>
                                </td>
                                <td class="font-semibold text-right text-theme-secondary-600">
                                    <span class="">{{ $token->created_at_local->format(DateFormat::DATE) }}</span>
                                </td>
                                @if(! ($hideButtons ?? false))
                                    <td>
                                        <div class="flex justify-end">
                                            <span
                                                @if ($currentUser->ownsToken($token))
                                                data-tippy-content="{{ trans('pages.user-settings.teams.leave_owner') }}"
                                                data-tippy-trigger="mouseenter"
                                                @endif
                                            >
                                                <button
                                                    class="button-secondary"
                                                    @if ($currentUser->ownsToken($token))
                                                    disabled
                                                    @else
                                                    @click="livewire.emit('showLeaveTeamModal', {{ $token->id }})"
                                                    @endif
                                                >
                                                    @lang('actions.leave')
                                                </button>
                                            </span>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="space-y-5">
                <div class="p-4 text-center rounded border-2 border-theme-secondary-200">
                    @lang('pages.user-settings.no_current_teams')
                </div>

                <div>
                    <img class="p-2" src="{{ asset('images/defaults/my-team.svg') }}" alt="" />
                    <img class="p-2" src="{{ asset('images/defaults/my-team.svg') }}" alt="" />
                    <img class="p-2" src="{{ asset('images/defaults/my-team.svg') }}" alt="" />
                </div>
            </div>
        @endif
    </div>
</div>
