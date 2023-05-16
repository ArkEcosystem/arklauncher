@if($invitationsCount)
    <div class="flex flex-col mb-8 space-y-8 md:pt-3 md:mb-0 lg:pt-0">
        <div>
            <h1 class="mb-4 text-lg md:text-3xl">@lang('pages.user-settings.teams.pending_title')</h1>
            <span>@lang('pages.user-settings.teams.pending_description')</span>
        </div>

        @if (session('status') === TokenInvitationController::ALERT_STATUS)
            <x-ark-flash />
        @endif

        <div class="flex flex-col mx-auto w-full">
            <ul class="md:hidden" role="list">
                @foreach($currentUser->invitations as $invitation)
                    <li class="flex flex-col space-y-4">
                        <dl class="flex flex-col space-y-4 text-theme-secondary-700">
                            <div class="flex justify-between space-x-3 min-w-0">
                                <dt class="flex-shrink-0 font-semibold text-theme-secondary-500">
                                    @lang('tables.blockchain_name')
                                </dt>
                                <dd class="font-semibold text-theme-secondary-900 truncate">
                                    {{ $invitation->token->name }}
                                </dd>
                            </div>
                            <div class="flex justify-between space-x-3 min-w-0">
                                <dt class="flex-shrink-0 font-semibold text-theme-secondary-500">
                                    @lang('tables.invited_by')
                                </dt>
                                <dd class="truncate">
                                    {{ $invitation->token->user->name }}
                                </dd>
                            </div>
                            <div class="flex justify-between space-x-3">
                                <dt class="font-semibold text-theme-secondary-500">
                                    @lang('tables.role')
                                </dt>
                                <dd>
                                    <button
                                        type="button"
                                        class="font-semibold text-theme-secondary-600 link"
                                        role="button"
                                        onclick="livewire.emit('showCollaboratorPermissions', {{ json_encode($invitation->permissions) }})"
                                    >
                                        {{ ucfirst($invitation->role) }}
                                    </button>
                                </dd>
                            </div>
                            <div class="flex justify-between space-x-3">
                                <dt class="font-semibold text-theme-secondary-500">
                                    @lang('tables.date_invited')
                                </dt>
                                <dd>
                                    {{ $invitation->token->created_at_local->format(DateFormat::DATE) }}
                                </dd>
                            </div>
                        </dl>

                        <div class="flex justify-between space-x-3 sm:justify-end">
                            <a
                                class="w-1/2 sm:w-auto button-primary"
                                href="{{ route('invitations.accept', $invitation) }}"
                            >
                                @lang('actions.accept')
                            </a>

                            <button
                                type="button"
                                class="w-1/2 sm:w-auto button-icon-cancel"
                                onclick="livewire.emit('showDeclineInvitationModal', {{ $invitation->id }})"
                            >
                                <x-ark-icon name="trash" size="sm" class="my-auto mx-5" />
                                <span class="sr-only">
                                    @lang('actions.decline')
                                </span>
                            </button>
                        </div>
                        @unless ($loop->last)
                            <x-divider spacing="4" />
                        @endunless
                    </li>
                @endforeach
            </ul>

            <div class="hidden pb-8 mb-8 border-b md:block table-container border-theme-secondary-300">
                <table>
                    <thead class="whitespace-nowrap">
                        <tr>
                            <th>@lang('tables.blockchain_name')</th>
                            <th>@lang('tables.invited_by')</th>
                            <th class="hidden w-0 xl:table-cell">@lang('tables.role')</th>
                            <th class="w-0 text-right">@lang('tables.date_invited')</th>
                            <th class="w-0"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currentUser->invitations as $invitation)
                            <tr>
                                <td>
                                    <div class="flex items-center space-x-4">
                                        <x-blockchain-logo :token="$invitation->token" size="small" rounded="base" />

                                        <div class="relative flex-grow">
                                            <span class="absolute max-w-full font-semibold truncate">
                                                {{ $invitation->token->name }}
                                            </span>
                                            <span class="overflow-hidden h-0">&nbsp;</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <x-truncated-cell class="font-semibold text-theme-secondary-600">
                                        {{ $invitation->token->user->name }}
                                    </x-truncated-cell>
                                </td>
                                <td class="hidden xl:table-cell">
                                    <button
                                        type="button"
                                        class="font-semibold text-theme-secondary-600 link"
                                        role="button"
                                        onclick="livewire.emit('showCollaboratorPermissions', {{ json_encode($invitation->permissions) }})"
                                    >
                                        {{ ucfirst($invitation->role) }}
                                    </button>
                                </td>
                                <td class="font-semibold text-right text-theme-secondary-600">
                                    <span class="whitespace-nowrap">{{ $invitation->token->created_at_local->format(DateFormat::DATE) }}</span>
                                </td>
                                <td>
                                    <div class="flex flex-col justify-end items-center ml-4 md:flex-row md:space-x-3">
                                        <a
                                            class="button-primary"
                                            href="{{ route('invitations.accept', $invitation) }}"
                                        >
                                            @lang('actions.accept')
                                        </a>

                                        <button
                                            class="button-icon-cancel"
                                            role="button"
                                            onclick="livewire.emit('showDeclineInvitationModal', {{ $invitation->id }})"
                                        >
                                            <div class="flex items-center h-11">
                                                <x-ark-icon
                                                    name="trash"
                                                    size="sm"
                                                    class="my-auto mx-5"
                                                />
                                            </div>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <hr class="-mx-8 sm:mx-0 md:hidden border-theme-secondary-300" />
    </div>
@endif
