<div>
    <h2 class="header-4">@lang('tokens.token_collaborator')</h2>
    <p>@lang('tokens.token_collaborator_description')</p>

    <div class="hidden mt-5 md:block table-container">
        <x-ark-tables.table sticky class="min-w-full">
            <thead>
                <tr>
                    <x-ark-tables.header>{{ trans('tables.name') }}</x-ark-tables.header>
                    <x-ark-tables.header>{{ trans('tables.role') }}</x-ark-tables.header>
                    <x-ark-tables.header class="w-0 text-right last-cell">{{ trans('tables.date_added') }}</x-ark-tables.header>
                    <x-ark-tables.header class="w-16" />
                </tr>
            </thead>

            <tbody>
                <x-ark-tables.row>
                    <x-ark-tables.cell class="font-semibold text-theme-secondary-700">
                        <div class="flex items-center">
                            <x-ark-avatar
                                :identifier="$this->user->name"
                                class="w-8 h-8 rounded-lg"
                                show-identifier-letters
                            />

                            <span class="ml-3">You</span>
                        </div>
                    </x-ark-tables.cell>

                    <x-ark-tables.cell>{{ ucfirst($this->user->pivot->role) }}</x-ark-tables.cell>

                    <x-ark-tables.cell class="text-right">
                        <span class="whitespace-nowrap">{{ $this->user->pivot->created_at_local->format(DateFormat::DATE) }}</span>
                    </x-ark-tables.cell>

                    <x-ark-tables.cell>
                        @unless ($token->user->id === $this->user->id)
                            <div class="flex justify-end w-full">
                                <button
                                    class="w-10 h-10 button-icon"
                                    wire:click="$emit('updateCollaborator', '{{ $this->user->id }}')"
                                    @cannot ('createCollaborator', $token) disabled @endcannot
                                >
                                    <x-ark-icon name="pencil" size="sm" />
                                </button>
                            </div>
                        @endunless
                    </x-ark-tables.cell>
                </x-ark-tables.row>

                @foreach ($this->collaborators as $collaborator)
                    <x-ark-tables.row>
                        <x-ark-tables.cell class="font-semibold text-theme-secondary-700">
                            <div class="flex items-center space-x-4 w-full">
                                <x-ark-avatar
                                    :identifier="$collaborator->name"
                                    class="w-8 h-8 rounded-lg"
                                    show-identifier-letters
                                />

                                <div class="relative flex-grow">
                                    <span class="absolute max-w-full font-semibold truncate">
                                        {{ $collaborator->name }}
                                    </span>
                                    <span class="overflow-hidden h-0">&nbsp;</span>
                                </div>
                            </div>
                        </x-ark-tables.cell>

                        <x-ark-tables.cell>{{ ucfirst($collaborator->pivot->role) }}</x-ark-tables.cell>

                        <x-ark-tables.cell class="text-right">
                            <span class="whitespace-nowrap">{{ $collaborator->pivot->created_at_local->format(DateFormat::DATE) }}</span>
                        </x-ark-tables.cell>

                        <x-ark-tables.cell>
                            @unless ($token->user->id === $collaborator->id)
                                <div class="flex justify-end space-x-3 w-full">
                                    <button
                                        class="w-10 h-10 button-icon"
                                        wire:click="$emit('updateCollaborator', '{{ $collaborator->id }}')"
                                        @cannot ('createCollaborator', $token) disabled @endcannot
                                    >
                                        <x-ark-icon name="pencil" size="sm" />
                                    </button>

                                    <button
                                        class="w-10 h-10 button-icon"
                                        wire:click="$emit('deleteCollaborator', '{{ $collaborator->id }}')"
                                        @cannot ('deleteCollaborator', $token) disabled @endcannot
                                    >
                                        <x-ark-icon name="trash" size="sm" />
                                    </button>
                                </div>
                            @endunless
                        </x-ark-tables.cell>
                    </x-ark-tables.row>
                @endforeach
            </tbody>
        </x-ark-tables.table>
    </div>

    <ul class="mt-5 md:hidden" role="list">
        <li class="flex flex-col space-y-4">
            <dl class="flex flex-col space-y-4 text-theme-secondary-700">
                <div class="flex justify-between space-x-3 w-full min-w-0">
                    <dt class="flex-grow">
                        <div class="flex items-center space-x-4 w-full">
                            <x-ark-avatar
                                :identifier="$this->user->name"
                                class="w-8 h-8 rounded-lg"
                                show-identifier-letters
                            />

                            <div class="relative flex-grow">
                                <span class="font-semibold">
                                    You
                                </span>
                            </div>
                        </div>
                    </dt>

                </div>
                <div class="flex justify-between space-x-3">
                    <dt class="font-semibold text-theme-secondary-500">
                        @lang('tables.role')
                    </dt>
                    <dd class="font-semibold text-theme-primary-600">
                        {{ ucfirst($this->user->pivot->role) }}
                    </dd>
                </div>
                <div class="flex justify-between space-x-3">
                    <dt class="font-semibold text-theme-secondary-500">
                        @lang('tables.date_added')
                    </dt>
                    <dd>
                        <span class="whitespace-nowrap">{{ $this->user->pivot->created_at_local->format(DateFormat::DATE) }}</span>
                    </dd>
                </div>

            </dl>

            @unless ($token->user->id === $this->user->id)
                <div class="flex justify-between space-x-3 sm:justify-end">

                    <div class="flex justify-end space-x-3 w-full">
                        <button
                            class="flex justify-center w-1/2 sm:w-auto button-secondary"
                            wire:click="$emit('updateCollaborator', '{{ $this->user->id }}')"
                            @cannot ('createCollaborator', $token) disabled @endcannot
                        >
                            <x-ark-icon name="pencil" size="sm" />
                        </button>
                    </div>
                </div>
            @endunless

            @if ($this->collaborators->count())
                    <x-divider spacing="4" />
            @endif
        </li>

        @foreach ($this->collaborators as $collaborator)
            <li class="flex flex-col space-y-4">
                <dl class="flex flex-col space-y-4 text-theme-secondary-700">
                    <div class="flex justify-between space-x-3 w-full min-w-0">
                        <dt class="flex-grow">
                            <div class="flex items-center space-x-4 w-full">
                                <x-ark-avatar
                                    :identifier="$collaborator->name"
                                    class="w-8 h-8 rounded-lg"
                                    show-identifier-letters
                                />

                                <div class="relative flex-grow">
                                    <span class="absolute max-w-full font-semibold truncate">
                                        {{ $collaborator->name }}
                                    </span>
                                    <span class="overflow-hidden h-0">&nbsp;</span>
                                </div>
                            </div>
                        </dt>

                    </div>
                    <div class="flex justify-between space-x-3">
                        <dt class="font-semibold text-theme-secondary-500">
                            @lang('tables.role')
                        </dt>
                        <dd class="font-semibold text-theme-primary-600">
                            {{ ucfirst($collaborator->pivot->role) }}
                        </dd>
                    </div>
                    <div class="flex justify-between space-x-3">
                        <dt class="font-semibold text-theme-secondary-500">
                            @lang('tables.date_added')
                        </dt>
                        <dd>
                            <span class="whitespace-nowrap">{{ $collaborator->pivot->created_at_local->format(DateFormat::DATE) }}</span>
                        </dd>
                    </div>

                </dl>

                <div class="flex justify-between space-x-3 sm:justify-end">
                    @unless ($token->user->id === $collaborator->id)
                        <div class="flex justify-end space-x-3 w-full">
                            <button
                                class="flex justify-center w-1/2 sm:w-auto button-secondary"
                                wire:click="$emit('updateCollaborator', '{{ $collaborator->id }}')"
                                @cannot ('createCollaborator', $token) disabled @endcannot
                            >
                                <x-ark-icon name="pencil" size="sm" />
                            </button>

                            <button
                                class="flex justify-center w-1/2 sm:w-auto button-cancel"
                                wire:click="$emit('deleteCollaborator', '{{ $collaborator->id }}')"
                                @cannot ('deleteCollaborator', $token) disabled @endcannot
                            >
                                <x-ark-icon name="trash" size="sm" />
                            </button>
                        </div>
                    @endunless
                </div>

                @unless ($loop->last)
                    <x-divider spacing="4" />
                @endunless
            </li>
        @endforeach
    </ul>

    @if ($token->canBeEdited())
        <div class="flex justify-end mt-5">
            <x-tokens.onboard-buttons
                :token="$token"
                step="collaborators"
                :title="trans('actions.continue')"
                :show-cancel="false"
            />
        </div>
    @endif
</div>
