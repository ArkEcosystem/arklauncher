@component ('layouts.token', ['token' => $token])
    @slot ('title')
        @lang ('tokens.activity.page_header')
    @endslot

    @if ($activities->isEmpty())
        <p>@lang('tokens.activity.activities_is_empty')</p>
    @else
        <div class="mt-5 table-container">
            <x-ark-tables.table sticky class="min-w-full">
                <thead>
                    <tr>
                        <x-ark-tables.header>
                            {{ trans('tokens.activity.activity_table_event') }}
                        </x-ark-tables.header>

                        <x-ark-tables.header>
                            {{ trans('tokens.activity.activity_table_performed_by') }}
                        </x-ark-tables.header>

                        <x-ark-tables.header class="text-right">
                            {{ trans('tokens.activity.activity_table_date') }}
                        </x-ark-tables.header>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($activities as ['activity' => $activity, 'user' => $user])
                        <x-ark-tables.row>
                            <x-ark-tables.cell class="font-semibold text-theme-secondary-700">
                                <div class="flex items-center space-x-2">
                                    @if ($activity->description === ActivityDescriptionEnum::CREATED)
                                        <x-ark-status-circle type="success" />
                                    @elseif ($activity->description === ActivityDescriptionEnum::UPDATED)
                                        <x-ark-status-circle type="updated" />
                                    @elseif ($activity->description === ActivityDescriptionEnum::DELETED)
                                        <x-ark-status-circle type="failed" />
                                    @endif

                                    <div class="font-semibold">
                                        @lang('activity.log_name.' . Str::snake($activity->log_name))

                                        @if ($activity->properties->count() > 0)
                                            @if ($activity->log_name === 'ServerProvider')
                                                {{ ucfirst($activity->properties['type']) }}
                                            @elseif ($activity->log_name === 'Server')
                                                {{ ucfirst($activity->properties['preset']) }}
                                            @endif
                                        @endif

                                        @lang('activity.description.' . lcfirst($activity->description))
                                    </div>
                                </div>
                            </x-ark-tables.cell>

                            <x-ark-tables.cell>
                                @if ($user !== null)
                                    <div class="flex items-center space-x-2">
                                        <x-ark-avatar
                                            :identifier="$user->name"
                                            class="w-8 h-8 rounded-lg"
                                            show-identifier-letters
                                        />

                                        <span class="text-sm font-semibold text-theme-secondary-700">{{ $user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-sm font-semibold text-theme-secondary-700">ARKLauncher</span>
                                @endif
                            </x-ark-tables.cell>

                            <x-ark-tables.cell class="text-sm text-right text-theme-secondary-700">
                                {{ $activity->created_at_local->format(DateFormat::TIME_PARENTHESES) }}
                            </x-ark-tables.cell>
                        </x-ark-tables.row>
                    @endforeach
                </tbody>
            </x-ark-tables.table>
        </div>

        @if ($activities->hasPages())
            <div class="flex justify-center mt-5">
                {{ $activities->links('ark::pagination-url') }}
            </div>
        @endif
    @endif
@endcomponent
